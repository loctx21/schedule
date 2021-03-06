<?php

use App\Comment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPageManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('fb_access_token')->nullable();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fb_id')->unique()->index();
            $table->unsignedBigInteger('def_fb_album_id')->nullable()->index();
            $table->string('name');
            $table->text('access_token')->nullable();
            $table->integer('likes')->default(0);
            $table->string('timezone')->nullable();
            $table->tinyInteger('conv_index')->default(0);
            $table->text('message_reply_tmpl')->nullable();
            $table->text('post_reply_tmpl')->nullable();
            $table->string('schedule_time')->nullable();
            $table->string('status')->default(0);
            $table->timestamps();
        });

        Schema::create('page_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('page_id');
            $table->primary(['user_id', 'page_id']);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

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
        Schema::dropIfExists('page_user');
        Schema::dropIfExists('pages');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fb_access_token');
        });
    }
}
