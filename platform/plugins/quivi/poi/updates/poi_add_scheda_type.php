<?php namespace Quivi\Poi\Updates;

use Db;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class PoiAddSchedaType extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quivi_poi_pois')) {
            return;
        }

        if (!Schema::hasColumn('quivi_poi_pois', 'scheda_type')) {
            Schema::table('quivi_poi_pois', function ($table) {
                $table->string('scheda_type', 45)->default('base')->after('category_app');
            });
        }

        Db::table('quivi_poi_pois')
            ->where(function ($query) {
                $query->whereNull('scheda_type')->orWhere('scheda_type', '');
            })
            ->update(['scheda_type' => 'base']);
    }

    public function down()
    {
        if (Schema::hasTable('quivi_poi_pois') && Schema::hasColumn('quivi_poi_pois', 'scheda_type')) {
            Schema::table('quivi_poi_pois', function ($table) {
                $table->dropColumn('scheda_type');
            });
        }
    }
}
