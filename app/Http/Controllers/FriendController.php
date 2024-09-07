<?php

namespace App\Http\Controllers;

use App\Events\ChatSent;
use App\Events\MessageFriendSent;
use App\Http\Requests\FriendMessageRequest;
use App\Models\Chat;
use App\Models\UserMessage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends  Controller
{
    /**
     * @param $id
     * @return JsonResponse
     */
    public function messages($id)
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
     * @param FriendMessageRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function message(FriendMessageRequest $request, $id)
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

                $chat->user_id = $id;
                $chat->group_id = null;
                $chat->friend_id = Auth::id();
                $chat->deleted_at = null;
                $chat->save();
                $chat->friend;
                $chat->group;

                broadcast(new ChatSent($chat));

                broadcast(new MessageFriendSent($message));

                return $message;
            });

            return response()->json($message);
        } catch (Exception $exception) {
            return response()->json(['error'=> $exception->getMessage()], 500);
        }
    }

}
