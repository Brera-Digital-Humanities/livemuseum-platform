<?php namespace Quivi\Poi\Models;

use Model;

class Comment extends Model
{
    use \Winter\Storm\Database\Traits\SoftDelete;

    public $table = 'quivi_poi_comments';
    protected $dates = ['deleted_at'];

    public $rules = [
        'target_type'  => 'required|string',
        'target_id'    => 'required|integer',
        'user_id'      => 'required|integer',
        'comment_text' => 'required|string',
    ];

    public $belongsTo = [
        'user' => [\Winter\User\Models\User::class, 'key' => 'user_id'],
    ];

    public function getTargetTypeOptions()
    {
        return ['poi' => 'POI', 'picture' => 'Foto'];
    }
}
