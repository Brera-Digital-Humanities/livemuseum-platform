<?php namespace Quivi\Poi\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class UserRankingsSchema extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quivi_poi_user_rankings')) {
            Schema::create('quivi_poi_user_rankings', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('user_id')->unique();
                $table->unsignedInteger('authority')->default(0);
                $table->unsignedInteger('photography')->default(0);
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('quivi_poi_user_rankings');
    }
}
