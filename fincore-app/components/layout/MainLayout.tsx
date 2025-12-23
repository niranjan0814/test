"use client";

import React, { useState } from "react";
import { Sidebar } from "./Sidebar";
import { Header } from "./Header";
import { ThemeProvider } from "../../contexts/ThemeContext";
import { usePathname, useRouter } from "next/navigation";

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

    // Mock user for now - in a real app this would come from an auth context
    const user = {
        name: "Admin User",
        role: "Super Admin", // Setting as Super Admin to show all options
        branch: "Head Office"
    };

    // Determine current page ID from pathname
    const getCurrentPage = (): Page => {
        if (pathname === '/' || pathname === '/dashboard') return 'dashboard';

        // Extract the first segment after the slash
        // e.g., /branches/create -> branches
        const segments = pathname.split('/').filter(Boolean);
        if (segments.length > 0) {
            // Special mapping for specific paths if needed, otherwise use the segment
            return segments[0];
        }

        return 'dashboard';
    };

    const handleNavigate = (pageId: Page) => {
        // Map page IDs to routes
        const routeMap: Record<string, string> = {
            'dashboard': '/',
            'branches': '/branches',
            'centers': '/centers',
            'groups': '/groups',
            'customers': '/customers',
            // Add more mappings as new pages are created
        };

        // Default to /pageId if not in map
        // For navigation items that don't have pages yet, this might 404, which is expected during dev
        const path = routeMap[pageId as string] || `/${pageId}`;
        router.push(path);
    };

    return (
        <ThemeProvider>
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
                        onLogout={() => console.log("Logout")}
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
