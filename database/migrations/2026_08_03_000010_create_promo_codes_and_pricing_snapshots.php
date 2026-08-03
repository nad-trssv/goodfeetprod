<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('minimum_amount', 10, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_customer_limit')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('first_booking_only')->default(false);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('promo_code_service', function (Blueprint $table) {
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->primary(['promo_code_id', 'service_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('promo_code_id')->nullable()->after('service_id')->constrained()->nullOnDelete();
            $table->string('promo_code', 50)->nullable()->after('promo_code_id');
            $table->decimal('original_price', 10, 2)->nullable()->after('price');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('original_price');
        });
        DB::table('appointments')->whereNull('original_price')->update(['original_price' => DB::raw('price')]);

        Schema::create('promo_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_email', 190)->nullable()->index();
            $table->decimal('original_price', 10, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
            $table->index(['promo_code_id', 'customer_id']);
            $table->index(['promo_code_id', 'customer_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_redemptions');
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn(['promo_code', 'original_price', 'discount_amount']);
        });
        Schema::dropIfExists('promo_code_service');
        Schema::dropIfExists('promo_codes');
    }
};
