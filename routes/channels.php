<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('friend.{user_id}', function (User $user, $user_id) {
    return (int) $user->id === (int) $user_id;
});

Broadcast::channel('group.{group_id}', function (User $user, $group_id) {
    return $user->groups()->pluck('id')->contains($group_id);
});
