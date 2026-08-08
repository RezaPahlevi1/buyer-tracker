<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_vendor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('harga_dari_vendor', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_vendor');
    }
};