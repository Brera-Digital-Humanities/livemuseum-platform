<?php namespace Quivi\Poi\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BookmarkFoldersIspublic extends Migration
{
    public function up()
    {
        if (Schema::hasTable('quivi_poi_bookmark_folders') &&
            !Schema::hasColumn('quivi_poi_bookmark_folders', 'is_public')) {
            Schema::table('quivi_poi_bookmark_folders', function ($table) {
                $table->boolean('is_public')->default(false)->after('name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('quivi_poi_bookmark_folders', 'is_public')) {
            Schema::table('quivi_poi_bookmark_folders', function ($table) {
                $table->dropColumn('is_public');
            });
        }
    }
}
