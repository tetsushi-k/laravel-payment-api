import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '@/hooks/useAuth';

export function AuthGuard() {
    const { user, loading } = useAuth();
    const location = useLocation();

    if (loading) {
        return (
            <div className="flex min-h-screen items-center justify-center bg-slate-100 text-slate-600">
                読み込み中...
            </div>
        );
    }

    if (!user) {
        return <Navigate to="/login" replace state={{ from: location.pathname }} />;
    }

    return <Outlet />;
}

export function GuestGuard() {
    const { user, loading } = useAuth();
    const location = useLocation();
    const redirectTo =
        (location.state as { from?: string } | null)?.from ?? '/orders';

    if (loading) {
        return (
            <div className="flex min-h-screen items-center justify-center bg-slate-100 text-slate-600">
                読み込み中...
            </div>
        );
    }

    if (user) {
        return <Navigate to={redirectTo} replace />;
    }

    return <Outlet />;
}
