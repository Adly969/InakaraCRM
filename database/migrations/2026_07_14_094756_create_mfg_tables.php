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
        // 1. Bill of Materials (BOM) Head
        Schema::create('mfg_boms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('bom_no', 100);
            $table->string('sku', 50);
            $table->string('description', 255)->nullable();
            $table->string('status', 30)->default('ACTIVE');
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'bom_no']);
        });

        // 2. BOM Items Line Detail
        Schema::create('mfg_bom_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id');
            $table->string('sku', 50);
            $table->decimal('quantity', 12, 4);
            $table->decimal('yield_factor', 5, 2)->default(100.00);
            $table->timestamps();

            $table->foreign('bom_id')->references('id')->on('mfg_boms')->onDelete('cascade');
        });

        // 3. Work Centers
        Schema::create('mfg_work_centers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('code', 50);
            $table->string('name', 150);
            $table->decimal('efficiency_rate', 5, 2)->default(100.00);
            $table->decimal('hourly_labor_rate', 15, 2)->default(0.00);
            $table->decimal('hourly_machine_rate', 15, 2)->default(0.00);
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        // 4. Active Machinery
        Schema::create('mfg_machines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_center_id');
            $table->string('code', 50);
            $table->string('name', 150);
            $table->decimal('max_capacity_hours', 6, 2);
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();

            $table->foreign('work_center_id')->references('id')->on('mfg_work_centers')->onDelete('cascade');
        });

        // 5. Machine Calendar Downtimes
        Schema::create('mfg_machine_downtimes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->string('reason_code', 50);
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('mfg_machines')->onDelete('cascade');
        });

        // 6. Routing Master Profile
        Schema::create('mfg_routings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('sku', 50);
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'sku']);
        });

        // 7. Routing Steps Detail
        Schema::create('mfg_routing_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('routing_id');
            $table->integer('step_sequence');
            $table->unsignedBigInteger('work_center_id');
            $table->integer('setup_time_minutes')->default(0);
            $table->integer('run_time_minutes')->default(0);
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->foreign('routing_id')->references('id')->on('mfg_routings')->onDelete('cascade');
            $table->foreign('work_center_id')->references('id')->on('mfg_work_centers')->onDelete('cascade');
        });

        // 8. Production Orders Table
        Schema::create('mfg_production_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('bom_id')->nullable();
            $table->string('production_no', 100);
            $table->string('sku', 50);
            $table->decimal('quantity_planned', 12, 4);
            $table->decimal('quantity_produced', 12, 4)->default(0.0000);
            $table->decimal('quantity_scrapped', 12, 4)->default(0.0000);
            $table->string('status', 30)->default('DRAFT');
            $table->timestamp('scheduled_start')->nullable();
            $table->timestamp('scheduled_end')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bom_id')->references('id')->on('mfg_boms')->onDelete('set null');
            $table->unique(['company_id', 'production_no']);
        });

        // 9. Material Reservations Table
        Schema::create('mfg_material_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('production_order_id');
            $table->string('sku', 50);
            $table->decimal('quantity_reserved', 12, 4);
            $table->decimal('quantity_issued', 12, 4)->default(0.0000);
            $table->string('status', 30)->default('RESERVED');
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('mfg_production_orders')->onDelete('cascade');
        });

        // 10. Work Order Operations Queue
        Schema::create('mfg_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->integer('step_sequence');
            $table->unsignedBigInteger('work_center_id');
            $table->string('status', 30)->default('PENDING');
            $table->integer('actual_setup_minutes')->default(0);
            $table->integer('actual_run_minutes')->default(0);
            $table->decimal('labor_hours_logged', 8, 2)->default(0.00);
            $table->decimal('machine_hours_logged', 8, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('mfg_production_orders')->onDelete('cascade');
            $table->foreign('work_center_id')->references('id')->on('mfg_work_centers')->onDelete('cascade');
        });

        // 11. Manufacturing Labor & Confirmations Log
        Schema::create('mfg_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operation_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->decimal('quantity_yield', 12, 4);
            $table->decimal('quantity_scrap', 12, 4)->default(0.0000);
            $table->decimal('labor_hours', 8, 2);
            $table->decimal('machine_hours', 8, 2);
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->foreign('operation_id')->references('id')->on('mfg_operations')->onDelete('cascade');
            $table->foreign('machine_id')->references('id')->on('mfg_machines')->onDelete('set null');
        });

        // 12. Quality Inspection Sheets
        Schema::create('mfg_quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->unsignedBigInteger('inspector_id');
            $table->decimal('quantity_tested', 12, 4);
            $table->decimal('quantity_passed', 12, 4);
            $table->decimal('quantity_failed', 12, 4);
            $table->string('status', 30)->default('PENDING');
            $table->text('inspection_notes')->nullable();
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('mfg_production_orders')->onDelete('set null');
        });

        // 13. Finished Goods Receipts
        Schema::create('mfg_finished_goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->string('receipt_no', 100);
            $table->decimal('quantity_received', 12, 4);
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('location_id');
            $table->timestamp('received_at');
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('mfg_production_orders')->onDelete('set null');
            $table->unique(['company_id', 'receipt_no']);
        });

        // 14. Cost Accumulators Table
        Schema::create('mfg_production_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->decimal('material_cost_actual', 15, 2)->default(0.00);
            $table->decimal('labor_cost_actual', 15, 2)->default(0.00);
            $table->decimal('machine_cost_actual', 15, 2)->default(0.00);
            $table->decimal('overhead_cost_actual', 15, 2)->default(0.00);
            $table->decimal('variance_amount', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('mfg_production_orders')->onDelete('cascade');
        });

        // 15. Engineering Change Notices (ECN)
        Schema::create('mfg_ecn_notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('ecn_no', 100);
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('DRAFT');
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'ecn_no']);
        });

        // 16. Shift Calendar Profiles
        Schema::create('mfg_shift_calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 100);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 17. Labor Skill Matrices
        Schema::create('mfg_skill_matrices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('skill_code', 50);
            $table->string('proficiency_level', 30);
            $table->timestamps();
        });

        // 18. Alternate BOM Mappings
        Schema::create('mfg_alternate_boms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id');
            $table->unsignedBigInteger('alternate_bom_id');
            $table->integer('preference_rank')->default(1);
            $table->timestamps();

            $table->foreign('bom_id')->references('id')->on('mfg_boms')->onDelete('cascade');
            $table->foreign('alternate_bom_id')->references('id')->on('mfg_boms')->onDelete('cascade');
        });

        // 19. Tool Master Inventory
        Schema::create('mfg_tools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('code', 50);
            $table->string('name', 100);
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        // 20. Tool Calibration Trackers
        Schema::create('mfg_tool_calibrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tool_id');
            $table->timestamp('calibrated_at');
            $table->date('next_calibration_due');
            $table->string('inspector_name', 100);
            $table->string('status', 30)->default('PASSED');
            $table->timestamps();

            $table->foreign('tool_id')->references('id')->on('mfg_tools')->onDelete('cascade');
        });

        // 21. Material Return Notes
        Schema::create('mfg_material_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->string('sku', 50);
            $table->decimal('quantity_returned', 12, 4);
            $table->unsignedBigInteger('warehouse_id');
            $table->timestamp('returned_at');
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('mfg_production_orders')->onDelete('set null');
        });

        // 22. Non-Conformance Reports (NCR)
        Schema::create('mfg_quality_ncrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('ncr_no', 100);
            $table->unsignedBigInteger('inspection_id')->nullable();
            $table->string('status', 30)->default('OPEN');
            $table->string('defect_reason_code', 50);
            $table->text('corrective_action_details')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inspection_id')->references('id')->on('mfg_quality_inspections')->onDelete('set null');
            $table->unique(['company_id', 'ncr_no']);
        });

        // 23. Rework Operations Details
        Schema::create('mfg_rework_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ncr_id');
            $table->unsignedBigInteger('routing_step_id')->nullable();
            $table->decimal('quantity_reworked', 12, 4);
            $table->string('status', 30)->default('PENDING');
            $table->timestamps();

            $table->foreign('ncr_id')->references('id')->on('mfg_quality_ncrs')->onDelete('cascade');
            $table->foreign('routing_step_id')->references('id')->on('mfg_routing_steps')->onDelete('set null');
        });

        // 24. Forecast Demand Matrix
        Schema::create('mfg_demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('sku', 50);
            $table->date('forecast_date');
            $table->decimal('quantity_forecast', 12, 4);
            $table->timestamps();
            $table->softDeletes();
        });

        // 25. Advanced Planning Sourced Supplies Pegging
        Schema::create('mfg_aps_supply_pegging', function (Blueprint $table) {
            $table->id();
            $table->string('demand_source_type', 50);
            $table->unsignedBigInteger('demand_source_id');
            $table->string('supply_type', 50);
            $table->unsignedBigInteger('supply_id');
            $table->decimal('pegged_quantity', 12, 4);
            $table->timestamps();
        });

        // 26. OEE Workstation Calculations
        Schema::create('mfg_oee_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_center_id');
            $table->date('calculated_date');
            $table->decimal('availability_score', 5, 2)->default(0.00);
            $table->decimal('performance_score', 5, 2)->default(0.00);
            $table->decimal('quality_score', 5, 2)->default(0.00);
            $table->decimal('oee_score', 5, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('work_center_id')->references('id')->on('mfg_work_centers')->onDelete('cascade');
        });

        // 27. Corrective Action Preventive Action (CAPA) Files
        Schema::create('mfg_quality_capas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('capa_no', 100);
            $table->unsignedBigInteger('ncr_id')->nullable();
            $table->text('preventive_action');
            $table->text('root_cause_analysis');
            $table->string('status', 30)->default('OPEN');
            $table->timestamps();

            $table->foreign('ncr_id')->references('id')->on('mfg_quality_ncrs')->onDelete('set null');
            $table->unique(['company_id', 'capa_no']);
        });

        // 28. Standard Cost Master Ledger
        Schema::create('mfg_standard_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('sku', 50);
            $table->decimal('standard_material_cost', 15, 2)->default(0.00);
            $table->decimal('standard_labor_cost', 15, 2)->default(0.00);
            $table->decimal('standard_machine_cost', 15, 2)->default(0.00);
            $table->decimal('standard_overhead_cost', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'sku']);
        });

        // 29. WIP Inventory Value Ledger
        Schema::create('mfg_wip_ledger', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->decimal('debit_amount', 15, 2)->default(0.00);
            $table->decimal('credit_amount', 15, 2)->default(0.00);
            $table->decimal('balance_amount', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('mfg_production_orders')->onDelete('set null');
        });

        // 30. Machine Maintenance Calendars
        Schema::create('mfg_machine_maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id');
            $table->string('maintenance_type', 50);
            $table->date('scheduled_date');
            $table->timestamp('completed_at')->nullable();
            $table->text('technician_notes')->nullable();
            $table->string('status', 30)->default('SCHEDULED');
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('mfg_machines')->onDelete('cascade');
        });

        // 31. Lot Genealogy Tracking
        Schema::create('mfg_lot_genealogy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('parent_lot_no', 100);
            $table->string('child_lot_no', 100);
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->string('link_type', 50);
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('mfg_production_orders')->onDelete('set null');
        });

        // 32. Heijunka Leveling Schedules
        Schema::create('mfg_heijunka_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('work_center_id')->nullable();
            $table->date('schedule_date');
            $table->integer('pitch_interval_minutes')->default(30);
            $table->timestamps();

            $table->foreign('work_center_id')->references('id')->on('mfg_work_centers')->onDelete('set null');
        });

        // 33. Kanban Container Cards
        Schema::create('mfg_kanban_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('card_no', 100);
            $table->string('sku', 50);
            $table->decimal('container_size', 12, 4);
            $table->string('source_location', 100);
            $table->string('destination_location', 100);
            $table->string('status', 30)->default('FULL');
            $table->timestamps();

            $table->unique(['company_id', 'card_no']);
        });

        // 34. IoT Device Mappings
        Schema::create('mfg_iot_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id');
            $table->string('device_uid', 100);
            $table->string('status', 30)->default('ONLINE');
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('mfg_machines')->onDelete('cascade');
        });

        // 35. SCADA Ingest Logs
        Schema::create('mfg_scada_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->string('metric_key', 50);
            $table->string('metric_value', 100);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('device_id')->references('id')->on('mfg_iot_devices')->onDelete('cascade');
        });

        // 36. PLC Signals Mapping
        Schema::create('mfg_plc_signals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id');
            $table->string('plc_address', 100);
            $table->string('signal_type', 50);
            $table->string('last_signal_value', 100)->nullable();
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('mfg_machines')->onDelete('cascade');
        });

        // 37. Operator Shift Logs
        Schema::create('mfg_operator_shift_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->date('logged_date');
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->timestamps();

            $table->foreign('shift_id')->references('id')->on('mfg_shift_calendars')->onDelete('set null');
        });

        // 38. Tool Usage Details
        Schema::create('mfg_tool_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tool_id')->nullable();
            $table->unsignedBigInteger('operation_id')->nullable();
            $table->timestamp('usage_started_at');
            $table->timestamp('usage_ended_at')->nullable();
            $table->timestamps();

            $table->foreign('tool_id')->references('id')->on('mfg_tools')->onDelete('set null');
            $table->foreign('operation_id')->references('id')->on('mfg_operations')->onDelete('set null');
        });

        // 39. Engineering Change Requests (ECR)
        Schema::create('mfg_ecr_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('ecr_no', 100);
            $table->string('title', 150);
            $table->text('description');
            $table->string('status', 30)->default('PENDING');
            $table->timestamps();

            $table->unique(['company_id', 'ecr_no']);
        });

        // 40. Engineering Change Orders (ECO)
        Schema::create('mfg_eco_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('eco_no', 100);
            $table->unsignedBigInteger('ecr_id')->nullable();
            $table->unsignedBigInteger('approver_id');
            $table->string('status', 30)->default('DRAFT');
            $table->timestamps();

            $table->foreign('ecr_id')->references('id')->on('mfg_ecr_requests')->onDelete('set null');
            $table->unique(['company_id', 'eco_no']);
        });

        // 41. Deviation Permits Logs
        Schema::create('mfg_quality_deviations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('deviation_no', 100);
            $table->string('sku', 50);
            $table->unsignedBigInteger('approved_by');
            $table->text('reason');
            $table->timestamp('valid_until');
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();

            $table->unique(['company_id', 'deviation_no']);
        });

        // 42. Rework Order Log Head
        Schema::create('mfg_rework_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('rework_no', 100);
            $table->unsignedBigInteger('ncr_id')->nullable();
            $table->string('status', 30)->default('DRAFT');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ncr_id')->references('id')->on('mfg_quality_ncrs')->onDelete('set null');
            $table->unique(['company_id', 'rework_no']);
        });

        // 43. Material Issue Slips (WMS)
        Schema::create('mfg_material_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->string('issue_no', 100);
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('mfg_production_orders')->onDelete('set null');
            $table->unique(['company_id', 'issue_no']);
        });

        // 44. Manufacturing Overhead Configurations
        Schema::create('mfg_overhead_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('work_center_id')->nullable();
            $table->decimal('overhead_percentage', 5, 2)->default(0.00);
            $table->string('allocation_method', 50)->default('LABOR_HOURS');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('work_center_id')->references('id')->on('mfg_work_centers')->onDelete('set null');
        });

        // 45. SPC Quality Control Charts
        Schema::create('mfg_spc_measurements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inspection_id');
            $table->string('parameter_name', 100);
            $table->decimal('measured_value', 12, 4);
            $table->decimal('upper_control_limit', 12, 4);
            $table->decimal('lower_control_limit', 12, 4);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('inspection_id')->references('id')->on('mfg_quality_inspections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mfg_spc_measurements');
        Schema::dropIfExists('mfg_overhead_configs');
        Schema::dropIfExists('mfg_material_issues');
        Schema::dropIfExists('mfg_rework_orders');
        Schema::dropIfExists('mfg_quality_deviations');
        Schema::dropIfExists('mfg_eco_orders');
        Schema::dropIfExists('mfg_ecr_requests');
        Schema::dropIfExists('mfg_tool_usage');
        Schema::dropIfExists('mfg_operator_shift_logs');
        Schema::dropIfExists('mfg_plc_signals');
        Schema::dropIfExists('mfg_scada_logs');
        Schema::dropIfExists('mfg_iot_devices');
        Schema::dropIfExists('mfg_kanban_cards');
        Schema::dropIfExists('mfg_heijunka_schedules');
        Schema::dropIfExists('mfg_lot_genealogy');
        Schema::dropIfExists('mfg_machine_maintenances');
        Schema::dropIfExists('mfg_wip_ledger');
        Schema::dropIfExists('mfg_standard_costs');
        Schema::dropIfExists('mfg_quality_capas');
        Schema::dropIfExists('mfg_oee_metrics');
        Schema::dropIfExists('mfg_aps_supply_pegging');
        Schema::dropIfExists('mfg_demand_forecasts');
        Schema::dropIfExists('mfg_rework_operations');
        Schema::dropIfExists('mfg_quality_ncrs');
        Schema::dropIfExists('mfg_material_returns');
        Schema::dropIfExists('mfg_tool_calibrations');
        Schema::dropIfExists('mfg_tools');
        Schema::dropIfExists('mfg_alternate_boms');
        Schema::dropIfExists('mfg_skill_matrices');
        Schema::dropIfExists('mfg_shift_calendars');
        Schema::dropIfExists('mfg_ecn_notices');
        Schema::dropIfExists('mfg_production_costs');
        Schema::dropIfExists('mfg_finished_goods_receipts');
        Schema::dropIfExists('mfg_quality_inspections');
        Schema::dropIfExists('mfg_operation_logs');
        Schema::dropIfExists('mfg_operations');
        Schema::dropIfExists('mfg_material_reservations');
        Schema::dropIfExists('mfg_production_orders');
        Schema::dropIfExists('mfg_routing_steps');
        Schema::dropIfExists('mfg_routings');
        Schema::dropIfExists('mfg_machine_downtimes');
        Schema::dropIfExists('mfg_machines');
        Schema::dropIfExists('mfg_work_centers');
        Schema::dropIfExists('mfg_bom_items');
        Schema::dropIfExists('mfg_boms');
    }
};
