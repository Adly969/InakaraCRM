import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';

interface CoaAccount {
    id: number;
    account_code: string;
    name: string;
    account_type: string;
    normal_balance: string;
    opening_balance: string | number;
    debits: string | number;
    credits: string | number;
    closing_balance: string | number;
}

export default function FinanceDashboard() {
    const [accounts, setAccounts] = useState<CoaAccount[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [year, setYear] = useState<number>(new Date().getFullYear());
    const [month, setMonth] = useState<number>(new Date().getMonth() + 1);

    useEffect(() => {
        fetch(`/finance/ledger/trial-balance?year=${year}&month=${month}`)
            .then(res => res.json())
            .then(data => {
                setAccounts(data.data || []);
                setLoading(false);
            })
            .catch(err => {
                console.error(err);
                setLoading(false);
            });
    }, [year, month]);

    return (
        <>
            <Head title="General Ledger & Trial Balance" />
            <div className="flex h-full flex-1 flex-col gap-4 p-6">
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                        General Ledger Core
                    </h1>
                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                        Double-entry verification, virtual trial balance grid, and real-time ledger accounting.
                    </p>
                </div>

                <div className="flex items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800">
                    <div>
                        <label className="block text-xs font-semibold text-neutral-500 mb-1">Fiscal Year</label>
                        <select
                            value={year}
                            onChange={(e) => setYear(Number(e.target.value))}
                            className="rounded-lg border border-neutral-300 px-3 py-1.5 text-sm dark:bg-neutral-800 dark:border-neutral-700"
                        >
                            {[2024, 2025, 2026, 2027].map(y => (
                                <option key={y} value={y}>{y}</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-xs font-semibold text-neutral-500 mb-1">Accounting Period</label>
                        <select
                            value={month}
                            onChange={(e) => setMonth(Number(e.target.value))}
                            className="rounded-lg border border-neutral-300 px-3 py-1.5 text-sm dark:bg-neutral-800 dark:border-neutral-700"
                        >
                            {Array.from({ length: 12 }, (_, i) => i + 1).map(m => (
                                <option key={m} value={m}>Period {m}</option>
                            ))}
                        </select>
                    </div>
                </div>

                <div className="flex-1 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h2 className="text-lg font-bold text-neutral-800 dark:text-neutral-200 mb-4">Trial Balance Grid</h2>
                    {loading ? (
                        <div className="text-center py-10 text-neutral-500">Loading trial balance projections...</div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
                                <thead>
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-bold text-neutral-500 uppercase tracking-wider">Account Code</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold text-neutral-500 uppercase tracking-wider">Account Name</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold text-neutral-500 uppercase tracking-wider">Type</th>
                                        <th className="px-6 py-3 text-right text-xs font-bold text-neutral-500 uppercase tracking-wider">Opening Balance</th>
                                        <th className="px-6 py-3 text-right text-xs font-bold text-neutral-500 uppercase tracking-wider">Debits</th>
                                        <th className="px-6 py-3 text-right text-xs font-bold text-neutral-500 uppercase tracking-wider">Credits</th>
                                        <th className="px-6 py-3 text-right text-xs font-bold text-neutral-500 uppercase tracking-wider">Closing Balance</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {accounts.map(acc => (
                                        <tr key={acc.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-850">
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-neutral-900 dark:text-neutral-100">{acc.account_code}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">{acc.name}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 capitalize">{acc.account_type}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-neutral-900 dark:text-neutral-100">
                                                {Number(acc.opening_balance).toLocaleString('id-ID', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-green-600">
                                                {Number(acc.debits).toLocaleString('id-ID', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-red-600">
                                                {Number(acc.credits).toLocaleString('id-ID', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-neutral-900 dark:text-neutral-100">
                                                {Number(acc.closing_balance).toLocaleString('id-ID', { minimumFractionDigits: 2 })}
                                            </td>
                                        </tr>
                                    ))}
                                    {accounts.length === 0 && (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-10 text-center text-sm text-neutral-500">
                                                No ledger balances found for this period.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
