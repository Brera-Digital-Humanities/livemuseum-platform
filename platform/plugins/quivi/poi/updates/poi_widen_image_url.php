<?php namespace Quivi\Poi\Updates;

use Db;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class PoiWidenImageUrl extends Migration
{
    public function up()
    {
        if (Schema::hasTable('quivi_poi_pois') && Schema::hasColumn('quivi_poi_pois', 'image_url')) {
            Db::statement('ALTER TABLE quivi_poi_pois MODIFY image_url TEXT NULL');
        }
    }

    public function down()
    {
        if (Schema::hasTable('quivi_poi_pois') && Schema::hasColumn('quivi_poi_pois', 'image_url')) {
            Db::statement('ALTER TABLE quivi_poi_pois MODIFY image_url VARCHAR(255) NULL');
        }
    }
}
