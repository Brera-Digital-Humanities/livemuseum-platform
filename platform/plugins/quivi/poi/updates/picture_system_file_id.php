<?php namespace Quivi\Poi\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class PictureSystemFileId extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quivi_poi_pictures')) {
            return;
        }

        if (Schema::hasColumn('quivi_poi_pictures', 'system_file_id')) {
            return;
        }

        Schema::table('quivi_poi_pictures', function ($table) {
            $table->integer('system_file_id')->unsigned()->nullable()->index('quivi_poi_pictures_system_file_id_index');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('quivi_poi_pictures') || !Schema::hasColumn('quivi_poi_pictures', 'system_file_id')) {
            return;
        }

        Schema::table('quivi_poi_pictures', function ($table) {
            $table->dropIndex('quivi_poi_pictures_system_file_id_index');
            $table->dropColumn('system_file_id');
        });
    }
}
