<?php

use App\Post;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('message');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('page_id')->index();
            $table->unsignedBigInteger('fb_id')->nullable()->index();
            $table->unsignedBigInteger('fb_post_id')->nullable()->index();
            $table->unsignedBigInteger('fb_album_id')->nullable()->index();
            $table->tinyInteger('status')->default(Post::STATUS_NOT_PUBLISH);
            $table->string('media_url', 2083)->nullable();
            $table->string('link', 2083)->nullable();
            $table->tinyInteger('type');
            $table->string('video_title')->nullable();            
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('target_url')->nullable();
            $table->timestamps();

            $table->collation = 'utf8mb4_unicode_ci';
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('page_id')->references('id')->on('pages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('posts');
    }
}
