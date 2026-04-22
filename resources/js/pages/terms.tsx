import React from 'react';

const Terms: React.FC = () => {
    return (
        <div className="relative min-h-screen overflow-hidden bg-slate-950 text-slate-100">
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_15%,rgba(56,189,248,0.2),transparent_45%),radial-gradient(circle_at_80%_80%,rgba(16,185,129,0.18),transparent_50%)]" />
            <main className="mx-auto w-full max-w-4xl px-6 py-16 lg:px-10 lg:py-20">
                <div className="relative rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-slate-950/40 backdrop-blur-md md:p-10">
                    <div className="inline-flex items-center gap-2 rounded-full border border-cyan-300/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-200">
                        Legal Terms
                    </div>
                    <h1 className="mt-4 text-3xl font-semibold text-white md:text-4xl">Terms of Service</h1>
                    <p className="mt-4 text-sm leading-relaxed text-slate-300">
                        These terms govern access to and usage of the VoteFlow digital election platform.
                    </p>

                    <section className="mt-8 space-y-6 text-sm leading-relaxed text-slate-300">
                        <div>
                            <h2 className="text-lg font-semibold text-white">1. Platform Use</h2>
                            <p className="mt-2">
                                Organizations are responsible for lawful election setup, participant eligibility rules,
                                and governance decisions configured within their workspace.
                            </p>
                        </div>

                        <div>
                            <h2 className="text-lg font-semibold text-white">2. Data Integrity</h2>
                            <p className="mt-2">
                                The platform provides secure workflows and reporting tools; customers must protect
                                organization credentials and maintain proper internal access control practices.
                            </p>
                        </div>

                        <div>
                            <h2 className="text-lg font-semibold text-white">3. Results Publication</h2>
                            <p className="mt-2">
                                Published election outcomes are controlled by authorized organization users. Audit,
                                disclosure, and compliance obligations remain with the organization using the platform.
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

export default Terms;
