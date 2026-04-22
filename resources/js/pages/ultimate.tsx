
import { Head, Link, usePage } from '@inertiajs/react';
import communityCta from '@/assets/landing/community-cta.jpg';
import featureAnalytics from '@/assets/landing/feature-analytics.jpg';
import featureCandidate from '@/assets/landing/feature-candidate.jpg';
import featureElection from '@/assets/landing/feature-election.jpg';
import featureVoting from '@/assets/landing/feature-voting.jpg';
import heroBg from '@/assets/landing/hero-bg.jpg';
import heroDevice from '@/assets/landing/hero-device.jpg';
import resultsBg from '@/assets/landing/results-bg.jpg';
import securityBg from '@/assets/landing/security-bg.jpg';
import step1 from '@/assets/landing/step-1.jpg';
import step2 from '@/assets/landing/step-2.jpg';
import step3 from '@/assets/landing/step-3.jpg';
import step4 from '@/assets/landing/step-4.jpg';
import { dashboard, login, register } from '@/routes';

export default function Ultimate({
  canRegister = true,
}: {
  canRegister?: boolean;
}) {
  const { auth } = usePage().props as { auth: { user?: unknown } };

  return (
    <>
      <Head title="Voting Management System" />

      <div className="min-h-screen bg-slate-950 text-slate-100">
        <div className="mx-auto max-w-7xl px-6 py-8 md:px-10">
          <header className="mb-8 flex items-center justify-between">
            <div className="text-lg font-semibold tracking-wide">VoteOS</div>
            <nav className="flex items-center gap-3 text-sm">
              {auth.user ? (
                <Link
                  href={dashboard()}
                  className="rounded-full border border-slate-600 px-4 py-2 hover:border-slate-300"
                >
                  Dashboard
                </Link>
              ) : (
                <>
                  <Link
                    href={login()}
                    className="rounded-full border border-transparent px-4 py-2 hover:border-slate-600"
                  >
                    Log in
                  </Link>
                  {canRegister && (
                    <Link
                      href={register()}
                      className="rounded-full bg-cyan-400 px-4 py-2 font-medium text-slate-950 hover:bg-cyan-300"
                    >
                      Register
                    </Link>
                  )}
                </>
              )}
            </nav>
          </header>

          <main className="space-y-16 pb-16">
            <section className="relative overflow-hidden rounded-3xl border border-slate-800">
                            <img src={heroBg} alt="Citizens participating in election" className="h-115 w-full object-cover opacity-50" />
                            <div className="absolute inset-0 bg-linear-to-r from-slate-950 via-slate-950/85 to-transparent" />
              <div className="absolute inset-0 grid gap-6 p-8 md:grid-cols-2 md:p-12">
                <div className="flex flex-col justify-center">
                  <p className="mb-3 text-xs uppercase tracking-[0.25em] text-cyan-300">National, Campus, Organization</p>
                  <h1 className="text-4xl font-semibold leading-tight md:text-5xl">Secure digital voting for every community.</h1>
                  <p className="mt-4 max-w-xl text-slate-300">
                    Create elections, verify voters, and publish results with complete transparency.
                  </p>
                  <div className="mt-7 flex flex-wrap gap-3">
                    <Link href={auth.user ? dashboard() : login()} className="rounded-full bg-white px-5 py-2.5 font-medium text-slate-900">
                      {auth.user ? 'Go to Dashboard' : 'Start Voting'}
                    </Link>
                    {canRegister && !auth.user && (
                      <Link href={register()} className="rounded-full border border-slate-300 px-5 py-2.5">
                        Create Account
                      </Link>
                    )}
                  </div>
                </div>
                <div className="hidden items-end justify-end md:flex">
                                    <img src={heroDevice} alt="Voting dashboard preview" className="h-82.5 w-110 rounded-2xl border border-slate-700 object-cover shadow-2xl" />
                </div>
              </div>
            </section>

            <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              {[
                { title: 'Voting Session', image: featureVoting },
                { title: 'Election Builder', image: featureElection },
                { title: 'Candidate Management', image: featureCandidate },
                { title: 'Live Analytics', image: featureAnalytics },
              ].map((item) => (
                <article key={item.title} className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60">
                  <img src={item.image} alt={item.title} className="h-36 w-full object-cover" />
                  <div className="p-4">
                    <h2 className="font-medium">{item.title}</h2>
                  </div>
                </article>
              ))}
            </section>

            <section className="grid gap-6 md:grid-cols-2">
              <article className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/50">
                <img src={resultsBg} alt="Election results" className="h-48 w-full object-cover" />
                <div className="p-5">
                  <h3 className="text-xl font-medium">Instant result publishing</h3>
                  <p className="mt-2 text-slate-300">Publish verified results in real-time with downloadable summaries and charts.</p>
                </div>
              </article>
              <article className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/50">
                <img src={securityBg} alt="Security controls" className="h-48 w-full object-cover" />
                <div className="p-5">
                  <h3 className="text-xl font-medium">Built-in security controls</h3>
                  <p className="mt-2 text-slate-300">Role-based access, vote session controls, and auditable activity history.</p>
                </div>
              </article>
            </section>

            <section>
              <h3 className="mb-4 text-2xl font-semibold">How It Works</h3>
              <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {[
                  { title: 'Create Election', image: step1 },
                  { title: 'Add Candidates', image: step2 },
                  { title: 'Open Voting', image: step3 },
                  { title: 'Publish Results', image: step4 },
                ].map((step) => (
                  <article key={step.title} className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/50">
                    <img src={step.image} alt={step.title} className="h-40 w-full object-cover" />
                    <div className="p-4 text-sm text-slate-200">{step.title}</div>
                  </article>
                ))}
              </div>
            </section>

            <section className="relative overflow-hidden rounded-3xl border border-slate-800">
              <img src={communityCta} alt="Community voting" className="h-56 w-full object-cover opacity-45" />
              <div className="absolute inset-0 bg-slate-950/60" />
              <div className="absolute inset-0 flex flex-col items-center justify-center px-6 text-center">
                <h3 className="text-2xl font-semibold md:text-3xl">Bring trusted voting to your organization.</h3>
                <p className="mt-2 max-w-2xl text-slate-300">Run your next election with confidence and transparency.</p>
                <div className="mt-5 flex gap-3">
                  <Link href={auth.user ? dashboard() : login()} className="rounded-full bg-cyan-400 px-5 py-2.5 font-medium text-slate-950">
                    {auth.user ? 'Open Dashboard' : 'Get Started'}
                  </Link>
                  {canRegister && !auth.user && (
                    <Link href={register()} className="rounded-full border border-slate-200 px-5 py-2.5">
                      Register
                    </Link>
                  )}
                </div>
              </div>
            </section>
          </main>
        </div>
      </div>
    </>
  );
}