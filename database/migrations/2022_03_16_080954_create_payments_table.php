<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStallPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stall_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');           
            $table->unsignedBigInteger('sp_stall_id');           
            $table->decimal('amount',15,2)->nullable();
            $table->tinyInteger('payment_type_id')->default(0)->comment('1 = online payment, 2 = check payment , 3 = cash payment')->nullable();
            $table->tinyInteger('is_partial')->default(0)->comment('0 = full_pay, 1 = partial_pay')->nullable();
            $table->string('transaction_no', 150)->nullable();
            $table->string('voucher_no', 100)->nullable();
            $table->string('voucher', 100)->nullable();
            $table->string('mac_addr', 100)->nullable();
            $table->string('pay_status', 50)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=not redirect, 2=redirect');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('stall_payments');
    }
}
