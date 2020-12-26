<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTestModelTable extends Migration
{
    public function up()
    {
        DB::transaction(function () {
            Schema::create('test_models', function (Blueprint $table) {
                $table->increments('id');

                $table->string('name');
                $table->string('some_field');
                $table->float('version')->default(0.0);
            });

            Schema::create('soft_test_models', function (Blueprint $table) {
                $table->increments('id');
                $table->softDeletes();

                $table->string('name');
            });
        });
    }

    public function down()
    {
        DB::transaction(function () {
            Schema::drop('test_models');
            Schema::drop('soft_test_models');
        });
    }
}
