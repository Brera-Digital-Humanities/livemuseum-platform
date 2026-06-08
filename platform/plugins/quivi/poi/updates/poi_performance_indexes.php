<?php namespace Quivi\Poi\Updates;

use Db;
use Winter\Storm\Database\Updates\Migration;

class PoiPerformanceIndexes extends Migration
{
    private $indexes = [
        // Covers every WHERE deleted_at IS NULL query (COUNT, SELECT, GROUP BY)
        'quivi_poi_pois_deleted_at_index'       => 'CREATE INDEX quivi_poi_pois_deleted_at_index ON quivi_poi_pois (deleted_at)',
        // Covering index for lmPoiCategories: GROUP BY category_app WHERE deleted_at IS NULL
        'quivi_poi_pois_deleted_category_index' => 'CREATE INDEX quivi_poi_pois_deleted_category_index ON quivi_poi_pois (deleted_at, category_app)',
        // Covering index for pois/all paginated COUNT and ORDER BY id
        'quivi_poi_pois_deleted_id_index'       => 'CREATE INDEX quivi_poi_pois_deleted_id_index ON quivi_poi_pois (deleted_at, id)',
        // Filters: type, scheda_type, city, province, region
        'quivi_poi_pois_type_index'             => 'CREATE INDEX quivi_poi_pois_type_index ON quivi_poi_pois (type)',
        'quivi_poi_pois_scheda_type_index'      => 'CREATE INDEX quivi_poi_pois_scheda_type_index ON quivi_poi_pois (scheda_type)',
        'quivi_poi_pois_city_index'             => 'CREATE INDEX quivi_poi_pois_city_index ON quivi_poi_pois (city)',
        'quivi_poi_pois_province_index'         => 'CREATE INDEX quivi_poi_pois_province_index ON quivi_poi_pois (province)',
        'quivi_poi_pois_region_index'           => 'CREATE INDEX quivi_poi_pois_region_index ON quivi_poi_pois (region)',
    ];

    public function up()
    {
        foreach ($this->indexes as $sql) {
            $this->addIndexIfMissing($sql);
        }
    }

    public function down()
    {
        foreach (array_keys($this->indexes) as $name) {
            $this->dropIndexIfExists('quivi_poi_pois', $name);
        }
    }

    private function addIndexIfMissing($sql)
    {
        try {
            Db::statement($sql);
        } catch (\Exception $e) {
            // Index already exists — safe to skip
        }
    }

    private function dropIndexIfExists($table, $index)
    {
        try {
            Db::statement('DROP INDEX ' . $index . ' ON ' . $table);
        } catch (\Exception $e) {
            // Index does not exist — safe to skip
        }
    }
}
