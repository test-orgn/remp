<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewsletterRectangleTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletter_rectangle_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('banner_id')->unsigned();
            $table->string('newsletter_id');
            $table->string('btn_submit');

            $table->string('title')->nullable(true);
            $table->text('text')->nullable(true);
            $table->text('success')->nullable(true);
            $table->text('failure')->nullable(true);
            $table->string('url_terms')->nullable(true);

            $table->foreign('banner_id')->references('id')->on('banners');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newsletter_rectangle_templates');
    }
}
