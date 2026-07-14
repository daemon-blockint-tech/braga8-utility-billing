<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
        $table->decimal('amount_paid', 15, 2);
        $table->date('due_date');
        $table->string('paid_using'); 
        $table->string('bank_rekening')->nullable(); 
        $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
        $table->timestamp('payment_date')->nullable();
        $table->string('proof_img')->nullable(); 
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
