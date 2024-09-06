<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'friend_id',
        'group_id',
    ];

    public function friend()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
