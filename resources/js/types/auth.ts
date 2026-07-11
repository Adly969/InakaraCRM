export type User = {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    avatar?: string | null;
    email_verified_at: string | null;
    is_active: boolean;
    roles: string[];
    permissions: string[];
    created_at: string;
    updated_at: string;
};

export type Auth = {
    user: User;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */
