<?php namespace Quivi\Poi\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CommentsReportsSchema extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quivi_poi_comment_reports')) {
            Schema::create('quivi_poi_comment_reports', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('comment_id');
                $table->unsignedInteger('user_id');
                $table->timestamps();
                $table->unique(['comment_id', 'user_id'], 'quivi_poi_comment_reports_unique');
                $table->index('comment_id', 'quivi_poi_comment_reports_comment_id_index');
                $table->index('user_id', 'quivi_poi_comment_reports_user_id_index');
            });
        }

        if (Schema::hasTable('quivi_poi_comments')) {
            if (!Schema::hasColumn('quivi_poi_comments', 'reports_count')) {
                Schema::table('quivi_poi_comments', function ($table) {
                    $table->unsignedInteger('reports_count')->default(0)->after('rating');
                    $table->boolean('is_hidden')->default(false)->after('reports_count');
                });
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('quivi_poi_comment_reports');

        if (Schema::hasTable('quivi_poi_comments')) {
            Schema::table('quivi_poi_comments', function ($table) {
                $table->dropColumn(['reports_count', 'is_hidden']);
            });
        }
    }
}
