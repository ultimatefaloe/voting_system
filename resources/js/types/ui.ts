// Generic UI and API helper types

import type { ReactNode } from 'react';
import type { BreadcrumbItem } from '@/types/navigation';

export interface ApiError {
    message?: string;
    errors?: Record<string, string[]>;
    status?: number;
}

export interface Paginated<T> {
    data: T[];
    meta: {
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
}

export interface Option<T = string> {
    label: string;
    value: T;
}

export type Nullable<T> = T | null | undefined;

export type AppLayoutProps = {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
};

export type AppVariant = 'header' | 'sidebar';

export type AuthLayoutProps = {
    children?: ReactNode;
    name?: string;
    title?: string;
    description?: string;
};
