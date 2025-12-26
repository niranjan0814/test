"use client";

import React, { useState, useEffect } from "react";
import { Sidebar } from "./Sidebar";
import { Header } from "./Header";
import { ThemeProvider } from "../../contexts/ThemeContext";
import { usePathname, useRouter } from "next/navigation";
import { authService } from "../../services/auth.service";
import { Toaster } from 'react-hot-toast';

export type Page =
    | 'dashboard' | 'branches' | 'centers' | 'groups' | 'customers'
    | 'loan-create' | 'loan-approval' | 'loan-list'
    | 'due-list' | 'collections' | 'collection-summary'
    | 'reports'
    | 'finance' | 'fund-transactions' | 'branch-transactions'
    | 'investments' | 'staff-management' | 'roles-privileges'
    | 'complaints' | 'system-config' | 'documents' | 'public-website'
    | string;

export function MainLayout({ children }: { children: React.ReactNode }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const pathname = usePathname();
    const router = useRouter();

    const [user, setUser] = useState({
        name: "Admin User",
        role: "Super Admin", // Fallback Default
        branch: "Head Office"
    });

    useEffect(() => {
        const currentUser = authService.getCurrentUser();
        if (currentUser) {
            // Try to get role from the stored roles array first
            const storedRolesStr = localStorage.getItem('roles');
            let userRole = currentUser.role;

            if (storedRolesStr) {
                try {
                    const roles = JSON.parse(storedRolesStr);
                    if (Array.isArray(roles) && roles.length > 0) {
                        // Use the name of the first role (e.g., 'super_admin')
                        userRole = roles[0].name;
                    }
                } catch (e) {
                    console.error("Failed to parse roles", e);
                }
            }

            setUser({
                name: currentUser.name,
                role: userRole || 'Staff',
                branch: 'Head Office' // You might want to store/retrieve this from user details too
            });
        }
    }, [pathname]);

    // If we are on the login page (or any other public page), render children directly without the shell
    if (pathname === '/login') {
        return <ThemeProvider>{children}</ThemeProvider>;
    }

    // Determine current page ID from pathname
    const getCurrentPage = (): Page => {
        if (pathname === '/' || pathname === '/dashboard') return 'dashboard';

        // Extract the first segment after the slash
        const segments = pathname.split('/').filter(Boolean);
        if (segments.length > 0) {
            return segments[0];
        }

        return 'dashboard';
    };

    const handleNavigate = (pageId: Page) => {
        const routeMap: Record<string, string> = {
            'dashboard': '/',
            'branches': '/branches',
            'centers': '/centers',
            'meeting-scheduling': '/meeting-scheduling',
            'groups': '/groups',
            'customers': '/customers',
        };

        const path = routeMap[pageId as string] || `/${pageId}`;
        router.push(path);
    };

    const handleLogout = async () => {
        try {
            await authService.logout();
            router.push('/login');
        } catch (error) {
            console.error("Logout failed", error);
            // Force redirect anyway
            router.push('/login');
        }
    };

    return (
        <ThemeProvider>
            <Toaster
                position="top-right"
                toastOptions={{
                    duration: 4000,
                    style: {
                        background: '#fff',
                        color: '#363636',
                    },
                    success: {
                        duration: 3000,
                        iconTheme: {
                            primary: '#10b981',
                            secondary: '#fff',
                        },
                    },
                    error: {
                        duration: 4000,
                        iconTheme: {
                            primary: '#ef4444',
                            secondary: '#fff',
                        },
                    },
                }}
            />
            <div className="flex h-screen bg-gray-50 dark:bg-gray-900 transition-colors">
                <Sidebar
                    currentPage={getCurrentPage()}
                    onNavigate={handleNavigate}
                    isOpen={sidebarOpen}
                    userRole={user.role}
                />

                <div className="flex-1 flex flex-col overflow-hidden">
                    <Header
                        user={user}
                        onLogout={handleLogout}
                        onToggleSidebar={() => setSidebarOpen(!sidebarOpen)}
                    />

                    <main className="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900">
                        {children}
                    </main>
                </div>
            </div>
        </ThemeProvider>
    );
}
