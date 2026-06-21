import type { User, ValidationErrorResponse } from '@/types/user';

export class ApiError extends Error {
    constructor(
        message: string,
        public status: number,
        public errors: Record<string, string[]> = {},
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

function getCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : null;
}

async function parseJson<T>(response: Response): Promise<T | null> {
    const text = await response.text();

    if (!text) {
        return null;
    }

    return JSON.parse(text) as T;
}

export const api = {
    async ensureCsrfCookie(): Promise<void> {
        await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
    },

    async get<T>(path: string): Promise<T> {
        const response = await fetch(path, {
            credentials: 'include',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw await this.toApiError(response);
        }

        return (await parseJson<T>(response)) as T;
    },

    async post<T>(path: string, body?: unknown): Promise<T | null> {
        await this.ensureCsrfCookie();

        const xsrfToken = getCookie('XSRF-TOKEN');

        const response = await fetch(path, {
            method: 'POST',
            credentials: 'include',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            },
            body: body === undefined ? undefined : JSON.stringify(body),
        });

        if (!response.ok) {
            throw await this.toApiError(response);
        }

        return parseJson<T>(response);
    },

    async toApiError(response: Response): Promise<ApiError> {
        const payload = await parseJson<ValidationErrorResponse>(response);

        return new ApiError(
            payload?.message ?? response.statusText,
            response.status,
            payload?.errors ?? {},
        );
    },
};

export type LoginResponse = {
    user: User;
};

export type UserResponse = {
    user: User;
};
