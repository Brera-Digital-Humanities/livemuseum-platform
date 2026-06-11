<?php namespace Quivi\Poi\Models;

use Model;

class CommentReport extends Model
{
    public $table = 'quivi_poi_comment_reports';

    public $belongsTo = [
        'comment' => [Comment::class, 'key' => 'comment_id'],
        'user'    => [\Winter\User\Models\User::class, 'key' => 'user_id'],
    ];
}
