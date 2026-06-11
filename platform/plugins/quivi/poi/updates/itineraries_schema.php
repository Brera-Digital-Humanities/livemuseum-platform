<?php namespace Quivi\Poi\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class ItinerariesSchema extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quivi_poi_itineraries')) {
            Schema::create('quivi_poi_itineraries', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('quivi_poi_itinerary_stops')) {
            Schema::create('quivi_poi_itinerary_stops', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('itinerary_id');
                $table->unsignedInteger('poi_id');
                $table->unsignedSmallInteger('sort_order')->default(1);
                $table->string('note', 500)->nullable();
                $table->timestamps();
                $table->index('itinerary_id', 'quivi_poi_itinerary_stops_itinerary_id_index');
                $table->index(['itinerary_id', 'sort_order'], 'quivi_poi_itinerary_stops_order_index');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('quivi_poi_itinerary_stops');
        Schema::dropIfExists('quivi_poi_itineraries');
    }
}
