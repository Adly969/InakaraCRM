<?php

namespace Database\Seeders;

use App\Enums\CollectionActivityType;
use App\Enums\CustomerStatus;
use App\Enums\GoodsIssueStatus;
use App\Enums\GoodsReceiptStatus;
use App\Enums\HeatScore;
use App\Enums\InvoiceStatus;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\PaymentMethodType;
use App\Enums\PaymentStatus;
use App\Enums\ProductionOrderStatus;
use App\Enums\ProductionPriority;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\StockAdjustmentStatus;
use App\Enums\StockAdjustmentType;
use App\Enums\WarehouseStatus;
use App\Models\Activity;
use App\Models\Branch;
use App\Models\Carrier;
use App\Models\CollectionActivity;
use App\Models\Company;
use App\Models\CreditLimit;
use App\Models\CrmPipelineDefinition;
use App\Models\CrmPipelineStage;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Driver;
use App\Models\FollowUp;
use App\Models\GoodsIssue;
use App\Models\GoodsIssueItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\OfficialReceipt;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WmsLocation;
use App\Models\WmsTask;
use App\Models\WmsWarehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmDummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Bersihkan seluruh tabel
        WmsTask::truncate();
        WmsLocation::truncate();
        WmsWarehouse::truncate();
        Activity::truncate();
        FollowUp::truncate();
        ShipmentItem::truncate();
        Shipment::truncate();
        DeliveryOrderItem::truncate();
        DeliveryOrder::truncate();
        Driver::truncate();
        Carrier::truncate();
        ProductionOrderItem::truncate();
        ProductionOrder::truncate();
        SalesOrderItem::truncate();
        SalesOrder::truncate();
        QuotationItem::truncate();
        Quotation::truncate();
        GoodsReceiptItem::truncate();
        GoodsReceipt::truncate();
        GoodsIssueItem::truncate();
        GoodsIssue::truncate();
        StockAdjustmentItem::truncate();
        StockAdjustment::truncate();
        OfficialReceipt::truncate();
        CollectionActivity::truncate();
        PaymentAllocation::truncate();
        Payment::truncate();
        InvoiceItem::truncate();
        Invoice::truncate();
        CreditLimit::truncate();
        CustomerContact::truncate();
        Customer::truncate();
        Lead::truncate();
        InventoryItem::truncate();
        Warehouse::truncate();
        Branch::truncate();
        Company::truncate();
        Tenant::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 1. Buat Penyewa (Tenant), Perusahaan, dan Cabang Utama
        $tenantId = '11111111-1111-1111-1111-111111111111';
        $tenant = Tenant::create([
            'id' => $tenantId,
            'name' => 'Inakara Enterprise',
            'slug' => 'inakara-enterprise',
            'status' => 'active',
            'version' => 1,
        ]);

        app()->instance('current_tenant', $tenant);

        $company = Company::create([
            'id' => 1,
            'tenant_id' => $tenantId,
            'name' => 'PT Inakara Hospitality Furniture',
            'tax_id' => '01.987.654.3-999.000',
            'version' => 1,
        ]);

        $branch = Branch::create([
            'id' => 1,
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'name' => 'Pabrik & Showroom Utama Jepara',
            'code' => 'JPR-01',
            'version' => 1,
        ]);

        // 2. Hubungkan Pengguna ke Perusahaan & Cabang
        User::query()->update([
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $owner = User::find(1);
        if ($owner) {
            Auth::login($owner);
        }

        // 3. Buat Gudang WMS
        $mainWarehouse = Warehouse::create([
            'tenant_id' => $tenantId,
            'code' => 'WHS-01',
            'name' => 'Gudang Kayu Jati & Furnitur Jepara',
            'type' => 'standard',
            'is_default' => true,
            'status' => WarehouseStatus::Active,
            'address' => 'Jl. Raya Tahunan No. 88, Jepara, Jawa Tengah',
            'created_by' => 1,
        ]);

        $eastWarehouse = Warehouse::create([
            'tenant_id' => $tenantId,
            'code' => 'WHS-02',
            'name' => 'Gudang & Showroom Seminyak Bali',
            'type' => 'standard',
            'is_default' => false,
            'status' => WarehouseStatus::Active,
            'address' => 'Jl. Sunset Road No. 101, Seminyak, Badung, Bali',
            'created_by' => 1,
        ]);

        // 4. Buat Barang Inventaris WMS (Furnitur Hotel & Villa)
        $seededItems = [];
        $itemsData = [
            [
                'warehouse_id' => $mainWarehouse->id,
                'sku' => 'TEAK-BED-KING',
                'name' => 'Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)',
                'description' => 'Kayu jati solid kualitas grade-A untuk kamar hotel suite mewah',
                'quantity_current' => 45.00,
                'quantity_reserved' => 10.00,
                'unit' => 'pcs',
                'avg_cost_price' => 6500000.00,
            ],
            [
                'warehouse_id' => $mainWarehouse->id,
                'sku' => 'RATTAN-SUNBED',
                'name' => 'Sunbed Outdoor Rotan Sintetis (Aman Villa Series)',
                'description' => 'Sunbed rotan sintetis tahan cuaca untuk pooldeck & beach club',
                'quantity_current' => 120.00,
                'quantity_reserved' => 25.00,
                'unit' => 'pcs',
                'avg_cost_price' => 2800000.00,
            ],
            [
                'warehouse_id' => $mainWarehouse->id,
                'sku' => 'OAK-DINING-8',
                'name' => 'Meja Makan Jati Solid 8 Kursi (Resort Collection)',
                'description' => 'Meja makan jati solid finishing alami untuk restoran & dining room villa',
                'quantity_current' => 18.00,
                'quantity_reserved' => 2.00,
                'unit' => 'pcs',
                'avg_cost_price' => 8900000.00,
            ],
            [
                'warehouse_id' => $mainWarehouse->id,
                'sku' => 'SUNBRELLA-SOFA',
                'name' => 'Set Sofa Outdoor Weatherproof (Alila Resort Series)',
                'description' => 'Rangka jati dengan bantal kain anti-air Sunbrella',
                'quantity_current' => 12.00,
                'quantity_reserved' => 3.00,
                'unit' => 'set',
                'avg_cost_price' => 14500000.00,
            ],
            [
                'warehouse_id' => $mainWarehouse->id,
                'sku' => 'MARBLE-COFFEE',
                'name' => 'Meja Kopi Atasan Marmer Carrara Italia',
                'description' => 'Atasan marmer Carrara Italia dengan kaki besi lapis kuningan',
                'quantity_current' => 3.00, // Peringatan Stok Kritis!
                'quantity_reserved' => 1.00,
                'unit' => 'pcs',
                'avg_cost_price' => 4200000.00,
            ],
            [
                'warehouse_id' => $eastWarehouse->id,
                'sku' => 'TEAK-BED-KING',
                'name' => 'Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)',
                'description' => 'Kayu jati solid kualitas grade-A untuk kamar hotel suite mewah',
                'quantity_current' => 25.00,
                'quantity_reserved' => 5.00,
                'unit' => 'pcs',
                'avg_cost_price' => 6600000.00,
            ],
        ];

        foreach ($itemsData as $item) {
            $createdItem = InventoryItem::create(array_merge($item, [
                'tenant_id' => $tenantId,
                'created_by' => 1,
            ]));
            $seededItems[$createdItem->sku] = $createdItem;
        }

        // 5. Buat Data Pelanggan (Hotel, Villa, Resort, Studio Interior)
        $customers = [
            [
                'name' => 'PT Mulia Resort & Villa Nusa Dua',
                'company_name' => 'Mulia Resort Bali',
                'email' => 'procurement@muliaresortbali.com',
                'phone' => '+62-361-771777',
                'type' => 'company',
                'status' => CustomerStatus::Active,
                'notes' => 'Klien resort bintang 5 - kontrak rutin pembaruan furnitur suite.',
            ],
            [
                'name' => 'CV Seminyak Luxury Villa Developments',
                'company_name' => 'Seminyak Villa Dev',
                'email' => 'purchasing@seminyakvillas.co.id',
                'phone' => '+62-361-8452100',
                'type' => 'company',
                'status' => CustomerStatus::Active,
                'notes' => 'Pengembang villa boutique - paket jati & rotan custom nilai tinggi.',
            ],
            [
                'name' => 'PT Archipelago Hotel International',
                'company_name' => 'Archipelago Group',
                'email' => 'furniture.procurement@archipelagogroup.com',
                'phone' => '+62-21-8318800',
                'type' => 'company',
                'status' => CustomerStatus::Active,
                'notes' => 'Operator hotel besar - pembeli furnitur kontrak standar.',
            ],
            [
                'name' => 'Toko Royal Bali Interior Design Studio',
                'company_name' => 'Royal Bali Interior',
                'email' => 'design@royalbaliinterior.com',
                'phone' => '+62-361-9004455',
                'type' => 'retail',
                'status' => CustomerStatus::Active,
                'notes' => 'Konsultan desain interior hospitality & studio retail kelas atas.',
            ],
        ];

        $seededCustomers = [];
        foreach ($customers as $c) {
            $customer = Customer::create(array_merge($c, [
                'tenant_id' => $tenantId,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'version' => 1,
            ]));

            CustomerContact::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'first_name' => 'Robert',
                'last_name' => 'Hutapea',
                'email' => 'robert.h@'.explode('@', $customer->email)[1],
                'phone' => $customer->phone,
                'position' => 'Kepala Pengadaan Furnitur Hospitality',
                'is_primary' => true,
                'version' => 1,
            ]);

            $seededCustomers[$customer->name] = $customer;
        }

        // 6. Buat Limit Kredit Pelanggan
        CreditLimit::create([
            'tenant_id' => $tenantId,
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'credit_limit' => 1500000000.00,
            'outstanding_receivables' => 240000000.00,
            'pending_invoices' => 0.00,
            'pending_sales_orders' => 185000000.00,
            'is_on_hold' => false,
            'risk_category' => 'low',
            'credit_score' => 780,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'version' => 1,
        ]);

        CreditLimit::create([
            'tenant_id' => $tenantId,
            'customer_id' => $seededCustomers['CV Seminyak Luxury Villa Developments']->id,
            'credit_limit' => 850000000.00,
            'outstanding_receivables' => 195000000.00,
            'pending_invoices' => 0.00,
            'pending_sales_orders' => 195000000.00,
            'is_on_hold' => false,
            'risk_category' => 'medium',
            'credit_score' => 710,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'version' => 1,
        ]);

        CreditLimit::create([
            'tenant_id' => $tenantId,
            'customer_id' => $seededCustomers['PT Archipelago Hotel International']->id,
            'credit_limit' => 500000000.00,
            'outstanding_receivables' => 62500000.00,
            'pending_invoices' => 0.00,
            'pending_sales_orders' => 62500000.00,
            'is_on_hold' => false,
            'risk_category' => 'medium',
            'credit_score' => 725,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'version' => 1,
        ]);

        CreditLimit::create([
            'tenant_id' => $tenantId,
            'customer_id' => $seededCustomers['Toko Royal Bali Interior Design Studio']->id,
            'credit_limit' => 150000000.00,
            'outstanding_receivables' => 0.00,
            'pending_invoices' => 0.00,
            'pending_sales_orders' => 0.00,
            'is_on_hold' => false,
            'risk_category' => 'low',
            'credit_score' => 760,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'version' => 1,
        ]);

        // 7. Buat Prospek Penjualan (Leads Proyek Hotel & Villa)
        $leads = [
            [
                'name' => 'Hendra Setiawan',
                'company_name' => 'Proyek Perluasan Villa St. Regis Bali Resort',
                'email' => 'hendra.setiawan@stregisbali.com',
                'phone' => '+62-361-8498888',
                'source' => LeadSource::Digital,
                'status' => LeadStatus::Qualified,
                'priority' => 'high',
                'heat_score' => HeatScore::Hot,
                'version' => 1,
            ],
            [
                'name' => 'Maya Putri',
                'company_name' => 'Proyek Villa Eco Luxury Ubud Tahap 3',
                'email' => 'maya.putri@ubudecovillas.com',
                'phone' => '+62-813-3882991',
                'source' => LeadSource::Referral,
                'status' => LeadStatus::Contacted,
                'priority' => 'medium',
                'heat_score' => HeatScore::Warm,
                'version' => 1,
            ],
            [
                'name' => 'Bambang Gunawan',
                'company_name' => 'Fitout Ballroom Utama Hilton Resort',
                'email' => 'bambang.g@hiltonbali.com',
                'phone' => '+62-361-773322',
                'source' => LeadSource::Phone,
                'status' => LeadStatus::New,
                'priority' => 'high',
                'heat_score' => HeatScore::Warm,
                'version' => 1,
            ],
            [
                'name' => 'Sarah Jenkins',
                'company_name' => 'Proyek Homestay Boutique Canggu',
                'email' => 'sarah.j@canggu-homestays.com',
                'phone' => '+62-811-9922441',
                'source' => LeadSource::Digital,
                'status' => LeadStatus::Disqualified,
                'priority' => 'low',
                'heat_score' => HeatScore::Cold,
                'disqualification_reason' => 'Anggaran klien di bawah batas minimum kontrak hotel.',
                'version' => 1,
            ],
            [
                'name' => 'Made Wira',
                'company_name' => 'Fitout Outdoor Beach Club Seminyak',
                'email' => 'wira@seminyakbeachclub.com',
                'phone' => '+62-812-3617722',
                'source' => LeadSource::Referral,
                'status' => LeadStatus::Qualified,
                'priority' => 'high',
                'heat_score' => HeatScore::Hot,
                'version' => 1,
            ],
            [
                'name' => 'Adly Pratama',
                'company_name' => 'Proyek Private Villa Uluwatu',
                'email' => 'adly@tech.io',
                'phone' => '+62-819-3344556',
                'source' => LeadSource::Digital,
                'status' => LeadStatus::New,
                'priority' => 'medium',
                'heat_score' => HeatScore::Warm,
                'version' => 1,
            ],
        ];

        $seededLeads = [];
        foreach ($leads as $l) {
            $createdLead = Lead::create(array_merge($l, [
                'tenant_id' => $tenantId,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'assigned_to' => 4,
                'created_by' => 1,
            ]));
            $seededLeads[$createdLead->name] = $createdLead;
        }

        // 8. Buat Peluang Penjualan CRM (Opportunities)
        $pipeline = CrmPipelineDefinition::where('is_default', true)->first();
        if ($pipeline) {
            $proposalStage = CrmPipelineStage::where('pipeline_definition_id', $pipeline->id)->where('name', 'Proposal')->first();
            $negotiationStage = CrmPipelineStage::where('pipeline_definition_id', $pipeline->id)->where('name', 'Negotiation')->first();
            $wonStage = CrmPipelineStage::where('pipeline_definition_id', $pipeline->id)->where('name', 'Won')->first();

            if ($proposalStage && $negotiationStage && $wonStage) {
                Opportunity::create([
                    'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
                    'title' => 'Penataan Ulang Furnitur Jati 120 Kamar Nusa Dua Resort',
                    'pipeline_stage_id' => $proposalStage->id,
                    'status' => 'qualification',
                    'deal_value' => 1850000000.00,
                    'expected_close_date' => Carbon::now()->addDays(30),
                    'assigned_to' => 4,
                    'created_by' => 1,
                ]);

                Opportunity::create([
                    'customer_id' => $seededCustomers['CV Seminyak Luxury Villa Developments']->id,
                    'title' => 'Paket Furnitur Villa Mewah Tebing Seminyak (12 Unit)',
                    'pipeline_stage_id' => $negotiationStage->id,
                    'status' => 'negotiation',
                    'deal_value' => 640000000.00,
                    'expected_close_date' => Carbon::now()->addDays(15),
                    'assigned_to' => 4,
                    'created_by' => 1,
                ]);

                Opportunity::create([
                    'customer_id' => $seededCustomers['PT Archipelago Hotel International']->id,
                    'title' => 'Headboard Jati Custom & Sunbed Suite Uluwatu',
                    'pipeline_stage_id' => $wonStage->id,
                    'status' => 'won',
                    'deal_value' => 320000000.00,
                    'expected_close_date' => Carbon::now()->subDays(2),
                    'assigned_to' => 4,
                    'created_by' => 1,
                ]);
            }
        }

        // 9. Buat Surat Penawaran (Quotations)
        $quo1 = Quotation::create([
            'reference_no' => 'QUO/2026/001',
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'subject' => 'Kontrak Pengadaan Furnitur Jati Villa Suite Nusa Dua Resort',
            'revision' => 1,
            'status' => QuotationStatus::Accepted,
            'valid_until' => Carbon::now()->addDays(15)->format('Y-m-d'),
            'notes' => 'Kontrak mencakup 10 Tempat Tidur Jati King dengan garansi 2 tahun.',
            'currency' => 'IDR',
            'tax_rate' => 11.00,
            'subtotal' => 166666666.67,
            'tax_amount' => 18333333.33,
            'total_amount' => 185000000.00,
            'assigned_to' => 4,
            'created_by' => 1,
        ]);

        QuotationItem::create([
            'quotation_id' => $quo1->id,
            'description' => 'Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 16666666.67,
            'total_price' => 166666666.67,
            'sort_order' => 1,
        ]);

        $quo2 = Quotation::create([
            'reference_no' => 'QUO/2026/002',
            'customer_id' => $seededCustomers['CV Seminyak Luxury Villa Developments']->id,
            'subject' => 'Paket Furnitur Rotan & Jati Villa Seminyak (12 Unit)',
            'revision' => 2,
            'status' => QuotationStatus::Sent,
            'valid_until' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Diskon khusus 5% untuk 12 paket villa lengkap.',
            'currency' => 'IDR',
            'tax_rate' => 11.00,
            'subtotal' => 576576576.58,
            'tax_amount' => 63423423.42,
            'total_amount' => 640000000.00,
            'assigned_to' => 4,
            'created_by' => 1,
        ]);

        QuotationItem::create([
            'quotation_id' => $quo2->id,
            'description' => 'Sunbed Outdoor Rotan Sintetis (Aman Villa Series)',
            'quantity' => 50,
            'unit' => 'pcs',
            'unit_price' => 11531531.53,
            'total_price' => 576576576.58,
            'sort_order' => 1,
        ]);

        // 10. Buat Pesanan Penjualan (Sales Orders)
        $so1 = SalesOrder::create([
            'reference_no' => 'SO/2026/001',
            'quotation_id' => $quo1->id,
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'subject' => 'Pesanan Furnitur Jati Villa Beachfront Mulia Resort',
            'status' => SalesOrderStatus::Confirmed,
            'delivery_terms' => 'FOB Pelabuhan Denpasar / Pengiriman Langsung ke Hotel',
            'notes' => 'Produksi dijadwalkan untuk pengiriman bulan depan.',
            'currency' => 'IDR',
            'tax_rate' => 11.00,
            'subtotal' => 166666666.67,
            'tax_amount' => 18333333.33,
            'total_amount' => 185000000.00,
            'assigned_to' => 4,
            'created_by' => 1,
        ]);

        $soItem1 = SalesOrderItem::create([
            'sales_order_id' => $so1->id,
            'description' => 'Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 16666666.67,
            'total_price' => 166666666.67,
            'sort_order' => 1,
        ]);

        $so2 = SalesOrder::create([
            'reference_no' => 'SO/2026/002',
            'customer_id' => $seededCustomers['CV Seminyak Luxury Villa Developments']->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'subject' => 'Pesanan Set Jati Villa Seminyak Batch 1',
            'status' => SalesOrderStatus::InPreparation,
            'delivery_terms' => 'Bongkar Muat di Lokasi Proyek Seminyak',
            'notes' => 'Sunbed rotan outdoor dalam tahap pengujian kontrol kualitas.',
            'currency' => 'IDR',
            'tax_rate' => 11.00,
            'subtotal' => 175675675.68,
            'tax_amount' => 19324324.32,
            'total_amount' => 195000000.00,
            'assigned_to' => 4,
            'created_by' => 1,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so2->id,
            'description' => 'Sunbed Outdoor Rotan Sintetis (Aman Villa Series)',
            'quantity' => 30,
            'unit' => 'pcs',
            'unit_price' => 5855855.86,
            'total_price' => 175675675.68,
            'sort_order' => 1,
        ]);

        // 11. Buat Perintah Produksi Manufaktur (Production Orders)
        $po1 = ProductionOrder::create([
            'reference_no' => 'PO-2026-001',
            'sales_order_id' => $so1->id,
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'subject' => 'Produksi 20 Unit Rangka Tempat Tidur Jati King untuk Mulia Resort',
            'status' => ProductionOrderStatus::InProduction,
            'priority' => ProductionPriority::High,
            'target_completion_date' => Carbon::now()->addDays(14),
            'started_at' => Carbon::now()->subDays(5),
            'estimated_hours' => 120.00,
            'actual_hours' => 45.00,
            'production_notes' => 'Kiln drying kayu jati selesai. Dalam tahap pengukiran & pengampelasan.',
            'currency' => 'IDR',
            'tax_rate' => 11.00,
            'subtotal' => 166666666.67,
            'tax_amount' => 18333333.33,
            'total_amount' => 185000000.00,
            'assigned_to' => 1,
            'created_by' => 1,
        ]);

        $poItem1 = ProductionOrderItem::create([
            'production_order_id' => $po1->id,
            'sales_order_item_id' => $soItem1->id,
            'description' => 'Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)',
            'quantity' => 20,
            'unit' => 'pcs',
            'unit_price' => 8333333.33,
            'total_price' => 166666666.67,
            'sort_order' => 1,
        ]);

        $po2 = ProductionOrder::create([
            'reference_no' => 'PO-2026-002',
            'sales_order_id' => $so2->id,
            'customer_id' => $seededCustomers['CV Seminyak Luxury Villa Developments']->id,
            'subject' => 'Penganyaman 50 Unit Sunbed Rotan Pooldeck Villa Seminyak',
            'status' => ProductionOrderStatus::QualityControl,
            'priority' => ProductionPriority::Urgent,
            'target_completion_date' => Carbon::now()->addDays(3),
            'started_at' => Carbon::now()->subDays(12),
            'estimated_hours' => 80.00,
            'actual_hours' => 78.00,
            'production_notes' => 'Penganyaman selesai. Pemeriksaan akhir pelapisan anti-UV.',
            'currency' => 'IDR',
            'tax_rate' => 11.00,
            'subtotal' => 175675675.68,
            'tax_amount' => 19324324.32,
            'total_amount' => 195000000.00,
            'assigned_to' => 1,
            'created_by' => 1,
        ]);

        // 12. Buat Transaksi Pergudangan WMS
        $gr1 = GoodsReceipt::create([
            'reference_no' => 'GR-2026-001',
            'production_order_id' => $po1->id,
            'warehouse_id' => $mainWarehouse->id,
            'received_date' => Carbon::now()->subDays(2),
            'status' => GoodsReceiptStatus::Received,
            'notes' => 'Penerimaan 20 unit Tempat Tidur Jati dari Workshop Jepara ke Gudang.',
            'remark' => 'Inspeksi & lulus uji QC.',
            'created_by' => 1,
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $gr1->id,
            'production_order_item_id' => $poItem1->id,
            'sku' => 'TEAK-BED-KING',
            'description' => 'Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)',
            'quantity_received' => 20.00,
            'unit' => 'pcs',
            'unit_cost' => 6500000.00,
            'sort_order' => 1,
        ]);

        $gi1 = GoodsIssue::create([
            'reference_no' => 'GI-2026-001',
            'sales_order_id' => $so1->id,
            'warehouse_id' => $mainWarehouse->id,
            'issued_date' => Carbon::now()->subDays(1),
            'status' => GoodsIssueStatus::Issued,
            'notes' => 'Pengeluaran 10 unit Tempat Tidur Jati untuk pengiriman Mulia Resort.',
            'remark' => 'Dimuat ke Truk DK 8912 AB.',
            'created_by' => 1,
        ]);

        GoodsIssueItem::create([
            'goods_issue_id' => $gi1->id,
            'sales_order_item_id' => $soItem1->id,
            'sku' => 'TEAK-BED-KING',
            'description' => 'Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)',
            'quantity_issued' => 10.00,
            'unit' => 'pcs',
            'sort_order' => 1,
        ]);

        $sa1 = StockAdjustment::create([
            'reference_no' => 'SA-2026-001',
            'warehouse_id' => $mainWarehouse->id,
            'adjustment_date' => Carbon::now()->subDays(3),
            'status' => StockAdjustmentStatus::Approved,
            'notes' => 'Penyesuaian stok meja kopi marmer karena goresan halus saat transit.',
            'approval_note' => 'Disetujui untuk alokasi diskon outlet.',
            'approved_by' => 1,
            'created_by' => 1,
        ]);

        StockAdjustmentItem::create([
            'stock_adjustment_id' => $sa1->id,
            'inventory_item_id' => $seededItems['MARBLE-COFFEE']->id,
            'type' => StockAdjustmentType::Deduction,
            'quantity_adjusted' => 1.00,
            'unit_cost' => 4200000.00,
            'sort_order' => 1,
        ]);

        // 13. Buat Kurir, Pengemudi, Surat Jalan (DO) & Pengiriman (Shipment)
        $carrier1 = Carrier::create([
            'code' => 'JPR-FREIGHT',
            'name' => 'Jepara Furniture Cargo Express',
            'status' => 'active',
        ]);

        $carrier2 = Carrier::create([
            'code' => 'BALI-LOGISTICS',
            'name' => 'Bali Island Cargo & Logistik Hotel',
            'status' => 'active',
        ]);

        $driver1 = Driver::create([
            'name' => 'I Wayan Sudarma',
            'phone' => '+62-812-9876543',
            'vehicle_plate_no' => 'DK 8912 AB',
            'status' => 'active',
        ]);

        $driver2 = Driver::create([
            'name' => 'Kadek Budiman',
            'phone' => '+62-813-5544332',
            'vehicle_plate_no' => 'DK 3411 XX',
            'status' => 'active',
        ]);

        $do1 = DeliveryOrder::create([
            'reference_no' => 'DO-2026-001',
            'sales_order_id' => $so1->id,
            'warehouse_id' => $mainWarehouse->id,
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'approved',
            'shipping_address_snapshot' => ['address' => 'Kawasan Resort Nusa Dua Lot N5, Badung, Bali'],
            'billing_address_snapshot' => ['address' => 'Kawasan Resort Nusa Dua Lot N5, Badung, Bali'],
            'notes' => 'Harap ditangani dengan hati-hati. Furnitur kayu jati solid berat.',
            'created_by' => 1,
            'approved_by' => 1,
            'approved_at' => Carbon::now()->subDays(1),
        ]);

        $doItem1 = DeliveryOrderItem::create([
            'delivery_order_id' => $do1->id,
            'sales_order_item_id' => $soItem1->id,
            'sku' => 'TEAK-BED-KING',
            'description' => 'Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)',
            'quantity_requested' => 10.00,
            'quantity_shipped' => 10.00,
            'quantity_delivered' => 10.00,
            'unit' => 'pcs',
            'item_specifications_snapshot' => ['finish' => 'Natural Teak Oil', 'wood' => 'Grade A Teak'],
            'sort_order' => 1,
        ]);

        $shipment1 = Shipment::create([
            'delivery_order_id' => $do1->id,
            'reference_no' => 'SHP-2026-001',
            'carrier_id' => $carrier2->id,
            'driver_id' => $driver1->id,
            'courier_type' => 'Truk Khusus Perusahaan',
            'tracking_number' => 'TRK-NUSA-DUA-881',
            'status' => 'delivered',
            'estimated_cost' => 3500000.00,
            'actual_cost' => 3500000.00,
            'currency' => 'IDR',
            'exchange_rate' => 1.000000,
            'estimated_delivery_date' => Carbon::now()->addDays(1),
            'actual_delivery_date' => Carbon::now(),
            'created_by' => 1,
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment1->id,
            'delivery_order_item_id' => $doItem1->id,
            'quantity_shipped' => 10.00,
        ]);

        // 14. Buat Faktur Tagihan (Invoices)
        $invoices = [
            [
                'customer' => 'PT Mulia Resort & Villa Nusa Dua',
                'ref' => 'INV/2026/001',
                'status' => InvoiceStatus::Issued,
                'date' => Carbon::now()->subDays(5),
                'due' => Carbon::now()->addDays(20),
                'amount' => 120000000.00,
            ],
            [
                'customer' => 'PT Mulia Resort & Villa Nusa Dua',
                'ref' => 'INV/2026/002',
                'status' => InvoiceStatus::Overdue,
                'date' => Carbon::now()->subDays(45),
                'due' => Carbon::now()->subDays(15),
                'amount' => 120000000.00,
            ],
            [
                'customer' => 'CV Seminyak Luxury Villa Developments',
                'ref' => 'INV/2026/003',
                'status' => InvoiceStatus::Overdue,
                'date' => Carbon::now()->subDays(75),
                'due' => Carbon::now()->subDays(45),
                'amount' => 120000000.00,
            ],
            [
                'customer' => 'CV Seminyak Luxury Villa Developments',
                'ref' => 'INV/2026/004',
                'status' => InvoiceStatus::Overdue,
                'date' => Carbon::now()->subDays(35),
                'due' => Carbon::now()->subDays(5),
                'amount' => 75000000.00,
            ],
            [
                'customer' => 'PT Archipelago Hotel International',
                'ref' => 'INV/2026/005',
                'status' => InvoiceStatus::Issued,
                'date' => Carbon::now()->subDays(2),
                'due' => Carbon::now()->addDays(14),
                'amount' => 62500000.00,
            ],
        ];

        $seededInvoices = [];
        foreach ($invoices as $inv) {
            $c = $seededCustomers[$inv['customer']];
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'reference_no' => $inv['ref'],
                'customer_id' => $c->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'status' => $inv['status'],
                'invoice_date' => $inv['date'],
                'due_date' => $inv['due'],
                'payment_term_code' => 'NET30',
                'subtotal' => $inv['amount'] / 1.11,
                'tax_amount' => ($inv['amount'] / 1.11) * 0.11,
                'total_amount' => $inv['amount'],
                'outstanding_balance' => $inv['amount'],
                'currency' => 'IDR',
                'exchange_rate' => 1.000000,
                'billing_address_snapshot' => ['address' => 'Kawasan Resort Nusa Dua Lot N5, Badung, Bali'],
                'shipping_address_snapshot' => ['address' => 'Kawasan Resort Nusa Dua Lot N5, Badung, Bali'],
                'created_by' => 1,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'sku' => 'TEAK-BED-KING',
                'description' => 'Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)',
                'quantity' => 10,
                'unit' => 'pcs',
                'unit_price' => $invoice->subtotal / 10,
                'discount_percentage' => 0.00,
                'discount_amount' => 0.00,
                'tax_percentage' => 11.00,
                'tax_amount' => $invoice->tax_amount,
                'total_amount' => $invoice->total_amount,
            ]);

            $seededInvoices[$invoice->reference_no] = $invoice;
        }

        // 15. Buat Pembayaran (Payments) & Kuitansi Resmi (Official Receipts)
        $pastInvoice = Invoice::create([
            'tenant_id' => $tenantId,
            'reference_no' => 'INV/2026/009',
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => InvoiceStatus::Issued,
            'invoice_date' => Carbon::now()->subDays(20),
            'due_date' => Carbon::now()->addDays(10),
            'payment_term_code' => 'NET30',
            'subtotal' => 135135135.14,
            'tax_amount' => 14864864.86,
            'total_amount' => 150000000.00,
            'outstanding_balance' => 0.00,
            'currency' => 'IDR',
            'exchange_rate' => 1.000000,
            'billing_address_snapshot' => ['address' => 'Kawasan Resort Nusa Dua Lot N5, Badung, Bali'],
            'shipping_address_snapshot' => ['address' => 'Kawasan Resort Nusa Dua Lot N5, Badung, Bali'],
            'created_by' => 1,
        ]);

        $payment1 = Payment::create([
            'tenant_id' => $tenantId,
            'reference_no' => 'PAY-2026-001',
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => PaymentStatus::Posted,
            'payment_date' => Carbon::now()->subDays(10),
            'payment_method' => PaymentMethodType::BankTransfer,
            'amount' => 150000000.00,
            'allocated_amount' => 150000000.00,
            'unallocated_amount' => 0.00,
            'base_currency' => 'IDR',
            'transaction_currency' => 'IDR',
            'exchange_rate' => 1.000000,
            'bank_name' => 'BCA',
            'bank_account_no' => '8009988771',
            'transaction_ref' => 'TRX-HOTEL-PAY-01',
            'created_by' => 1,
        ]);

        PaymentAllocation::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $pastInvoice->id,
            'amount' => 150000000.00,
            'notes' => 'Pelunasan faktur kontrak furnitur villa suite INV/2026/009',
        ]);

        OfficialReceipt::create([
            'payment_id' => $payment1->id,
            'receipt_no' => 'OR-2026-001',
            'status' => 'issued',
            'sent_at' => Carbon::now()->subDays(9),
            'printed_at' => Carbon::now()->subDays(9),
        ]);

        $retailInvoice = Invoice::create([
            'tenant_id' => $tenantId,
            'reference_no' => 'INV/2026/010',
            'customer_id' => $seededCustomers['Toko Royal Bali Interior Design Studio']->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => InvoiceStatus::Issued,
            'invoice_date' => Carbon::now()->subDays(15),
            'due_date' => Carbon::now()->addDays(15),
            'payment_term_code' => 'COD',
            'subtotal' => 22522522.52,
            'tax_amount' => 2477477.48,
            'total_amount' => 25000000.00,
            'outstanding_balance' => 0.00,
            'currency' => 'IDR',
            'exchange_rate' => 1.000000,
            'billing_address_snapshot' => ['address' => 'Jl. Sunset Road No. 45, Seminyak, Bali'],
            'shipping_address_snapshot' => ['address' => 'Jl. Sunset Road No. 45, Seminyak, Bali'],
            'created_by' => 1,
        ]);

        $payment2 = Payment::create([
            'tenant_id' => $tenantId,
            'reference_no' => 'PAY-2026-002',
            'customer_id' => $seededCustomers['Toko Royal Bali Interior Design Studio']->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => PaymentStatus::Posted,
            'payment_date' => Carbon::now()->subDays(2),
            'payment_method' => PaymentMethodType::BankTransfer,
            'amount' => 25000000.00,
            'allocated_amount' => 25000000.00,
            'unallocated_amount' => 0.00,
            'base_currency' => 'IDR',
            'transaction_currency' => 'IDR',
            'exchange_rate' => 1.000000,
            'created_by' => 1,
        ]);

        PaymentAllocation::create([
            'payment_id' => $payment2->id,
            'invoice_id' => $retailInvoice->id,
            'amount' => 25000000.00,
            'notes' => 'Pelunasan faktur sampel furnitur interior INV/2026/010',
        ]);

        OfficialReceipt::create([
            'payment_id' => $payment2->id,
            'receipt_no' => 'OR-2026-002',
            'status' => 'issued',
            'sent_at' => Carbon::now()->subDays(1),
            'printed_at' => Carbon::now()->subDays(1),
        ]);

        // 16. Buat Penagihan Piutang & Aktivitas CRM & Tugas FollowUp
        CollectionActivity::create([
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'invoice_id' => $seededInvoices['INV/2026/002']->id,
            'activity_type' => CollectionActivityType::WhatsApp,
            'status' => 'completed',
            'notes' => 'Kirim pengingat faktur otomatis via WA ke Pak Robert (Pengadaan Mulia). Pak Robert mengonfirmasi pembayaran dijadwalkan Senin depan setelah inspeksi akhir villa.',
            'assigned_to' => 5,
            'created_by' => 5,
        ]);

        CollectionActivity::create([
            'customer_id' => $seededCustomers['CV Seminyak Luxury Villa Developments']->id,
            'invoice_id' => $seededInvoices['INV/2026/003']->id,
            'activity_type' => CollectionActivityType::PhoneCall,
            'status' => 'completed',
            'promise_amount' => 120000000.00,
            'promise_date' => Carbon::now()->addDays(5),
            'notes' => 'Telepon supervisor keuangan Villa Seminyak. Berjanji melunasi faktur sunbed rotan Jumat depan.',
            'next_follow_up_date' => Carbon::now()->addDays(6),
            'assigned_to' => 5,
            'created_by' => 5,
        ]);

        $act1 = Activity::create([
            'tenant_id' => $tenantId,
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'lead_id' => $seededLeads['Hendra Setiawan']->id,
            'type' => 'meeting',
            'subject' => 'Presentasi Teknis Sampel Kayu Jati Jepara St. Regis Bali Resort',
            'description' => 'Mempresentasikan sampel kayu jati Jepara grade-A dan uji ketahanan cuaca ke GM & Chief Engineer.',
            'occurred_at' => Carbon::now()->subDays(3)->toDateTimeString(),
            'created_by' => 4,
        ]);

        $act2 = Activity::create([
            'tenant_id' => $tenantId,
            'customer_id' => $seededCustomers['CV Seminyak Luxury Villa Developments']->id,
            'type' => 'visit',
            'subject' => 'Inspeksi Lapangan & Pengukuran Dimensi Pooldeck Villa Seminyak',
            'description' => 'Menginspeksi area pooldeck untuk tata letak dan dimensi sunbed rotan sintetis custom.',
            'occurred_at' => Carbon::now()->subDays(1)->toDateTimeString(),
            'created_by' => 4,
        ]);

        FollowUp::create([
            'tenant_id' => $tenantId,
            'customer_id' => $seededCustomers['PT Mulia Resort & Villa Nusa Dua']->id,
            'activity_id' => $act1->id,
            'title' => 'Kirim Sampel Finishing Jati & Penawaran Revisi ke St. Regis',
            'description' => 'Kirim fisik sampel finishing minyak jati ke kantor St. Regis Nusa Dua via kurir.',
            'due_date' => Carbon::now()->addDays(2)->toDateTimeString(),
            'priority' => 'high',
            'status' => 'pending',
            'assigned_to' => 4,
            'created_by' => 4,
        ]);

        FollowUp::create([
            'tenant_id' => $tenantId,
            'customer_id' => $seededCustomers['CV Seminyak Luxury Villa Developments']->id,
            'activity_id' => $act2->id,
            'title' => 'Finalisasi Pilihan Warna Kain Bantal Sunbrella',
            'description' => 'Mengonfirmasi pilihan warna kain Sunbrella biru navy dengan lead designer Villa Seminyak.',
            'due_date' => Carbon::now()->subDays(1)->toDateTimeString(),
            'priority' => 'medium',
            'status' => 'completed',
            'assigned_to' => 4,
            'completed_at' => Carbon::now()->subDays(1)->toDateTimeString(),
            'created_by' => 4,
        ]);

        // 17. Buat WMS Physical Network Topology & Locations & Wave Tasks
        $wmsWh1 = WmsWarehouse::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'WMS-JPR-01',
            'name' => 'Jepara Master Timber & Furniture Warehouse',
            'type' => 'central',
            'status' => 'active',
            'version' => 1,
        ]);

        $wmsWh2 = WmsWarehouse::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'WMS-DPS-01',
            'name' => 'Bali Sunset Road Showroom & Hub',
            'type' => 'regional',
            'status' => 'active',
            'version' => 1,
        ]);

        $loc1 = WmsLocation::create([
            'warehouse_id' => $wmsWh1->id,
            'type' => 'rack',
            'code' => 'JPR-A1-TEAK',
            'max_weight' => 5000.00,
            'max_volume' => 100.00,
            'current_weight' => 2400.00,
            'current_volume' => 45.00,
            'status' => 'active',
            'version' => 1,
        ]);

        $loc2 = WmsLocation::create([
            'warehouse_id' => $wmsWh1->id,
            'type' => 'rack',
            'code' => 'JPR-B2-RATTAN',
            'max_weight' => 3000.00,
            'max_volume' => 80.00,
            'current_weight' => 1200.00,
            'current_volume' => 35.00,
            'status' => 'active',
            'version' => 1,
        ]);

        WmsTask::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'type' => 'picking',
            'status' => 'pending',
            'priority' => 1,
            'assigned_to' => 2, // Gudang user
            'source_location_id' => $loc1->id,
            'sku' => 'TEAK-BED-KING',
            'quantity' => 10.00,
            'version' => 1,
        ]);
    }
}
