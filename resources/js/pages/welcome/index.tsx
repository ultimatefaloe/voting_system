
import React, { useState } from 'react';
import featureAnalyticsLocalImg from '@/assets/landing/feature-analytics.jpg';
import resultsLocalImg from '@/assets/landing/results-bg.jpg';

type WelcomeProps = {
    canRegister?: boolean;
};

const heroPhotoUrl =
    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=2200&q=80';

const featureElectionImg =
    'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1600&q=80';
const featureCandidateImg =
    'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1600&q=80';
const featureVotingImg =
    'https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=1600&q=80';
const featureAnalyticsImg = featureAnalyticsLocalImg;

const securityBgImg =
    'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=2200&q=80';
const resultsBgImg = heroPhotoUrl;
const communityCtaImg =
    'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=2200&q=80';

const step1Img =
    'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1200&q=80';
const step2Img =
    'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80';
const step3Img =
    'https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=1200&q=80';
const step4Img = resultsLocalImg;

const featureCards = [
    {
        title: 'Election Creation',
        description: 'Configure elections, timelines, and access in minutes.',
        image: featureElectionImg,
    },
    {
        title: 'Candidate Management',
        description: 'Organize positions and candidates with clean workflows.',
        image: featureCandidateImg,
    },
    {
        title: 'Secure Voting',
        description: 'Token-based ballot submissions with traceable integrity.',
        image: featureVotingImg,
    },
    {
        title: 'Analytics & Results',
        description: 'Publish transparent outcomes with rich performance metrics.',
        image: featureAnalyticsImg,
    },
];

const workflow = [
    {
        step: '01',
        title: 'Configure Election',
        image: step1Img,
        imagePositionClass: 'object-left md:object-center',
    },
    {
        step: '02',
        title: 'Assign Positions',
        image: step2Img,
        imagePositionClass: 'object-center',
    },
    {
        step: '03',
        title: 'Launch Voting',
        image: step3Img,
        imagePositionClass: 'object-center',
    },
    {
        step: '04',
        title: 'Review Results',
        image: step4Img,
        imagePositionClass: 'object-top',
    },
];

const Welcome: React.FC<WelcomeProps> = ({ canRegister = false }) => {
    const currentYear = new Date().getFullYear();
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <header className="sticky top-0 z-30 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
                <nav className="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4 lg:px-10">
                    <div className="flex items-center gap-3">
                        <div className="brand-mark h-8 w-8 rounded-md" />
                        <span className="text-sm font-semibold tracking-[0.18em] text-slate-200">VOTEFLOW</span>
                    </div>
                    <div className="hidden items-center gap-7 text-sm text-slate-300 md:flex">
                        <a href="#features" className="transition hover:text-white">Features</a>
                        <a href="#security" className="transition hover:text-white">Security</a>
                        <a href="#workflow" className="transition hover:text-white">How It Works</a>
                        <a href="#results" className="transition hover:text-white">Results</a>
                    </div>
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            className="rounded-full border border-white/20 px-3 py-2 text-xs font-semibold text-slate-100 md:hidden"
                            onClick={() => setIsMobileMenuOpen((prev) => !prev)}
                            aria-label="Toggle navigation menu"
                        >
                            Menu
                        </button>
                        <a
                            href="/login"
                            className="rounded-full border border-white/20 px-4 py-2 text-xs font-medium text-slate-100 transition hover:border-white/40"
                        >
                            Log In
                        </a>
                        {canRegister && (
                            <a
                                href="/register"
                                className="brand-cta rounded-full px-4 py-2 text-xs"
                            >
                                Get Started
                            </a>
                        )}
                    </div>
                </nav>
                {isMobileMenuOpen && (
                    <div className="border-t border-white/10 bg-slate-950/95 px-6 py-4 md:hidden">
                        <div className="flex flex-col gap-3 text-sm text-slate-200">
                            <a href="#features" onClick={() => setIsMobileMenuOpen(false)} className="transition hover:text-white">Features</a>
                            <a href="#security" onClick={() => setIsMobileMenuOpen(false)} className="transition hover:text-white">Security</a>
                            <a href="#workflow" onClick={() => setIsMobileMenuOpen(false)} className="transition hover:text-white">How It Works</a>
                            <a href="#results" onClick={() => setIsMobileMenuOpen(false)} className="transition hover:text-white">Results</a>
                        </div>
                    </div>
                )}
            </header>

            <main>
                <section className="relative overflow-hidden border-b border-white/10">
                    <img
                        src={heroPhotoUrl}
                        alt="Professional team monitoring digital election analytics and transparent results"
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                    <div className="absolute inset-0 bg-linear-to-r from-slate-950/88 via-slate-950/70 to-slate-950/65" />
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_22%,rgba(34,211,238,0.18),transparent_36%),radial-gradient(circle_at_82%_78%,rgba(16,185,129,0.18),transparent_36%)]" />

                    <div className="relative mx-auto grid w-full max-w-7xl gap-10 px-6 py-18 lg:grid-cols-[1.1fr_0.9fr] lg:px-10 lg:py-24">
                        <div>
                            <p className="mb-4 inline-block rounded-full border border-cyan-300/40 bg-cyan-300/10 px-3 py-1 text-xs font-medium tracking-wide text-cyan-200">
                                Global Digital Election Platform
                            </p>
                            <h1 className="max-w-xl text-4xl font-semibold leading-tight tracking-tight text-white md:text-6xl">
                                Deliver secure digital elections with transparent, verifiable outcomes.
                            </h1>
                            <p className="mt-5 max-w-xl text-base leading-relaxed text-slate-200 md:text-lg">
                                Build professional election workflows for organizations, campuses, associations, and private institutions with secure access, digital voting, and transparent analytics.
                            </p>
                            <div className="mt-5 flex flex-wrap gap-2 text-xs font-medium text-slate-100">
                                <span className="rounded-full border border-cyan-300/40 bg-cyan-400/10 px-3 py-1">Configure Elections</span>
                                <span className="rounded-full border border-cyan-300/40 bg-cyan-400/10 px-3 py-1">Secure Token Voting</span>
                                <span className="rounded-full border border-cyan-300/40 bg-cyan-400/10 px-3 py-1">Publish Transparent Results</span>
                            </div>
                            <div className="mt-8 flex flex-wrap gap-3">
                                <a
                                    href={canRegister ? '/register' : '/login'}
                                    className="brand-cta rounded-full px-6 py-3 text-sm"
                                >
                                    Launch Your Election
                                </a>
                                <a
                                    href="#features"
                                    className="rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:border-white/45"
                                >
                                    Explore Platform
                                </a>
                            </div>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                            <div className="rounded-2xl border border-white/20 bg-white/0 p-4 backdrop-blur-xl">
                                <p className="text-xs uppercase tracking-[0.12em] text-slate-300">Platform Workflow</p>
                                <p className="mt-1 text-lg font-semibold text-white">Create | Verify Access | Vote | Publish</p>
                                <p className="mt-2 text-sm text-slate-200">One complete digital election cycle aligned with your backend architecture.</p>
                            </div>
                            <div className="rounded-2xl border border-cyan-300/25 bg-slate-900/15 p-4 backdrop-blur-xl">
                                <div className="flex items-center justify-between border-b border-white/10 pb-3">
                                    <p className="text-xs font-medium uppercase tracking-[0.12em] text-cyan-300">Election Control Panel</p>
                                    <span className="rounded-full border border-emerald-300/40 bg-emerald-400/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-200">
                                        Live
                                    </span>
                                </div>
                                <div className="mt-4 space-y-3 text-xs">
                                    <div className="rounded-xl border border-white/10 bg-slate-950/70 p-3">
                                        <div className="flex items-center justify-between text-slate-300">
                                            <span>Access Validation</span>
                                            <span className="font-semibold text-emerald-200">98.7%</span>
                                        </div>
                                        <div className="mt-2 h-1.5 rounded-full bg-slate-800">
                                            <div className="h-full w-[98.7%] rounded-full bg-emerald-400" />
                                        </div>
                                    </div>
                                    <div className="rounded-xl border border-white/10 bg-slate-950/70 p-3">
                                        <div className="flex items-center justify-between text-slate-300">
                                            <span>Ballot Completion</span>
                                            <span className="font-semibold text-cyan-200">84.2%</span>
                                        </div>
                                        <div className="mt-2 h-1.5 rounded-full bg-slate-800">
                                            <div className="h-full w-[84.2%] rounded-full bg-cyan-400" />
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-3 gap-2 text-center">
                                        <div className="rounded-lg border border-white/10 bg-slate-950/70 px-2 py-2">
                                            <p className="text-[10px] uppercase tracking-wide text-slate-400">Elections</p>
                                            <p className="mt-1 text-sm font-semibold text-white">24</p>
                                        </div>
                                        <div className="rounded-lg border border-white/10 bg-slate-950/70 px-2 py-2">
                                            <p className="text-[10px] uppercase tracking-wide text-slate-400">Voters</p>
                                            <p className="mt-1 text-sm font-semibold text-white">12.8k</p>
                                        </div>
                                        <div className="rounded-lg border border-white/10 bg-slate-950/70 px-2 py-2">
                                            <p className="text-[10px] uppercase tracking-wide text-slate-400">Published</p>
                                            <p className="mt-1 text-sm font-semibold text-white">18</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" className="mx-auto w-full max-w-7xl px-6 py-16 lg:px-10 lg:py-20">
                    <div className="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p className="text-xs font-medium uppercase tracking-[0.18em] text-cyan-300">Platform Capabilities</p>
                            <h2 className="mt-2 text-3xl font-semibold text-white md:text-4xl">Built for modern election operations</h2>
                        </div>
                        <p className="max-w-xl text-sm leading-relaxed text-slate-300">
                            Every module aligns with your current backend progress: organization management, election setup, secure vote capture, and transparent result publication.
                        </p>
                    </div>

                    <div className="grid gap-5 md:grid-cols-2">
                        {featureCards.map((card) => (
                            <article
                                key={card.title}
                                className="group overflow-hidden rounded-2xl border border-white/10 bg-slate-900/70"
                            >
                                <img
                                    src={card.image}
                                    alt={card.title}
                                    className="h-44 w-full object-cover transition duration-500 group-hover:scale-105"
                                    loading="lazy"
                                />
                                <div className="p-5">
                                    <h3 className="text-xl font-semibold text-white">{card.title}</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-slate-300">{card.description}</p>
                                </div>
                            </article>
                        ))}
                    </div>
                </section>

                <section id="security" className="relative overflow-hidden border-y border-white/10 py-16 lg:py-20">
                    <img
                        src={securityBgImg}
                        alt="Cybersecurity operations environment"
                        className="absolute inset-0 h-full w-full object-cover"
                        loading="lazy"
                    />
                    <div className="absolute inset-0 bg-slate-950/80" />
                    <div className="relative mx-auto grid w-full max-w-7xl gap-8 px-6 lg:grid-cols-2 lg:px-10">
                        <div className="rounded-2xl border border-cyan-300/30 bg-cyan-400/10 p-6 backdrop-blur-xl">
                            <p className="text-xs font-medium uppercase tracking-[0.15em] text-cyan-300">Security First</p>
                            <h3 className="mt-3 text-3xl font-semibold text-white">Vote integrity from setup to final publication</h3>
                            <p className="mt-4 text-sm leading-relaxed text-slate-200">
                                Protect elections using authenticated workflows, token-based access, policy-driven authorization, and complete visibility over turnout and outcomes.
                            </p>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="rounded-2xl border border-white/15 bg-white/5 p-5">
                                <p className="text-2xl font-semibold text-white">Role Policies</p>
                                <p className="mt-2 text-sm text-slate-300">Owner, admin, member, and viewer permissions mapped to organization and election resources.</p>
                            </div>
                            <div className="rounded-2xl border border-white/15 bg-white/5 p-5">
                                <p className="text-2xl font-semibold text-white">Token Voting</p>
                                <p className="mt-2 text-sm text-slate-300">Header-based voter token flows for secure, controlled ballot submissions.</p>
                            </div>
                            <div className="rounded-2xl border border-white/15 bg-white/5 p-5">
                                <p className="text-2xl font-semibold text-white">Live Monitoring</p>
                                <p className="mt-2 text-sm text-slate-300">Track participation, distribution, and activity through election analytics endpoints.</p>
                            </div>
                            <div className="rounded-2xl border border-white/15 bg-white/5 p-5">
                                <p className="text-2xl font-semibold text-white">Published Proof</p>
                                <p className="mt-2 text-sm text-slate-300">Generate result summaries and export-ready datasets for transparent reporting.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="workflow" className="mx-auto w-full max-w-7xl px-6 py-16 lg:px-10 lg:py-20">
                    <h2 className="text-center text-3xl font-semibold text-white md:text-4xl">How it works in 4 steps</h2>
                    <p className="mx-auto mt-4 max-w-2xl text-center text-sm leading-relaxed text-slate-300">
                        A predictable flow from election configuration to trusted result publication.
                    </p>
                    <div className="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        {workflow.map((item) => (
                            <article key={item.step} className="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/70">
                                <img
                                    src={item.image}
                                    alt={item.title}
                                    className={`h-36 w-full object-cover ${item.imagePositionClass}`}
                                    loading="lazy"
                                />
                                <div className="p-4">
                                    <p className="text-xs font-medium tracking-[0.16em] text-cyan-300">STEP {item.step}</p>
                                    <h3 className="mt-2 text-lg font-semibold text-white">{item.title}</h3>
                                </div>
                            </article>
                        ))}
                    </div>
                </section>

                <section id="results" className="border-y border-white/10 bg-slate-900/50">
                    <div className="mx-auto grid w-full max-w-7xl gap-8 px-6 py-16 lg:grid-cols-2 lg:px-10 lg:py-20">
                        <div className="overflow-hidden rounded-2xl border border-white/10">
                            <img
                                src={resultsBgImg}
                                alt="Team reviewing election analytics dashboards"
                                className="h-full w-full object-cover"
                                loading="lazy"
                            />
                        </div>
                        <div className="flex flex-col justify-center">
                            <p className="text-xs font-medium uppercase tracking-[0.18em] text-cyan-300">Transparency & Insights</p>
                            <h2 className="mt-3 text-3xl font-semibold text-white md:text-4xl">Turn ballots into decisions backed by data</h2>
                            <p className="mt-4 text-sm leading-relaxed text-slate-300">
                                Surface live trends, turnout metrics, candidate performance, and published summaries with a presentation style your stakeholders can trust.
                            </p>
                            <ul className="mt-6 space-y-3 text-sm text-slate-200">
                                <li>• Live results for active elections</li>
                                <li>• Position and candidate level statistics</li>
                                <li>• Organization-wide participation analytics</li>
                                <li>• Export-ready published outcomes</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section className="relative overflow-hidden py-16 lg:py-20">
                    <img
                        src={communityCtaImg}
                        alt="Diverse professional community collaborating"
                        className="absolute inset-0 h-full w-full object-cover"
                        loading="lazy"
                    />
                    <div className="absolute inset-0 bg-linear-to-r from-slate-950/90 via-slate-950/75 to-slate-950/80" />
                    <div className="relative mx-auto w-full max-w-5xl px-6 text-center lg:px-10">
                        <h2 className="text-3xl font-semibold text-white md:text-5xl">Ready to modernize your election process?</h2>
                        <p className="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-slate-200 md:text-base">
                            Build confidence with secure workflows, transparent reporting, and a professional voting experience designed for organizations that take governance seriously.
                        </p>
                        <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                            <a
                                href={canRegister ? '/register' : '/login'}
                                className="brand-cta rounded-full px-7 py-3 text-sm"
                            >
                                Request a Demo
                            </a>
                            <a
                                href="/login"
                                className="rounded-full border border-white/25 px-7 py-3 text-sm font-semibold text-white transition hover:border-white/45"
                            >
                                Access Platform
                            </a>
                        </div>
                    </div>
                </section>

                <section className="border-y border-white/10 bg-slate-900/60">
                    <div className="mx-auto grid w-full max-w-7xl gap-4 px-6 py-6 md:grid-cols-3 lg:px-10">
                        <div className="rounded-xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-200">
                            <p className="font-semibold text-white">Token-Based Access Control</p>
                            <p className="mt-1 text-xs text-slate-300">Controlled voter entry and authenticated ballot submission flow.</p>
                        </div>
                        <div className="rounded-xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-200">
                            <p className="font-semibold text-white">Audit-Ready Published Results</p>
                            <p className="mt-1 text-xs text-slate-300">Transparent outcomes with export-ready reporting and visibility.</p>
                        </div>
                        <div className="rounded-xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-200">
                            <p className="font-semibold text-white">Role-Driven Election Governance</p>
                            <p className="mt-1 text-xs text-slate-300">Owner, admin, and member permissions enforced across workflows.</p>
                        </div>
                    </div>
                </section>

                <footer className="border-t border-white/10 bg-slate-950">
                    <div className="mx-auto grid w-full max-w-7xl gap-10 px-6 py-12 md:grid-cols-4 lg:px-10">
                        <div>
                            <div className="flex items-center gap-3">
                                <div className="brand-mark h-7 w-7 rounded-md" />
                                <span className="text-sm font-semibold tracking-[0.16em] text-slate-100">VOTEFLOW</span>
                            </div>
                            <p className="mt-4 text-sm leading-relaxed text-slate-300">
                                Secure digital election workflows for organizations, institutions, and private bodies worldwide.
                            </p>
                        </div>

                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-300">Product</p>
                            <ul className="mt-4 space-y-2 text-sm text-slate-300">
                                <li><a href="#features" className="transition hover:text-white">Features</a></li>
                                <li><a href="#security" className="transition hover:text-white">Security</a></li>
                                <li><a href="#workflow" className="transition hover:text-white">Workflow</a></li>
                                <li><a href="#results" className="transition hover:text-white">Results</a></li>
                            </ul>
                        </div>

                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-300">Trust</p>
                            <ul className="mt-4 space-y-2 text-sm text-slate-300">
                                <li><a href="/privacy-policy" className="transition hover:text-white">Privacy Policy</a></li>
                                <li><a href="/terms" className="transition hover:text-white">Terms of Service</a></li>
                                <li><a href="/login" className="transition hover:text-white">Platform Access</a></li>
                                <li><a href="mailto:support@voteflow.app" className="transition hover:text-white">Security Contact</a></li>
                            </ul>
                        </div>

                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-300">Get Started</p>
                            <p className="mt-4 text-sm leading-relaxed text-slate-300">
                                Launch professional elections with transparent reporting and audit-ready results.
                            </p>
                            <a
                                href={canRegister ? '/register' : '/login'}
                                className="brand-cta mt-4 inline-flex rounded-full px-5 py-2 text-xs"
                            >
                                Start Now
                            </a>
                        </div>
                    </div>
                    <div className="border-t border-white/10">
                        <div className="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-3 px-6 py-4 text-xs text-slate-400 lg:px-10">
                            <p>Copyright {currentYear} VOTEFLOW. All rights reserved.</p>
                            <p>Digital, transparent, and secure election infrastructure.</p>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    );
};

export default Welcome;