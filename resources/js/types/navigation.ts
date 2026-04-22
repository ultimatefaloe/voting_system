import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import type { User } from './auth';

// Organization shapes
export interface Organization {
  id: number;
  name: string;
  slug: string;
  owner_id: number;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface OrganizationMember {
  id: number;
  organization_id: number;
  user_id: number;
  role: 'owner' | 'admin' | 'member' | 'viewer';
  status: 'active' | 'pending';
  created_at?: string | null;
  updated_at?: string | null;
  // optional relation
  user?: User;
}

export interface OrganizationInvite {
  id: number;
  organization_id: number;
  invited_by: number;
  email: string;
  token: string;
  role: 'admin' | 'member' | 'viewer';
  status: 'pending' | 'accepted';
  expires_at?: string | null;
  accepted_at?: string | null;
  created_at?: string | null;
  updated_at?: string | null;
}

// Elections and related models
export interface Election {
  id: number;
  organization_id: number;
  title: string;
  description?: string | null;
  type: 'public' | 'private';
  status: 'draft' | 'active' | 'stopped' | 'closed' | 'published';
  start_date?: string | null;
  end_date?: string | null;
  access_token?: string | null;
  created_by: number;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface ElectionSummary {
  id: number;
  title: string;
  status: Election['status'];
  type: Election['type'];
  start_date?: string | null;
  end_date?: string | null;
}

export interface Position {
  id: number;
  election_id: number;
  title: string;
  description?: string | null;
  max_votes: number;
  order: number;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface Candidate {
  id: number;
  position_id: number;
  name: string;
  bio?: string | null;
  avatar?: string | null;
  order: number;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface PositionSummary {
  id: number;
  title: string;
  order: number;
  max_votes: number;
}

export interface CandidateSummary {
  id: number;
  name: string;
  avatar?: string | null;
}

export interface ElectionAccess {
  id: number;
  election_id: number;
  user_id?: number | null;
  email?: string | null;
  token: string;
  status: 'active' | 'used';
  expires_at?: string | null;
  used_at?: string | null;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface VoteSession {
  id: number;
  election_id: number;
  voter_token: string;
  ip_address?: string | null;
  user_agent?: string | null;
  submitted_at: string;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface Vote {
  id: number;
  vote_session_id: number;
  position_id: number;
  candidate_id: number;
  created_at?: string | null;
  updated_at?: string | null;
}

// Convenience types used for navigation lists
export type OrganizationSummary = Pick<Organization, 'id' | 'name' | 'slug' | 'owner_id'>;

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
};
