<?php

use App\Reply;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConversationReply extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('message');
            $table->tinyInteger('status')->default(Reply::STATUS_NOT_PUBLISH);
            $table->string('fb_target_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('page_id')->index();
            $table->unsignedBigInteger('post_id')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('page_id')->references('id')->on('pages');
            $table->foreign('post_id')->references('id')->on('posts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('replies');
    }
}
