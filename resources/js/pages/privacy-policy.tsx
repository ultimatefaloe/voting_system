import React from 'react';

const PrivacyPolicy: React.FC = () => {
    return (
        <div className="relative min-h-screen overflow-hidden bg-slate-950 text-slate-100">
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_15%_20%,rgba(56,189,248,0.2),transparent_45%),radial-gradient(circle_at_85%_75%,rgba(16,185,129,0.18),transparent_50%)]" />
            <main className="mx-auto w-full max-w-4xl px-6 py-16 lg:px-10 lg:py-20">
                <div className="relative rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-slate-950/40 backdrop-blur-md md:p-10">
                    <div className="inline-flex items-center gap-2 rounded-full border border-cyan-300/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-200">
                        Privacy & Data
                    </div>
                    <h1 className="mt-4 text-3xl font-semibold text-white md:text-4xl">Privacy Policy</h1>
                    <p className="mt-4 text-sm leading-relaxed text-slate-300">
                        This policy explains how VoteFlow handles personal and election-related data processed through
                        the platform.
                    </p>

                    <section className="mt-8 space-y-6 text-sm leading-relaxed text-slate-300">
                    <div>
                        <h2 className="text-lg font-semibold text-white">1. Data We Process</h2>
                        <p className="mt-2">
                            Account details, organization settings, election configuration metadata, and participation
                            records necessary to provide secure digital election functionality.
                        </p>
                    </div>

                    <div>
                        <h2 className="text-lg font-semibold text-white">2. Security Practices</h2>
                        <p className="mt-2">
                            Access is controlled through authenticated workflows and role-based authorization. System
                            monitoring and integrity controls are applied to protect election operations.
                        </p>
                    </div>

                    <div>
                        <h2 className="text-lg font-semibold text-white">3. Contact</h2>
                        <p className="mt-2">
                            For privacy requests or questions, contact
                            <a href="mailto:support@voteflow.app" className="ml-1 text-cyan-300 hover:text-cyan-200">support@voteflow.app</a>.
                        </p>
                    </div>
                    </section>

                    <div className="mt-10">
                    <a href="/welcome" className="brand-cta rounded-full px-5 py-2 text-xs font-semibold">
                        Back to Home
                    </a>
                    </div>
                </div>
            </main>
        </div>
    );
};

export default PrivacyPolicy;
