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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('tax_id', 100)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('code', 50);
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['company_id', 'code']);
            $table->index('tenant_id');
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('code', 50);
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['branch_id', 'code']);
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('companies');
    }
};
