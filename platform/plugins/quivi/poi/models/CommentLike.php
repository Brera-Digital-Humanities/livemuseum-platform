<?php namespace Quivi\Poi\Models;

use Model;

class CommentLike extends Model
{
    use \Winter\Storm\Database\Traits\SoftDelete;

    public $table = 'quivi_poi_comment_likes';
    protected $dates = ['deleted_at'];

    public $belongsTo = [
        'comment' => [Comment::class, 'key' => 'comment_id'],
        'user'    => [\Winter\User\Models\User::class, 'key' => 'user_id'],
    ];
}
