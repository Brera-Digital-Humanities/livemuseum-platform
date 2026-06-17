<?php

use Winter\Storm\Database\Updates\Migration;

class PoiTitleIndex extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE quivi_poi_pois ADD INDEX poi_deleted_title_index (deleted_at, title)');
    }

    public function down()
    {
        DB::statement('ALTER TABLE quivi_poi_pois DROP INDEX poi_deleted_title_index');
    }
}
