import { Head, useForm } from '@inertiajs/react';
import { useRef, useState } from 'react';
import { Shield, Info, Check, Circle, Monitor, Smartphone, ExternalLink, KeyRound } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/input-error';
import { edit as editSecurity } from '@/routes/security';
import { toast } from 'sonner';

type Props = {
    passwordRules: string;
};

export default function Security(props: Props) {
    const { data, setData, put, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const [isTwoFactorEnabled, setIsTwoFactorEnabled] = useState(false);
    const [showTwoFactorModal, setShowTwoFactorModal] = useState(false);
    const [twoFactorCode, setTwoFactorCode] = useState('');
    const [twoFactorError, setTwoFactorError] = useState('');

    // Mock sessions state for interactive Revoke actions
    const [sessions, setSessions] = useState([
        { id: 1, device: 'MacBook Pro 16"', os: 'Chrome on macOS', location: 'New York, USA', status: 'Active now', current: true },
        { id: 2, device: 'iPhone 14 Pro', os: 'Safari on iOS', location: 'Boston, USA', status: '2 hours ago', current: false },
    ]);

    const handleUpdatePassword = (e: React.FormEvent) => {
        e.preventDefault();
        put('/settings/password', {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                toast.success('Password updated successfully.');
            },
        });
    };

    const handleRevokeSession = (id: number, deviceName: string) => {
        setSessions(sessions.filter((s) => s.id !== id));
        toast.success(`Session for ${deviceName} revoked successfully.`);
    };

    const handleRevokeAll = () => {
        setSessions(sessions.filter((s) => s.current));
        toast.success('All other sessions revoked successfully.');
    };

    const handleToggleTwoFactor = () => {
        if (isTwoFactorEnabled) {
            setIsTwoFactorEnabled(false);
            toast.success('Two-factor authentication disabled.');
        } else {
            setShowTwoFactorModal(true);
        }
    };

    const handleVerifyTwoFactor = (e: React.FormEvent) => {
        e.preventDefault();
        if (twoFactorCode.length === 6 && /^\d+$/.test(twoFactorCode)) {
            setIsTwoFactorEnabled(true);
            setShowTwoFactorModal(false);
            setTwoFactorCode('');
            setTwoFactorError('');
            toast.success('Two-factor authentication enabled successfully.');
        } else {
            setTwoFactorError('Invalid verification code. Please enter a 6-digit number.');
        }
    };

    // Live password requirement validation
    const hasMinLength = data.password.length >= 8;
    const hasUppercase = /[A-Z]/.test(data.password);
    const hasNumberOrSymbol = /[0-9\W]/.test(data.password);

    return (
        <>
            <Head title="Security settings" />

            <h1 className="sr-only">Security settings</h1>

            <div className="space-y-6">
                <div>
                    <h2 className="text-xl font-bold text-neutral-900 dark:text-neutral-100">Security & Authentication</h2>
                    <p className="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                        Manage your account security, passwords, and active sessions.
                    </p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    {/* Left Column: Password and Sessions */}
                    <div className="lg:col-span-8 space-y-6">
                        {/* Change Password Card */}
                        <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-xl overflow-hidden shadow-sm">
                            <CardHeader className="border-b border-neutral-100 dark:border-neutral-850/60 p-5 flex flex-row items-center gap-2">
                                <KeyRound className="h-5 w-5 text-neutral-500" />
                                <CardTitle className="text-sm font-bold text-neutral-800 dark:text-neutral-250">Change Password</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <form onSubmit={handleUpdatePassword} className="space-y-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="current_password">CURRENT PASSWORD</Label>
                                        <Input
                                            id="current_password"
                                            type="password"
                                            value={data.current_password}
                                            onChange={(e) => setData('current_password', e.target.value)}
                                            placeholder="Enter current password"
                                            required
                                        />
                                        <InputError message={errors.current_password} />
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="password">NEW PASSWORD</Label>
                                            <Input
                                                id="password"
                                                type="password"
                                                value={data.password}
                                                onChange={(e) => setData('password', e.target.value)}
                                                placeholder="Enter new password"
                                                required
                                            />
                                            <InputError message={errors.password} />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="password_confirmation">CONFIRM PASSWORD</Label>
                                            <Input
                                                id="password_confirmation"
                                                type="password"
                                                value={data.password_confirmation}
                                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                                placeholder="Confirm new password"
                                                required
                                            />
                                            <InputError message={errors.password_confirmation} />
                                        </div>
                                    </div>

                                    {/* Password Requirements */}
                                    <div className="bg-[#f0f4f9] dark:bg-neutral-800/40 rounded-lg p-4 space-y-2 mt-2">
                                        <h4 className="text-xs font-bold text-neutral-700 dark:text-neutral-300">Password Requirements:</h4>
                                        <div className="space-y-1.5 text-xs text-neutral-600 dark:text-neutral-450">
                                            <div className="flex items-center gap-2">
                                                {hasMinLength ? (
                                                    <Check className="h-4.5 w-4.5 text-green-600 dark:text-green-400 shrink-0" />
                                                ) : (
                                                    <Circle className="h-3.5 w-3.5 text-neutral-400 dark:text-neutral-500 fill-none stroke-2 mx-0.5 shrink-0" />
                                                )}
                                                <span>Minimum 8 characters</span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                {hasUppercase ? (
                                                    <Check className="h-4.5 w-4.5 text-green-600 dark:text-green-400 shrink-0" />
                                                ) : (
                                                    <Circle className="h-3.5 w-3.5 text-neutral-400 dark:text-neutral-500 fill-none stroke-2 mx-0.5 shrink-0" />
                                                )}
                                                <span>At least one uppercase letter</span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                {hasNumberOrSymbol ? (
                                                    <Check className="h-4.5 w-4.5 text-green-600 dark:text-green-400 shrink-0" />
                                                ) : (
                                                    <Circle className="h-3.5 w-3.5 text-neutral-400 dark:text-neutral-500 fill-none stroke-2 mx-0.5 shrink-0" />
                                                )}
                                                <span>At least one number or symbol</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex justify-end pt-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="bg-[#0052cc] hover:bg-[#0747a6] text-white px-5 py-2 font-semibold text-xs rounded-md"
                                        >
                                            Update Password
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>

                        {/* Active Sessions Card */}
                        <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-xl overflow-hidden shadow-sm">
                            <CardHeader className="border-b border-neutral-100 dark:border-neutral-850/60 p-5 flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <Monitor className="h-5 w-5 text-neutral-500" />
                                    <CardTitle className="text-sm font-bold text-neutral-800 dark:text-neutral-250">Active Sessions</CardTitle>
                                </div>
                                {sessions.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={handleRevokeAll}
                                        className="text-[#d93838] hover:text-red-750 font-semibold text-xs transition-colors"
                                    >
                                        Revoke All
                                    </button>
                                )}
                            </CardHeader>
                            <CardContent className="p-0 divide-y divide-neutral-150 dark:divide-neutral-800">
                                {sessions.map((session) => (
                                    <div key={session.id} className="p-5 flex items-center justify-between gap-4">
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 rounded-lg bg-neutral-50 dark:bg-neutral-850 border border-neutral-200 dark:border-neutral-800 flex items-center justify-center text-neutral-600 dark:text-neutral-400 shrink-0">
                                                {session.device.includes('iPhone') ? (
                                                    <Smartphone className="h-5 w-5" />
                                                ) : (
                                                    <Monitor className="h-5 w-5" />
                                                )}
                                            </div>
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <h4 className="text-sm font-semibold text-neutral-900 dark:text-neutral-100">{session.device}</h4>
                                                    {session.current && (
                                                        <Badge className="bg-[#e9f2ff] text-[#0066cc] dark:bg-blue-950/40 dark:text-blue-400 border-none text-[10px] uppercase font-bold py-0.5 px-2">
                                                            Current Session
                                                        </Badge>
                                                    )}
                                                </div>
                                                <p className="text-xs text-neutral-500 mt-0.5">{session.os}</p>
                                                <p className="text-[10px] text-neutral-400 mt-1">
                                                    {session.location} • {session.status}
                                                </p>
                                            </div>
                                        </div>

                                        {!session.current && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => handleRevokeSession(session.id, session.device)}
                                                className="text-xs font-semibold h-8 border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 px-3.5 hover:bg-neutral-50"
                                            >
                                                Revoke
                                            </Button>
                                        )}
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Right Column: 2FA and Audit Logs */}
                    <div className="lg:col-span-4 space-y-6">
                        {/* Two-Factor Authentication Card */}
                        <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-xl overflow-hidden shadow-sm p-6 text-center">
                            <div className="flex flex-col items-center space-y-4">
                                <div className="w-12 h-12 rounded-full bg-[#e9f2ff] dark:bg-blue-950 flex items-center justify-center text-[#0066cc] dark:text-blue-400">
                                    <Shield className="h-6 w-6" />
                                </div>
                                <div className="space-y-1">
                                    <h3 className="text-sm font-bold text-neutral-900 dark:text-neutral-100">Two-Factor Authentication</h3>
                                    <p className="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                        Add an extra layer of security to your account. Require a code from your mobile device when logging in.
                                    </p>
                                </div>

                                <div className="w-full bg-[#f8f9fa] dark:bg-neutral-850 border border-neutral-200 dark:border-neutral-800 rounded-lg p-4 flex items-center justify-between text-left">
                                    <div>
                                        <h4 className="text-xs font-bold text-neutral-800 dark:text-neutral-200">Authenticator App</h4>
                                        <p className={`text-[10px] mt-0.5 font-semibold ${isTwoFactorEnabled ? 'text-green-600' : 'text-red-500'}`}>
                                            {isTwoFactorEnabled ? 'Configured' : 'Not Configured'}
                                        </p>
                                    </div>
                                    {/* Custom Toggle Switch */}
                                    <button
                                        type="button"
                                        onClick={handleToggleTwoFactor}
                                        className={`relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${
                                            isTwoFactorEnabled ? 'bg-green-600' : 'bg-neutral-300 dark:bg-neutral-700'
                                        }`}
                                    >
                                        <span
                                            className={`pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                                                isTwoFactorEnabled ? 'translate-x-5' : 'translate-x-0'
                                            }`}
                                        />
                                    </button>
                                </div>
                            </div>
                        </Card>

                        {/* Security Audit Log Card */}
                        <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-xl overflow-hidden shadow-sm p-6 text-left space-y-4">
                            <div className="flex items-start gap-3">
                                <div className="w-10 h-10 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-600 dark:text-neutral-400 shrink-0">
                                    <Info className="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 className="text-sm font-bold text-neutral-900 dark:text-neutral-100">Security Audit Log</h3>
                                    <p className="text-xs text-neutral-500 dark:text-neutral-450 leading-relaxed mt-1">
                                        View a detailed log of all security-related actions taken on your account.
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => toast.info('Navigating to Audit Trail log view...')}
                                className="text-xs font-semibold text-[#0066cc] hover:underline flex items-center gap-1.5 pt-1"
                            >
                                View Audit Log
                                <ExternalLink className="h-3 w-3" />
                            </button>
                        </Card>
                    </div>
                </div>
            </div>

            {/* Simulated 2FA Modal Configuration */}
            {showTwoFactorModal && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-xl max-w-sm w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-200">
                        <div className="space-y-1">
                            <h3 className="text-lg font-bold text-neutral-900 dark:text-neutral-100">Configure Authenticator</h3>
                            <p className="text-xs text-neutral-500 dark:text-neutral-400">
                                Scan the QR code or enter this key in your Google Authenticator or Microsoft Authenticator app.
                            </p>
                        </div>

                        <div className="flex flex-col items-center gap-3 py-2 bg-neutral-50 dark:bg-neutral-900 rounded-lg border border-neutral-150 dark:border-neutral-800">
                            {/* Dummy QR Code */}
                            <div className="w-32 h-32 bg-white border border-neutral-200 flex items-center justify-center p-2">
                                <div className="w-full h-full bg-[radial-gradient(ellipse_at_center,var(--tw-gradient-stops))] from-neutral-800 via-neutral-900 to-black rounded opacity-80" />
                            </div>
                            <span className="text-[10px] font-mono text-neutral-500 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">
                                KEY: JBSWY3DPEHPK3PXP
                            </span>
                        </div>

                        <form onSubmit={handleVerifyTwoFactor} className="space-y-3">
                            <div className="space-y-1">
                                <Label htmlFor="code" className="text-xs font-semibold text-neutral-700 dark:text-neutral-300">ENTER 6-DIGIT CODE</Label>
                                <Input
                                    id="code"
                                    type="text"
                                    maxLength={6}
                                    value={twoFactorCode}
                                    onChange={(e) => setTwoFactorCode(e.target.value)}
                                    placeholder="000000"
                                    className="text-center font-mono text-lg tracking-widest"
                                    required
                                />
                                {twoFactorError && <p className="text-xs text-red-500 mt-1">{twoFactorError}</p>}
                            </div>

                            <div className="flex justify-end gap-2 pt-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => {
                                        setShowTwoFactorModal(false);
                                        setTwoFactorCode('');
                                        setTwoFactorError('');
                                    }}
                                    className="text-xs"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    className="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-4"
                                >
                                    Verify & Enable
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'Security settings',
            href: editSecurity(),
        },
    ],
};
