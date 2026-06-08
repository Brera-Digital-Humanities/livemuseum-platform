<?php namespace Quivi\Poi\Models;

use Model;

class Picture extends Model
{
    use \Winter\Storm\Database\Traits\SoftDelete;

    public $table = 'quivi_poi_pictures';
    protected $dates = ['deleted_at'];

    public $rules = [
        'poi_id'  => 'required|integer',
        'picture' => 'required',
    ];

    public $belongsTo = [
        'poi' => [Poi::class, 'key' => 'poi_id'],
    ];
}
