<?php namespace Quivi\Poi\Models;

use Model;

class Itinerary extends Model
{
    use \Winter\Storm\Database\Traits\SoftDelete;

    public $table = 'quivi_poi_itineraries';
    protected $dates = ['deleted_at'];

    protected $fillable = ['title', 'description'];

    public $hasMany = [
        'stops' => [ItineraryStop::class, 'key' => 'itinerary_id', 'order' => 'sort_order asc'],
    ];
}
