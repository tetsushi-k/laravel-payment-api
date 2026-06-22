import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthGuard, GuestGuard } from '@/components/AuthGuard';
import { Layout } from '@/components/Layout';
import { AuthProvider, useAuth } from '@/hooks/useAuth';
import { LoginPage } from '@/pages/LoginPage';
import { OrderListPage } from '@/pages/OrderListPage';
import { PaymentPage } from '@/pages/PaymentPage';
import { PaymentSuccessPage } from '@/pages/PaymentSuccessPage';

function RootRedirect() {
    const { user, loading } = useAuth();

    if (loading) {
        return (
            <div className="flex min-h-screen items-center justify-center bg-slate-100 text-slate-600">
                読み込み中...
            </div>
        );
    }

    return <Navigate to={user ? '/payment' : '/login'} replace />;
}

export default function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    <Route path="/" element={<RootRedirect />} />

                    <Route element={<GuestGuard />}>
                        <Route path="/login" element={<LoginPage />} />
                    </Route>

                    <Route element={<AuthGuard />}>
                        <Route path="/payment/success" element={<PaymentSuccessPage />} />
                        <Route element={<Layout />}>
                            <Route path="/payment" element={<PaymentPage />} />
                            <Route path="/orders" element={<OrderListPage />} />
                        </Route>
                    </Route>

                    <Route path="*" element={<Navigate to="/" replace />} />
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}
