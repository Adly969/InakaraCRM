import React, { useState } from 'react';
import { useForm, Head } from '@inertiajs/react';
import AuthLayout from '@/layouts/auth-layout';

export default function OnboardingWizard() {
    const [step, setStep] = useState(1);
    
    const { data, setData, post, processing, errors } = useForm({
        tenant_name: '',
        company_name: '',
        company_tax_id: '',
        branch_name: '',
        branch_code: '',
        owner_name: '',
        owner_email: '',
        owner_password: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/register');
    };

    const nextStep = () => {
        // Validate local step requirements
        if (step === 1 && !data.tenant_name) return;
        if (step === 2 && !data.company_name) return;
        if (step === 3 && (!data.branch_name || !data.branch_code)) return;
        setStep(step + 1);
    };

    const prevStep = () => {
        setStep(step - 1);
    };

    return (
        <AuthLayout>
            <Head title="Create Tenant Account" />
            <div className="w-full max-w-lg p-8 mx-auto bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl transition-all duration-300">
                <div className="mb-8">
                    <h1 className="text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 mb-2">
                        Get Started with Inakara
                    </h1>
                    <p className="text-sm text-zinc-500 dark:text-zinc-400">
                        Setup your isolated tenant workspace in under 2 minutes.
                    </p>
                </div>

                {/* Progress Indicators */}
                <div className="flex items-center justify-between mb-8 relative">
                    <div className="absolute left-0 right-0 top-1/2 h-0.5 bg-zinc-200 dark:bg-zinc-800 -translate-y-1/2 -z-10" />
                    {[1, 2, 3, 4].map((num) => (
                        <div
                            key={num}
                            className={`w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all duration-300 ${
                                step >= num
                                    ? 'bg-zinc-900 dark:bg-zinc-50 text-white dark:text-zinc-950 border-zinc-900 dark:border-zinc-50'
                                    : 'bg-white dark:bg-zinc-900 text-zinc-400 border-zinc-200 dark:border-zinc-850'
                            }`}
                        >
                            {num}
                        </div>
                    ))}
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Step 1: Tenant Info */}
                    {step === 1 && (
                        <div className="space-y-4 animate-fade-in">
                            <h2 className="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                Step 1: Tenant Workspace
                            </h2>
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Workspace Name
                                </label>
                                <input
                                    type="text"
                                    value={data.tenant_name}
                                    onChange={(e) => setData('tenant_name', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-zinc-500 transition-colors duration-200"
                                    placeholder="e.g. Nusa Indah Manufacturing"
                                />
                                {errors.tenant_name && (
                                    <span className="text-xs text-red-500 mt-1 block">{errors.tenant_name}</span>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Step 2: Company Info */}
                    {step === 2 && (
                        <div className="space-y-4 animate-fade-in">
                            <h2 className="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                Step 2: Company Profile
                            </h2>
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Company Name
                                    </label>
                                    <input
                                        type="text"
                                        value={data.company_name}
                                        onChange={(e) => setData('company_name', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-zinc-500 transition-colors duration-200"
                                        placeholder="e.g. PT Nusa Indah Sejahtera"
                                    />
                                    {errors.company_name && (
                                        <span className="text-xs text-red-500 mt-1 block">{errors.company_name}</span>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Tax ID / NPWP (Optional)
                                    </label>
                                    <input
                                        type="text"
                                        value={data.company_tax_id}
                                        onChange={(e) => setData('company_tax_id', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-zinc-500 transition-colors duration-200"
                                        placeholder="e.g. 01.234.567.8-999.000"
                                    />
                                    {errors.company_tax_id && (
                                        <span className="text-xs text-red-500 mt-1 block">{errors.company_tax_id}</span>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Step 3: Branch Info */}
                    {step === 3 && (
                        <div className="space-y-4 animate-fade-in">
                            <h2 className="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                Step 3: Primary Branch
                            </h2>
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Branch Name
                                    </label>
                                    <input
                                        type="text"
                                        value={data.branch_name}
                                        onChange={(e) => setData('branch_name', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-zinc-500 transition-colors duration-200"
                                        placeholder="e.g. Pabrik Utama Surabaya"
                                    />
                                    {errors.branch_name && (
                                        <span className="text-xs text-red-500 mt-1 block">{errors.branch_name}</span>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Branch Code
                                    </label>
                                    <input
                                        type="text"
                                        value={data.branch_code}
                                        onChange={(e) => setData('branch_code', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-zinc-500 transition-colors duration-200"
                                        placeholder="e.g. SUR-01"
                                    />
                                    {errors.branch_code && (
                                        <span className="text-xs text-red-500 mt-1 block">{errors.branch_code}</span>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Step 4: Admin Owner User */}
                    {step === 4 && (
                        <div className="space-y-4 animate-fade-in">
                            <h2 className="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                Step 4: Administrator Credentials
                            </h2>
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Full Name
                                    </label>
                                    <input
                                        type="text"
                                        value={data.owner_name}
                                        onChange={(e) => setData('owner_name', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-zinc-500 transition-colors duration-200"
                                        placeholder="e.g. Adly Rafi"
                                    />
                                    {errors.owner_name && (
                                        <span className="text-xs text-red-500 mt-1 block">{errors.owner_name}</span>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Email Address
                                    </label>
                                    <input
                                        type="email"
                                        value={data.owner_email}
                                        onChange={(e) => setData('owner_email', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-zinc-500 transition-colors duration-200"
                                        placeholder="e.g. adly@nusaindah.co.id"
                                    />
                                    {errors.owner_email && (
                                        <span className="text-xs text-red-500 mt-1 block">{errors.owner_email}</span>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Password
                                    </label>
                                    <input
                                        type="password"
                                        value={data.owner_password}
                                        onChange={(e) => setData('owner_password', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-zinc-500 transition-colors duration-200"
                                        placeholder="••••••••"
                                    />
                                    {errors.owner_password && (
                                        <span className="text-xs text-red-500 mt-1 block">{errors.owner_password}</span>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Navigation Buttons */}
                    <div className="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-800 mt-6">
                        {step > 1 ? (
                            <button
                                type="button"
                                onClick={prevStep}
                                className="px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-50 transition-colors"
                            >
                                Back
                            </button>
                        ) : (
                            <div />
                        )}

                        {step < 4 ? (
                            <button
                                type="button"
                                onClick={nextStep}
                                className="px-6 py-2.5 text-sm font-semibold text-white dark:text-zinc-950 bg-zinc-900 dark:bg-zinc-50 hover:bg-zinc-850 dark:hover:bg-zinc-100 rounded-lg shadow-sm transition-all duration-200 hover:scale-[1.02]"
                            >
                                Continue
                            </button>
                        ) : (
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-6 py-2.5 text-sm font-semibold text-white dark:text-zinc-950 bg-zinc-900 dark:bg-zinc-50 hover:bg-zinc-850 dark:hover:bg-zinc-100 rounded-lg shadow-sm transition-all duration-200 disabled:opacity-55 hover:scale-[1.02]"
                            >
                                {processing ? 'Provisioning...' : 'Complete Registration'}
                            </button>
                        )}
                    </div>
                </form>
            </div>
        </AuthLayout>
    );
}
