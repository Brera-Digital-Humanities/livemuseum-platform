<?php namespace Quivi\Poi\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateQuiviPoiPois extends Migration
{
    public function up()
    {
        Schema::create('quivi_poi_pois', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('id_opendata', 255)->nullable();
            $table->string('code', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->string('fulladdress', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('zipcode', 45)->nullable();
            $table->decimal('lat', 10, 2)->nullable();
            $table->decimal('lng', 11, 1)->nullable();
            $table->text('descr')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->text('tickets_info')->nullable();
            $table->text('booking_info')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('quivi_poi_pois');
    }
}
