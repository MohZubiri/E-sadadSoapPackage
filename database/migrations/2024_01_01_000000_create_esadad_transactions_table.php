<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('esadad_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique();
            $table->string('customer_id');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('YER');
            $table->string('bank_trx_id')->nullable();
            $table->string('sep_trx_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('esadad_transactions');
    }
};
