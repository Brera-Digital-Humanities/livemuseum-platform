<?php namespace Quivi\Poi\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class PictureUploadSchema extends Migration
{
    public function up()
    {
        if (Schema::hasTable('quivi_poi_pictures')) {
            Schema::table('quivi_poi_pictures', function ($table) {
                if (!Schema::hasColumn('quivi_poi_pictures', 'user_id')) {
                    $table->integer('user_id')->unsigned()->nullable();
                }

                if (!Schema::hasColumn('quivi_poi_pictures', 'system_file_id')) {
                    $table->integer('system_file_id')->unsigned()->nullable()->index('quivi_poi_pictures_system_file_id_index');
                }

                if (!Schema::hasColumn('quivi_poi_pictures', 'caption')) {
                    $table->string('caption', 500)->nullable();
                }

                if (!Schema::hasColumn('quivi_poi_pictures', 'exif_lat')) {
                    $table->decimal('exif_lat', 10, 7)->nullable();
                }

                if (!Schema::hasColumn('quivi_poi_pictures', 'exif_lng')) {
                    $table->decimal('exif_lng', 10, 7)->nullable();
                }

                if (!Schema::hasColumn('quivi_poi_pictures', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (!Schema::hasColumn('quivi_poi_pictures', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }

                if (!Schema::hasColumn('quivi_poi_pictures', 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable();
                }
            });

            return;
        }

        Schema::create('quivi_poi_pictures', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('poi_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('system_file_id')->unsigned()->nullable();
            $table->text('picture');
            $table->string('caption', 500)->nullable();
            $table->decimal('exif_lat', 10, 7)->nullable();
            $table->decimal('exif_lng', 10, 7)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index('poi_id', 'quivi_poi_pictures_poi_id_index');
            $table->index('user_id', 'quivi_poi_pictures_user_id_index');
            $table->index('system_file_id', 'quivi_poi_pictures_system_file_id_index');
            $table->index('created_at', 'quivi_poi_pictures_created_at_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quivi_poi_pictures');
    }
}
