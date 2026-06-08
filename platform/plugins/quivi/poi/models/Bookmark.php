<?php namespace Quivi\Poi\Models;

use Model;

class Bookmark extends Model
{
    use \Winter\Storm\Database\Traits\SoftDelete;

    public $table = 'quivi_poi_bookmarks';
    protected $dates = ['deleted_at'];

    public function getTargetTypeOptions()
    {
        return ['poi' => 'POI', 'picture' => 'Foto'];
    }
}
