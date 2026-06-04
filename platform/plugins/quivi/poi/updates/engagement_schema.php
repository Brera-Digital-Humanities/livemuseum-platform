<?php namespace Quivi\Poi\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class EngagementSchema extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quivi_poi_picture_likes')) {
            Schema::create('quivi_poi_picture_likes', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->integer('picture_id')->unsigned();
                $table->integer('user_id')->unsigned();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->index(['picture_id', 'user_id'], 'quivi_poi_picture_likes_target_user_index');
                $table->index('user_id', 'quivi_poi_picture_likes_user_id_index');
            });
        }

        if (!Schema::hasTable('quivi_poi_comment_likes')) {
            Schema::create('quivi_poi_comment_likes', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->integer('comment_id')->unsigned();
                $table->integer('user_id')->unsigned();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->index(['comment_id', 'user_id'], 'quivi_poi_comment_likes_target_user_index');
                $table->index('user_id', 'quivi_poi_comment_likes_user_id_index');
            });
        }

        if (!Schema::hasTable('quivi_poi_bookmark_folders')) {
            Schema::create('quivi_poi_bookmark_folders', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->integer('user_id')->unsigned();
                $table->string('name');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->index('user_id', 'quivi_poi_bookmark_folders_user_id_index');
            });
        }

        if (!Schema::hasTable('quivi_poi_bookmarks')) {
            Schema::create('quivi_poi_bookmarks', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->integer('user_id')->unsigned();
                $table->string('target_type', 20);
                $table->integer('target_id')->unsigned();
                $table->integer('folder_id')->unsigned()->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->index(['target_type', 'target_id'], 'quivi_poi_bookmarks_target_index');
                $table->index(['user_id', 'target_type', 'target_id'], 'quivi_poi_bookmarks_user_target_index');
                $table->index('folder_id', 'quivi_poi_bookmarks_folder_id_index');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('quivi_poi_bookmarks');
        Schema::dropIfExists('quivi_poi_bookmark_folders');
        Schema::dropIfExists('quivi_poi_comment_likes');
        Schema::dropIfExists('quivi_poi_picture_likes');
    }
}
