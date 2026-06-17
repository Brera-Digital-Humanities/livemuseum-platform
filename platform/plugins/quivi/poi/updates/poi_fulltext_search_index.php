<?php

use Winter\Storm\Database\Updates\Migration;

class PoiFulltextSearchIndex extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE quivi_poi_pois ADD FULLTEXT INDEX ft_poi_search (title, city, province, region, category_app, fulladdress)');
    }

    public function down()
    {
        DB::statement('ALTER TABLE quivi_poi_pois DROP INDEX ft_poi_search');
    }
}
