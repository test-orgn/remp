<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBrowserIdToConversionCommerceEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversion_commerce_events', function (Blueprint $table) {
            $table->string('browser_id')->nullable()->after('utm_source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversion_commerce_events', function (Blueprint $table) {
            $table->dropColumn('browser_id');
        });
    }
}
