<?php

namespace App\Http\Controllers;

use App\Events\ChatSent;
use App\Events\MessageGroupSent;
use App\Http\Requests\GroupCreateRequest;
use App\Http\Requests\GroupMessageRequest;
use App\Models\Chat;
use App\Models\Group;
use App\Models\GroupMessage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * @param $id
     * @return JsonResponse
     */
    public function messages($id)
    {
        return response()->json([
            'messages' => GroupMessage::query()
                ->with('user')
                ->where(['group_id' => $id])
                ->get(),
            'members' => Group::query()
                ->with('members')
                ->find($id)
                ->members
        ]);
    }

    /**
     * @param GroupMessageRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function message(GroupMessageRequest $request, $id)
    {
        try {
            $message = DB::transaction(function () use ($request, $id) {
                $message = GroupMessage::query()->create([
                    'user_id' => Auth::id(),
                    'group_id' => $id,
                    'text' => $request->input('message')
                ]);
                $message->user;

                $message->members()->pluck('user_id')->map(function ($user_id) use ($id) {
                    $chat = Chat::withTrashed()
                        ->with(['friend', 'group'])
                        ->where('user_id', $user_id)
                        ->where('group_id', $id)
                        ->whereNull('friend_id')
                        ->firstOrNew();

                    $chat->user_id = $user_id;
                    $chat->group_id = $id;
                    $chat->friend_id = null;
                    $chat->deleted_at = null;
                    $chat->save();
                    $chat->friend;
                    $chat->group;

                    broadcast(new ChatSent($chat));
                });

                broadcast(new MessageGroupSent($message));

                return $message;
            });

            return response()->json($message);
        } catch (Exception $exception) {
            return response()->json(['error'=> $exception->getMessage()], 500);
        }
    }

    public function create(GroupCreateRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $members = $request->validated('members');
                array_unshift($members, ['id' => Auth::id(), 'name' => Auth::user()->name]);
                $member_id = array_column($members, 'id');
                $member_name = array_column($members, 'name');

                $group = Group::query()->create([
                    'leader_id' => Auth::id(),
                    'name' => implode(',', array_slice($member_name, 0, 3)) . '...',
                ]);

                $group->members()->attach($member_id);

                $return_chat = null;

                foreach ($member_id as $user_id) {
                    $chat = Chat::query()->create([
                        'user_id' => $user_id,
                        'group_id' => $group->id
                    ]);
                    $chat->friend;
                    $chat->group;

                    if ($user_id === Auth::id()) {
                        $return_chat = $chat;
                    } else {
                        broadcast(new ChatSent($chat));
                    }
                }

                return $return_chat;
            });
        } catch (Exception $exception) {
            return response()->json(['error'=> $exception->getMessage()], 500);
        }

    }
}
