// Types related to authentication and users

// Consolidated user interface used by the frontend. Mirrors users table and
// optional two-factor metadata.
export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  email_verified_at?: string | null;
  // Two-factor fields (nullable)
  two_factor_secret?: string | null;
  two_factor_recovery_codes?: string | null;
  two_factor_confirmed_at?: string | null;
  // convenience boolean (may be supplied by backend)
  two_factor_enabled?: boolean;
  created_at?: string | null;
  updated_at?: string | null;
  // allow extra backend fields without breaking callers
  [key: string]: unknown;
}

export interface SessionRow {
  id: string;
  user_id?: number | null;
  ip_address?: string | null;
  user_agent?: string | null;
  payload: string;
  last_activity: number;
}

export interface PasswordResetToken {
  email: string;
  token: string;
  created_at?: string | null;
}

// Common auth API responses
export interface AuthResponse {
  user: User;
  token?: string; // when returning API token
}

export type NullableString = string | null | undefined;

export type Auth = {
  user: User;
};

export type TwoFactorSetupData = {
  svg: string;
  url: string;
};

export type TwoFactorSecretKey = {
  secretKey: string;
};
