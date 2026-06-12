<?php namespace Quivi\Poi\Models;

use Model;

class UserRanking extends Model
{
    public $table = 'quivi_poi_user_rankings';
    public $timestamps = false;

    protected $fillable = ['user_id', 'authority', 'photography'];

    public $belongsTo = [
        'user' => [\Winter\User\Models\User::class, 'key' => 'user_id'],
    ];
}
