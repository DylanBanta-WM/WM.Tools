<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chromebook_usage', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number', 100);
            $table->string('asset_id', 100)->nullable();
            $table->string('user_email', 255);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index('serial_number');
            $table->index('user_email');
            $table->index('recorded_at');

            $table->foreign('serial_number')
                  ->references('serial_number')
                  ->on('chromebook_inventory')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chromebook_usage');
    }
};
