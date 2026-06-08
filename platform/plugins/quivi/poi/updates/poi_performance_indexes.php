<?php namespace Quivi\Poi\Updates;

use Db;
use Schema;
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
        foreach ($this->indexes as $name => $sql) {
            $this->addIndexIfMissing('quivi_poi_pois', $name, $sql);
        }
    }

    public function down()
    {
        foreach (array_keys($this->indexes) as $name) {
            $this->dropIndexIfExists('quivi_poi_pois', $name);
        }
    }

    private function addIndexIfMissing($table, $index, $sql)
    {
        if (!$this->indexExists($table, $index)) {
            Db::statement($sql);
        }
    }

    private function dropIndexIfExists($table, $index)
    {
        if ($this->indexExists($table, $index)) {
            Db::statement('DROP INDEX ' . $index . ' ON ' . $table);
        }
    }

    private function indexExists($table, $index)
    {
        return Db::table('information_schema.statistics')
            ->where('table_schema', Db::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
}
