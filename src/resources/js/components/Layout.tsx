import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '@/hooks/useAuth';

export function Layout() {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const handleLogout = async () => {
        await logout();
        navigate('/login', { replace: true });
    };

    return (
        <div className="min-h-screen bg-slate-50">
            <header className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
                    <nav className="flex items-center gap-6 text-sm font-medium text-slate-700">
                        <Link to="/payment" className="hover:text-indigo-600">
                            決済
                        </Link>
                        <Link to="/orders" className="hover:text-indigo-600">
                            注文一覧
                        </Link>
                    </nav>
                    <div className="flex items-center gap-4 text-sm text-slate-600">
                        <span>{user?.name}</span>
                        <button
                            type="button"
                            onClick={handleLogout}
                            className="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                        >
                            ログアウト
                        </button>
                    </div>
                </div>
            </header>
            <main className="mx-auto max-w-5xl px-4 py-8">
                <Outlet />
            </main>
        </div>
    );
}
