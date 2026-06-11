<?php namespace Quivi\Poi\Models;

use Model;

class ItineraryStop extends Model
{
    public $table = 'quivi_poi_itinerary_stops';

    protected $fillable = ['itinerary_id', 'poi_id', 'sort_order', 'note'];

    public $belongsTo = [
        'itinerary' => [Itinerary::class, 'key' => 'itinerary_id'],
        'poi'       => [Poi::class, 'key' => 'poi_id'],
    ];
}
