<?php

namespace Tests\Feature;

use App\Enums\ActivityOutcome;
use App\Enums\CrmActivityStatus;
use App\Enums\CrmActivityType;
use App\Enums\Permission;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CrmActivity;
use App\Models\CrmTask;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmOperationalSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'test-tenant', 'domain' => 'test']);
        $company = Company::create(['name' => 'Test Company', 'tenant_id' => $tenant->id]);
        $branch = Branch::create(['name' => 'Test Branch', 'company_id' => $company->id, 'tenant_id' => $tenant->id, 'code' => 'TB01']);
        $this->user = User::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
        $this->user->givePermissionTo([
            Permission::ViewActivities->value,
            Permission::CreateActivities->value,
            Permission::EditActivities->value,
            Permission::ViewTasks->value,
            Permission::CreateTasks->value,
            Permission::EditTasks->value,
            Permission::ViewCalendar->value,
            Permission::CreateCalendarEvents->value,
            Permission::ViewDocuments->value,
            Permission::UploadDocuments->value,
        ]);
    }

    public function test_can_list_and_create_activities(): void
    {
        $response = $this->actingAs($this->user)->get(route('crm.activities.index'));
        $response->assertStatus(200);

        $createResponse = $this->actingAs($this->user)->post(route('crm.activities.store'), [
            'activity_type' => CrmActivityType::PhoneCall->value,
            'subject' => 'Follow up Telepon Pelanggan Kayu Jati',
            'start_time' => now()->toIso8601String(),
            'priority' => 'high',
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('crm_activities', [
            'subject' => 'Follow up Telepon Pelanggan Kayu Jati',
            'priority' => 'high',
        ]);
    }

    public function test_can_complete_activity_with_outcome(): void
    {
        $activity = CrmActivity::create([
            'activity_type' => CrmActivityType::Meeting,
            'subject' => 'Rapat Penawaran Meja Makan',
            'start_time' => now(),
            'status' => CrmActivityStatus::Pending,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('crm.activities.complete', $activity), [
            'outcome' => ActivityOutcome::Interested->value,
            'notes' => 'Pelanggan tertarik dengan kayu jati grade A',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('crm_activities', [
            'id' => $activity->id,
            'status' => CrmActivityStatus::Completed->value,
            'outcome' => ActivityOutcome::Interested->value,
        ]);
    }

    public function test_can_create_and_toggle_task(): void
    {
        $createResponse = $this->actingAs($this->user)->post(route('crm.tasks.store'), [
            'title' => 'Kirim Sample Kayu Mahoni',
            'due_date' => now()->addDays(2)->toDateString(),
            'priority' => TaskPriority::High->value,
            'checklists' => ['Packing sampel', 'Kirim via JNE'],
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('crm_tasks', [
            'title' => 'Kirim Sample Kayu Mahoni',
        ]);

        $task = CrmTask::where('title', 'Kirim Sample Kayu Mahoni')->firstOrFail();
        $this->assertCount(2, $task->checklists);

        $statusResponse = $this->actingAs($this->user)->patch(route('crm.tasks.status', $task), [
            'status' => TaskStatus::Completed->value,
        ]);

        $statusResponse->assertRedirect();
        $this->assertDatabaseHas('crm_tasks', [
            'id' => $task->id,
            'status' => TaskStatus::Completed->value,
        ]);
    }

    public function test_timeline_api_returns_json(): void
    {
        $customer = Customer::factory()->create();

        CrmActivity::create([
            'activity_type' => CrmActivityType::Note,
            'subject' => 'Catatan Kebutuhan Pelanggan',
            'start_time' => now(),
            'customer_id' => $customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('api.timeline.customer', $customer->id));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => 'Catatan Kebutuhan Pelanggan',
        ]);
    }
}
