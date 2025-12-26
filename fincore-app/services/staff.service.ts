import { User, Permission, Role, Staff } from '../types/staff.types';
import { API_BASE_URL, getHeaders } from './api.config';

export const staffService = {
    getUsers: async (): Promise<User[]> => {
        try {
            const response = await fetch(`${API_BASE_URL}/admins`, { headers: getHeaders() });

            if (!response.ok) return [];

            const data = await response.json();

            // Safety check for null/undefined data
            if (!data || !data.data || !Array.isArray(data.data)) {
                return [];
            }

            return data.data.map((u: any) => ({
                id: u.id,
                name: u.user_name || u.email,
                email: u.email,
                role: u.roles?.[0]?.display_name || 'N/A',
                branch: u.branch_id ? 'Branch ' + u.branch_id : 'Head Office',
                status: u.is_active ? 'Active' : 'Inactive'
            }));
        } catch (error) {
            console.error("Error fetching users", error);
            return [];
        }
    },

    getStaffList: async (): Promise<Staff[]> => {
        try {
            const response = await fetch(`${API_BASE_URL}/staffs`, { headers: getHeaders() });
            if (!response.ok) return [];

            const json = await response.json();
            return json.data || [];
        } catch (error) {
            console.error("Error fetching staff list", error);
            return [];
        }
    },

    // getRoles: async (): Promise<Role[]> => {
    //     try {
    //         const response = await fetch(`${API_BASE_URL}/roles`, { headers: getHeaders() });

    //         if (!response.ok) {
    //             console.error(`Status: ${response.status}`);
    //             return [];
    //         }

    //         const data = await response.json();
    //         console.log('Roles API Response:', data); // Debug log

    //         if (!data) return [];

    //         // Handle BaseController.paginated format: { success: true, data: { items: [...], pagination: {...} } }
    //         if (data.data && data.data.items && Array.isArray(data.data.items)) {
    //             return data.data.items;
    //         }

    //         // Handle standard list format: { success: true, data: [...] }
    //         if (data.data && Array.isArray(data.data)) {
    //             return data.data;
    //         }

    //         // Handle standard pagination format: { success: true, data: { data: [...] } }
    //         if (data.data?.data && Array.isArray(data.data.data)) {
    //             return data.data.data;
    //         }

    //         console.warn('Roles data structure not recognized', data);
    //         return [];
    //     } catch (error) {
    //         console.error("Error fetching roles", error);
    //         return [];
    //     }
    // },

    getPermissions: async (): Promise<Permission[]> => {
        return [
            { module: 'Dashboard', view: true, create: true, edit: true, delete: false },
            { module: 'Customers', view: true, create: true, edit: true, delete: false },
            { module: 'Loans', view: true, create: true, edit: true, delete: false },
            { module: 'Collections', view: true, create: true, edit: false, delete: false },
            { module: 'Reports', view: true, create: false, edit: false, delete: false },
            { module: 'Finance', view: true, create: true, edit: true, delete: true },
            { module: 'Shareholders', view: true, create: true, edit: true, delete: true },
            { module: 'System Config', view: true, create: true, edit: true, delete: true }
        ];
    },

    createAdmin: async (userData: any): Promise<void> => {
        const response = await fetch(`${API_BASE_URL}/admins`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify(userData)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to create admin');
        }
    },

    createUser: async (userData: any): Promise<void> => {
        const role = userData.role?.toLowerCase() || '';

        if (role.includes('admin') || role.includes('super')) {
            return staffService.createAdmin(userData);
        }

        // For other roles (Manager, Staff, Field Officer, etc.) use the generic User API
        const payload = {
            // Generate a username from name or email if not provided
            user_name: userData.name ? userData.name.replace(/\s+/g, '').toLowerCase() + Math.floor(Math.random() * 1000) : userData.email.split('@')[0],
            email: userData.email,
            password: userData.password,
            password_confirmation: userData.password, // Match generic register requirements
            is_active: true,
            roles: [parseInt(userData.roleId)] // Expects array of IDs
        };

        const response = await fetch(`${API_BASE_URL}/users`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `Failed to create ${role}`);
        }
    },
    getAllRoles: async (): Promise<Role[]> => {
        const response = await fetch(`${API_BASE_URL}/roles/all`, {
            headers: getHeaders()
        });

        if (!response.ok) {
            console.error(`Error fetching roles: ${response.status} ${response.statusText}`);
            throw new Error(`Failed to fetch roles: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        return data.data;
    },


};
