<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('donation_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('foundation_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('description');
        $table->string('location');
        $table->string('reqiured_donation');
        $table->decimal('required_amount', 10, 2);
        $table->string('file_path')->nullable(); 
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('donation_requests');
    }
};
