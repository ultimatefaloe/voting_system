import { Head, Link, usePage } from '@inertiajs/react';
import { Building2, ShieldCheck, Users } from 'lucide-react';
import { PageHero } from '@/components/page-hero';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type OrganizationItem = {
    id: number;
    name: string;
    slug: string;
    role: 'owner' | 'admin' | 'member' | 'viewer';
    members_count: number;
    pending_invites_count: number;
    elections_count: number;
    created_at: string | null;
};

type OrganizationsPageProps = {
    organizationsData?: {
        items: OrganizationItem[];
        summary: {
            organizations_count: number;
            active_memberships: number;
            total_elections: number;
        };
    };
};

export default function OrganizationsIndex() {
    const { organizationsData } = usePage<OrganizationsPageProps>().props;
    const organizations = organizationsData?.items ?? [];
    const summary = organizationsData?.summary ?? {
        organizations_count: 0,
        active_memberships: 0,
        total_elections: 0,
    };

    return (
        <>
            <Head title="Organizations" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-linear-to-b from-emerald-500/6 via-transparent to-transparent p-4">
                <PageHero
                    eyebrow="Access and governance"
                    title="Organizations"
                    description="Manage organizational boundaries, role policies, and election ownership with a shared governance view."
                    startGlowClass="bg-emerald-400/15"
                    endGlowClass="bg-cyan-400/15"
                    actions={(
                        <>
                            <Badge variant="secondary" className="bg-emerald-500/15 text-emerald-700">
                                Backend ready
                            </Badge>
                            <Button asChild size="sm">
                                <Link href="/dashboard">Back to dashboard</Link>
                            </Button>
                        </>
                    )}
                    chips={(
                        <>
                            <Badge variant="outline" className="gap-1">
                                <Building2 className="h-3.5 w-3.5" />
                                Multi-organization workspace
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <Users className="h-3.5 w-3.5" />
                                Live membership scopes
                            </Badge>
                            <Badge variant="outline" className="gap-1">
                                <ShieldCheck className="h-3.5 w-3.5" />
                                Role-based access controls
                            </Badge>
                        </>
                    )}
                />

                <section className="grid gap-4 md:grid-cols-3 reveal-up">
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Organizations</CardDescription>
                            <CardTitle>{summary.organizations_count}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Active memberships</CardDescription>
                            <CardTitle>{summary.active_memberships}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="rounded-2xl border-border/70 bg-card/95 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Total elections</CardDescription>
                            <CardTitle>{summary.total_elections}</CardTitle>
                        </CardHeader>
                    </Card>
                </section>

                <section className="grid gap-4 lg:grid-cols-3 reveal-up reveal-delay-1">
                    <Card className="rounded-2xl">
                        <CardHeader>
                            <CardTitle>Your organizations</CardTitle>
                            <CardDescription>
                                Live membership scope from backend organization records.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            {organizations.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-3 text-muted-foreground">
                                    No active organization memberships yet.
                                </div>
                            ) : (
                                organizations.map((organization) => (
                                    <div key={organization.id} className="space-y-1 rounded-xl border bg-card/70 p-3">
                                        <div className="flex items-center justify-between gap-2">
                                            <p className="text-sm font-medium">{organization.name}</p>
                                            <Badge variant="outline" className="capitalize">
                                                {organization.role}
                                            </Badge>
                                        </div>
                                        <p className="text-xs text-muted-foreground">/{organization.slug}</p>
                                        <p className="text-xs text-muted-foreground">
                                            {organization.members_count} members • {organization.pending_invites_count} pending invites • {organization.elections_count} elections
                                        </p>
                                        <div className="pt-2">
                                            <Button asChild variant="outline" size="sm">
                                                <Link href={`/organizations/${organization.id}`}>View details</Link>
                                            </Button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card className="rounded-2xl lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Backend scope map</CardTitle>
                            <CardDescription>
                                Next implementation targets already available in authenticated API routes.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-2 text-sm text-muted-foreground md:grid-cols-2">
                            <p className="rounded-lg border bg-card/70 p-3">GET /api/organizations</p>
                            <p className="rounded-lg border bg-card/70 p-3">POST /api/organizations</p>
                            <p className="rounded-lg border bg-card/70 p-3">GET/PATCH/DELETE /api/organizations/{'{organization}'}</p>
                            <p className="rounded-lg border bg-card/70 p-3">GET/POST /api/organizations/{'{organization}'}/members</p>
                            <p className="rounded-lg border bg-card/70 p-3">PATCH/DELETE /api/organizations/{'{organization}'}/members/{'{member}'}</p>
                            <p className="rounded-lg border bg-card/70 p-3">Invites: list/create/resend/cancel routes available.</p>
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

OrganizationsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Organizations',
            href: '/organizations',
        },
    ],
};
