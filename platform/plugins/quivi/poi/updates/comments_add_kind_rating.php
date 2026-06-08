<?php namespace Quivi\Poi\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CommentsAddKindRating extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quivi_poi_comments')) {
            return;
        }

        Schema::table('quivi_poi_comments', function ($table) {
            if (!Schema::hasColumn('quivi_poi_comments', 'kind')) {
                $table->string('kind', 20)->default('comment')->after('comment_text');
            }
            if (!Schema::hasColumn('quivi_poi_comments', 'rating')) {
                $table->tinyInteger('rating')->unsigned()->nullable()->after('kind');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('quivi_poi_comments')) {
            return;
        }

        Schema::table('quivi_poi_comments', function ($table) {
            if (Schema::hasColumn('quivi_poi_comments', 'rating')) {
                $table->dropColumn('rating');
            }
            if (Schema::hasColumn('quivi_poi_comments', 'kind')) {
                $table->dropColumn('kind');
            }
        });
    }
}
