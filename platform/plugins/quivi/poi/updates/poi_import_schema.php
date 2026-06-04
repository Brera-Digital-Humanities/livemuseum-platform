<?php namespace Quivi\Poi\Updates;

use Db;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class PoiImportSchema extends Migration
{
    public function up()
    {
        if (Schema::hasTable('quivi_poi_pois')) {
            Schema::table('quivi_poi_pois', function ($table) {
                if (!Schema::hasColumn('quivi_poi_pois', 'type')) {
                    $table->string('type', 255)->nullable()->after('slug');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'category_app')) {
                    $table->string('category_app', 255)->nullable()->after('type');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'city')) {
                    $table->string('city', 255)->nullable()->after('address');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'province')) {
                    $table->string('province', 255)->nullable()->after('city');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'region')) {
                    $table->string('region', 255)->nullable()->after('province');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'phone')) {
                    $table->string('phone', 255)->nullable()->after('image_url');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'email')) {
                    $table->string('email', 255)->nullable()->after('phone');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'website')) {
                    $table->string('website', 512)->nullable()->after('email');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'opening_hours')) {
                    $table->json('opening_hours')->nullable()->after('website');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'source')) {
                    $table->string('source', 255)->nullable()->after('booking_info');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'source_file')) {
                    $table->string('source_file', 512)->nullable()->after('source');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'dedupe_key')) {
                    $table->string('dedupe_key', 255)->nullable()->after('source_file');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'raw_data')) {
                    $table->json('raw_data')->nullable()->after('dedupe_key');
                }
                if (!Schema::hasColumn('quivi_poi_pois', 'imported_at')) {
                    $table->timestamp('imported_at')->nullable()->after('raw_data');
                }
            });

            Db::statement('ALTER TABLE quivi_poi_pois MODIFY lat DECIMAL(10,7) NULL');
            Db::statement('ALTER TABLE quivi_poi_pois MODIFY lng DECIMAL(10,7) NULL');

            if (!Schema::hasColumn('quivi_poi_pois', 'location')) {
                Db::statement('ALTER TABLE quivi_poi_pois ADD location POINT SRID 4326 NULL AFTER lng');
            }

            $this->addIndexIfMissing('quivi_poi_pois', 'quivi_poi_pois_id_opendata_unique', 'CREATE UNIQUE INDEX quivi_poi_pois_id_opendata_unique ON quivi_poi_pois (id_opendata)');
            $this->addIndexIfMissing('quivi_poi_pois', 'quivi_poi_pois_dedupe_key_index', 'CREATE INDEX quivi_poi_pois_dedupe_key_index ON quivi_poi_pois (dedupe_key)');
            $this->addIndexIfMissing('quivi_poi_pois', 'quivi_poi_pois_category_app_index', 'CREATE INDEX quivi_poi_pois_category_app_index ON quivi_poi_pois (category_app)');
            $this->addIndexIfMissing('quivi_poi_pois', 'quivi_poi_pois_lat_lng_index', 'CREATE INDEX quivi_poi_pois_lat_lng_index ON quivi_poi_pois (lat, lng)');
        }

        if (!Schema::hasTable('quivi_poi_sources')) {
            Schema::create('quivi_poi_sources', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->integer('poi_id')->unsigned();
                $table->string('source', 255)->nullable();
                $table->string('source_file', 512)->nullable();
                $table->string('uri', 512)->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unique(['source', 'uri'], 'quivi_poi_sources_source_uri_unique');
                $table->index('poi_id', 'quivi_poi_sources_poi_id_index');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('quivi_poi_sources');

        if (Schema::hasTable('quivi_poi_pois')) {
            $this->dropIndexIfExists('quivi_poi_pois', 'quivi_poi_pois_location_spatial');
            $this->dropIndexIfExists('quivi_poi_pois', 'quivi_poi_pois_lat_lng_index');
            $this->dropIndexIfExists('quivi_poi_pois', 'quivi_poi_pois_category_app_index');
            $this->dropIndexIfExists('quivi_poi_pois', 'quivi_poi_pois_dedupe_key_index');
            $this->dropIndexIfExists('quivi_poi_pois', 'quivi_poi_pois_id_opendata_unique');

            Schema::table('quivi_poi_pois', function ($table) {
                $columns = [
                    'type',
                    'category_app',
                    'city',
                    'province',
                    'region',
                    'phone',
                    'email',
                    'website',
                    'opening_hours',
                    'source',
                    'source_file',
                    'dedupe_key',
                    'raw_data',
                    'imported_at',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('quivi_poi_pois', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });

            if (Schema::hasColumn('quivi_poi_pois', 'location')) {
                Db::statement('ALTER TABLE quivi_poi_pois DROP COLUMN location');
            }
        }
    }

    protected function addIndexIfMissing($table, $index, $sql)
    {
        if (!$this->indexExists($table, $index)) {
            Db::statement($sql);
        }
    }

    protected function dropIndexIfExists($table, $index)
    {
        if ($this->indexExists($table, $index)) {
            Db::statement('DROP INDEX ' . $index . ' ON ' . $table);
        }
    }

    protected function indexExists($table, $index)
    {
        $database = Db::getDatabaseName();

        return Db::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
}
