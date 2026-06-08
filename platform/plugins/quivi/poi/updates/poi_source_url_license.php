<?php namespace Quivi\Poi\Updates;

use Db;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class PoiSourceUrlLicense extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quivi_poi_pois')) {
            return;
        }

        Schema::table('quivi_poi_pois', function ($table) {
            if (!Schema::hasColumn('quivi_poi_pois', 'source_url')) {
                $table->string('source_url', 512)->nullable()->after('website');
            }
            if (!Schema::hasColumn('quivi_poi_pois', 'license')) {
                $table->string('license', 255)->nullable()->after('source_url');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('quivi_poi_pois')) {
            return;
        }

        Schema::table('quivi_poi_pois', function ($table) {
            if (Schema::hasColumn('quivi_poi_pois', 'license')) {
                $table->dropColumn('license');
            }
            if (Schema::hasColumn('quivi_poi_pois', 'source_url')) {
                $table->dropColumn('source_url');
            }
        });
    }
}
