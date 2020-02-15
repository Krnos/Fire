<?php

use Krnos\Fire\Change;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChangeTables extends Migration
{
    /**
     * Run the migration.
     */
    public function up()
    {
        Schema::create(config('fire.changes_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->morphs('model');
            $table->enum('change_type', [
                Change::TYPE_CREATED,
                Change::TYPE_UPDATED,
                Change::TYPE_DELETED,
                Change::TYPE_RESTORED,
            ]);
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('user_type')->nullable();
            $table->string('message');
            $table->json('changes');
            $table->timestamp('recorded_at');
        });
    }

    /**
     * Revert the migration.
     */
    public function down()
    {
        Schema::dropIfExists(config('fire.changes_table'));
    }

}