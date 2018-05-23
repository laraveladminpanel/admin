<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaginationToDataTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_types', function (Blueprint $table) {
            $table->string('pagination', 10)->default('js')->after('generate_permissions');

            if (Schema::hasColumn('permissions', 'server_side')) {
                $table->dropColumn('server_side');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_types', function (Blueprint $table) {
            $table->dropColumn('pagination');
        });
    }
}
