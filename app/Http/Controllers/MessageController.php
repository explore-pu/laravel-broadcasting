<?php

namespace App\Http\Controllers;

use App\Events\ChatSent;
use App\Events\MessageFriendSent;
use App\Events\MessageGroupSent;
use App\Models\Chat;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\UserMessage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function friends(Request $request, $id)
    {
        return response()->json([
            'messages' => UserMessage::query()
                ->where(function ($query) use ($id) {
                    $query->where(['user_id' => Auth::id(), 'friend_id' => $id])
                        ->orWhere(function ($query) use ($id) {
                            $query->where(['user_id' => $id, 'friend_id' => Auth::id()]);
                        });
                })
                ->orderBy('id')
                ->get(),
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function friend(Request $request, $id)
    {
        try {
            $message = DB::transaction(function () use ($request, $id) {
                $message = UserMessage::query()->create([
                    'user_id' => Auth::id(),
                    'friend_id' => $id,
                    'text' => $request->input('message')
                ]);

                $chat = Chat::withTrashed()
                    ->with(['friend', 'group'])
                    ->where('user_id', $id)
                    ->where('friend_id', Auth::id())
                    ->whereNull('group_id')
                    ->firstOrNew();

                $chat->deleted_at = null;
                $chat->save();

                broadcast(new ChatSent($chat));

                broadcast(new MessageFriendSent($message));

                return $message;
            });

            return response()->json($message);
        } catch (Exception $exception) {
            return response()->json(['error'=> $exception->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function groups(Request $request, $id)
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
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function group(Request $request, $id)
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

                    $chat->deleted_at = null;
                    $chat->save();

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
}
