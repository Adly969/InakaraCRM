<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Parallel Ledgers
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('code', 30);
            $table->string('name', 100);
            $table->boolean('is_leading')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'uq_ledger_code');
        });

        // 2. Generic Financial Dimension Framework
        Schema::create('financial_dimensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('code', 30);
            $table->string('name', 100);
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'uq_fd_code');
        });

        Schema::create('financial_dimension_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('financial_dimension_id');
            $table->string('code', 50);
            $table->string('name', 150);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('financial_dimension_id')->references('id')->on('financial_dimensions')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('financial_dimension_values')->onDelete('set null');
            $table->unique(['financial_dimension_id', 'code'], 'uq_fdv_code');
        });

        // 3. Document Sequence Ranges
        Schema::create('document_number_ranges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('document_type', 30);
            $table->string('prefix', 20);
            $table->bigInteger('current_value')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'document_type'], 'uq_doc_num');
        });

        // 4. Enterprise Exchange Rates History
        Schema::create('exchange_rates_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->string('rate_type', 20)->default('SPOT');
            $table->decimal('rate', 15, 6);
            $table->date('effective_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates_history');
        Schema::dropIfExists('document_number_ranges');
        Schema::dropIfExists('financial_dimension_values');
        Schema::dropIfExists('financial_dimensions');
        Schema::dropIfExists('ledgers');
    }
};
