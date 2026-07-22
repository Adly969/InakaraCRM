<?php

namespace App\Providers;

use App\Enums\Permission as AppPermission;
use App\Enums\UserRole;
use App\Listeners\AuthenticationLogger;
use App\Models\CalendarEvent;
use App\Models\CrmActivity;
use App\Models\CrmDocument;
use App\Models\CrmTask;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseTask;
use App\Policies\ActivityPolicy;
use App\Policies\CalendarEventPolicy;
use App\Policies\CrmDocumentPolicy;
use App\Policies\CrmTaskPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\LeadPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehousePolicy;
use App\Policies\WarehouseTaskPolicy;
use App\Repositories\FinancialEventRepository;
use App\Repositories\FinancialEventRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            FinancialEventRepositoryInterface::class,
            FinancialEventRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureLogging();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure Gate authorization, policies, and Owner role bypass.
     */
    protected function configureAuthorization(): void
    {
        // Owner role bypasses all checks
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole(UserRole::Owner->value)) {
                return true;
            }
        });

        // Register policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(CrmActivity::class, ActivityPolicy::class);
        Gate::policy(CrmTask::class, CrmTaskPolicy::class);
        Gate::policy(CalendarEvent::class, CalendarEventPolicy::class);
        Gate::policy(CrmDocument::class, CrmDocumentPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(WarehouseTask::class, WarehouseTaskPolicy::class);

        // Define foundational gates
        Gate::define('access-admin-panel', function (User $user) {
            return $user->hasRole(UserRole::Admin->value) || $user->hasRole(UserRole::Owner->value);
        });

        Gate::define('view-any-dashboard', function (User $user) {
            return $user->hasPermissionTo(AppPermission::ViewDashboard->value);
        });
    }

    /**
     * Register event subscribers.
     */
    protected function configureLogging(): void
    {
        Event::subscribe(AuthenticationLogger::class);
    }
}
