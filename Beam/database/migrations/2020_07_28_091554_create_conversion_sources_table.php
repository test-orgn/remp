<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversionSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversion_sources', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('conversion_id')->unsigned();
            $table->string('type');
            $table->string('referer_medium');
            $table->string('referer_source')->nullable();
            $table->string('referer_host_with_path')->nullable();
            $table->string('pageview_article_external_id')->nullable();
            $table->timestamps();

            $table->foreign('conversion_id')->references('id')->on('conversions');
            $table->foreign('pageview_article_external_id')->references('external_id')->on('articles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversion_sources');
    }
}
