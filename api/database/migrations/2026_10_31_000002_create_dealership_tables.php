<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('vehicles', function(Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('stock_reference', 60)->unique();
            $t->string('slug', 200)->unique();
            $t->string('make', 80)->index();
            $t->string('model', 100)->index();
            $t->string('trim', 100)->nullable();
            $t->unsignedSmallInteger('year')->index();
            $t->unsignedBigInteger('price_kes')->index();
            $t->unsignedBigInteger('mileage_km')->nullable();
            $t->unsignedInteger('engine_cc')->nullable();
            $t->string('fuel_type', 40)->nullable()->index();
            $t->string('transmission', 40)->nullable()->index();
            $t->string('drivetrain', 40)->nullable();
            $t->string('body_type', 50)->nullable()->index();
            $t->string('condition', 50)->nullable();
            $t->string('colour', 50)->nullable();
            $t->string('location', 140)->nullable();
            $t->text('description')->nullable();
            $t->jsonb('features')->nullable();
            $t->string('status', 20)->default('available')->index();
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_published')->default(false);
            $t->timestampTz('archived_at')->nullable()->index();
            $t->timestampsTz();
            $t->index(['is_published','status','archived_at']);
        });
        Schema::create('vehicle_images', function(Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $t->string('disk', 30)->default('public');
            $t->string('path', 500);
            $t->string('alt_text', 200)->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_primary')->default(false);
            $t->timestampsTz();
            $t->index(['vehicle_id','sort_order']);
        });
        Schema::create('vehicle_enquiries', function(Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $t->string('name', 80);
            $t->string('phone', 24);
            $t->string('email', 254)->nullable();
            $t->text('message');
            $t->string('status', 20)->default('new')->index();
            $t->timestampTz('consent_at');
            $t->timestampsTz();
        });
        Schema::create('test_drive_requests', function(Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->string('name', 80);
            $t->string('phone', 24);
            $t->string('email', 254)->nullable();
            $t->text('message')->nullable();
            $t->timestampTz('requested_at');
            $t->string('status', 20)->default('requested')->index();
            $t->timestampTz('consent_at');
            $t->timestampsTz();
        });
        Schema::create('site_settings', function(Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name', 130)->default('Carfolk LTD');
            $t->string('tagline', 150)->default('Quality & Affordability');
            $t->string('phone', 30)->nullable();
            $t->string('whatsapp', 30)->nullable();
            $t->string('email', 254)->nullable();
            $t->string('address', 300)->nullable();
            $t->string('opening_hours', 250)->nullable();
            $t->text('about')->nullable();
            $t->timestampsTz();
        });
        Schema::create('audit_events', function(Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action', 80)->index();
            $t->string('resource_type', 80);
            $t->string('resource_id', 100)->nullable();
            $t->jsonb('metadata')->nullable();
            $t->timestampTz('created_at')->useCurrent();
        });
    }
    public function down(): void {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('test_drive_requests');
        Schema::dropIfExists('vehicle_enquiries');
        Schema::dropIfExists('vehicle_images');
        Schema::dropIfExists('vehicles');
    }
};
