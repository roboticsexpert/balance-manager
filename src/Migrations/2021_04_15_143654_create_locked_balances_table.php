<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLockedBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locked_balances', function (Blueprint $table) {
            $table->string('currency', 16);
            $table->unsignedBigInteger('user_id');
            $table->string('owner_type', 32);
            $table->string('owner_id', 32);
            $table->unsignedDecimal('value', 32, 16);

            $table->primary([
                'currency',
                'user_id',
                'owner_type',
                'owner_id',
            ], 'p_key');

            $table->foreign(['user_id', 'currency'])->references(['user_id', 'currency'])->on('balances');

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
        Schema::dropIfExists('locked_balances');
    }
}
