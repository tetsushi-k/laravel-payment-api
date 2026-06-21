import {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
    type ReactNode,
} from 'react';
import { api, ApiError, type LoginResponse, type UserResponse } from '@/api/client';
import type { User } from '@/types/user';

type AuthContextValue = {
    user: User | null;
    loading: boolean;
    login: (email: string, password: string) => Promise<void>;
    logout: () => Promise<void>;
    refreshUser: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [user, setUser] = useState<User | null>(null);
    const [loading, setLoading] = useState(true);

    const refreshUser = useCallback(async () => {
        try {
            const data = await api.get<UserResponse>('/api/user');
            setUser(data.user);
        } catch (error) {
            if (error instanceof ApiError && error.status === 401) {
                setUser(null);
                return;
            }

            throw error;
        }
    }, []);

    useEffect(() => {
        refreshUser().finally(() => setLoading(false));
    }, [refreshUser]);

    const login = useCallback(async (email: string, password: string) => {
        const data = await api.post<LoginResponse>('/api/login', { email, password });
        setUser(data?.user ?? null);
    }, []);

    const logout = useCallback(async () => {
        await api.post('/api/logout');
        setUser(null);
    }, []);

    const value = useMemo(
        () => ({ user, loading, login, logout, refreshUser }),
        [user, loading, login, logout, refreshUser],
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth must be used within AuthProvider');
    }

    return context;
}
