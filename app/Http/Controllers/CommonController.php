<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use Inertia\ResponseFactory;

class CommonController extends Controller
{
    /**
     * @return Response|ResponseFactory
     */
    public function chats()
    {
        return inertia('Chats', [
            'chats' => Chat::query()
                ->where('user_id', Auth::id())
                ->with(['friend', 'group'])
                ->orderByDesc('updated_at')
                ->get()
        ]);
    }

    /**
     * @return Response|ResponseFactory
     */
    public function contacts()
    {
        return inertia('Contacts', [
            'contacts' => User::query()
                ->with([
                    'friendRequests' => function ($query) {
                        $query->withPivot(['message', 'status']);
                    },
                    'groups' => function ($query) {
                        $query->with(['members']);
                    },
                    'friends',
                ])
                ->find(Auth::id())
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function chat(Request $request)
    {
        $group_id = $request->input('group_id');
        $friend_id = $request->input('friend_id');

        $chat = Chat::withTrashed()
            ->with(['friend', 'group'])
            ->where('user_id', Auth::id())
            ->where('group_id', $group_id)
            ->where('friend_id', $friend_id)
            ->firstOrNew();

        $chat->user_id = Auth::id();
        $chat->group_id = $group_id;
        $chat->friend_id = $friend_id;
        $chat->deleted_at = null;
        $chat->save();
        $chat->friend;
        $chat->group;

        return $chat;
    }

    public function friends(Request $request)
    {
        return response()->json(User::query()->with('friends')->find(Auth::id()));
    }
}
