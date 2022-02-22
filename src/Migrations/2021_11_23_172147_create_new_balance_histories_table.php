<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewBalanceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('currency', 16);
            $table->decimal('value', 32, 16);
            $table->decimal('change_value', 32, 16);
            $table->string('reason', 64);
            $table->string('related_model_type', 32);
            $table->string('related_model_id', 32);
            $table->timestamps();

            $table->unique(['user_id', 'currency', 'related_model_type', 'related_model_id', 'reason'], 'reason_model_balance');
            $table->index(['user_id', 'currency', 'created_at'], 'pnl_index');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trade_new_balance_histories_tmp');
    }
}
