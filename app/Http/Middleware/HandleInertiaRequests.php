<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'title' => $user->title,
                    'bio' => $user->bio,
                    'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toIso8601String() : null,
                    'is_active' => $user->is_active,
                    'roles' => $user->getRoleNames()->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    'created_at' => $user->created_at ? $user->created_at->toIso8601String() : null,
                    'updated_at' => $user->updated_at ? $user->updated_at->toIso8601String() : null,
                ] : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'demoMode' => env('APP_DEMO_MODE', true) && app()->environment() !== 'production',
            'ssoGoogle' => ! empty(env('GOOGLE_CLIENT_ID')),
            'ssoEnterprise' => env('SSO_ENABLED', false),
            'appVersion' => env('APP_VERSION', '1.0.0'),
            'appEnv' => app()->environment(),
            'currentTenant' => app()->bound('current_tenant') ? [
                'id' => app('current_tenant')->id,
                'name' => app('current_tenant')->name,
                'slug' => app('current_tenant')->slug,
                'status' => app('current_tenant')->status,
                'subscription' => app('current_tenant')->subscription ? [
                    'plan_name' => app('current_tenant')->subscription->plan_name,
                    'status' => app('current_tenant')->subscription->status,
                    'ends_at' => app('current_tenant')->subscription->ends_at ? app('current_tenant')->subscription->ends_at->toIso8601String() : null,
                ] : null,
                'features' => app('current_tenant')->featureFlags()->pluck('is_enabled', 'feature_code')->toArray(),
            ] : null,
        ];
    }
}
