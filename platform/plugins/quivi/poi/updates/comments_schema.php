<?php namespace Quivi\Poi\Updates;

use Db;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class CommentsSchema extends Migration
{
    public function up()
    {
        if (Schema::hasTable('quivi_poi_comments')) {
            return;
        }

        Schema::create('quivi_poi_comments', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('target_type', 50)->default('poi');
            $table->integer('target_id')->unsigned();
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned();
            $table->text('comment_text');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index(['target_type', 'target_id'], 'quivi_poi_comments_target_index');
            $table->index('parent_id', 'quivi_poi_comments_parent_index');
            $table->index('user_id', 'quivi_poi_comments_user_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quivi_poi_comments');
    }
}
