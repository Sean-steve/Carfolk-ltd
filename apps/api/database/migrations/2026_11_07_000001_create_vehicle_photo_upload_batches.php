<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_photo_upload_batches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->uuid('client_batch_id');
            $table->jsonb('image_ids');
            $table->timestampsTz();
            $table->unique(['vehicle_id', 'client_batch_id'], 'vehicle_photo_batch_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photo_upload_batches');
    }
};
