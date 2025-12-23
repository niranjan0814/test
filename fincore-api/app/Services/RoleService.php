<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleService
{
    /**
     * Get all roles with pagination and filters
     */
    public function getAllRoles(array $filters = [], int $perPage = 20)
    {
        $query = Role::query();

        // Filter by name
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%')
                  ->orWhere('display_name', 'like', '%' . $filters['name'] . '%');
        }

        // Filter by level
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        // Sort
        $sortField = $filters['sort_by'] ?? 'hierarchy';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Create a new role
     */
    public function createRole(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'display_name' => $data['display_name'] ?? ucwords(str_replace('_', ' ', $data['name'])),
                'description' => $data['description'] ?? null,
                'level' => $data['level'] ?? 'staff',
                'hierarchy' => $data['hierarchy'] ?? 100,
                'guard_name' => 'web',
                'is_system' => false,
                'is_editable' => true,
                'is_default' => $data['is_default'] ?? false,
            ]);

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role;
        });
    }

    /**
     * Get role by ID
     */
    public function getRoleById($id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    /**
     * Update a role
     */
    public function updateRole($id, array $data)
    {
        $role = Role::findOrFail($id);

        if (!$role->is_editable) {
            throw new \Exception("Values for this role cannot be edited.");
        }

        return DB::transaction(function () use ($role, $data) {
            $role->update([
                'display_name' => $data['display_name'] ?? $role->display_name,
                'description' => $data['description'] ?? $role->description,
                'level' => $data['level'] ?? $role->level,
                'hierarchy' => $data['hierarchy'] ?? $role->hierarchy,
                'is_default' => $data['is_default'] ?? $role->is_default,
            ]);

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->refresh();
        });
    }

    /**
     * Delete a role
     */
    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system) {
            throw new \Exception("System roles cannot be deleted.");
        }

        if ($role->users()->count() > 0) {
            throw new \Exception("Cannot delete role with assigned users.");
        }

        return $role->delete();
    }

    /**
     * Sync permissions to role
     */
    public function syncRolePermissions($id, array $permissions)
    {
        $role = Role::findOrFail($id);
        
        // Validate if editable? usually permissions are editable even for system roles depending on logic
        // But let's allow it for now
        
        $role->syncPermissions($permissions);
        return $role->load('permissions');
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissionsToRole($id, array $permissions)
    {
        $role = Role::findOrFail($id);
        $role->givePermissionTo($permissions);
        return $role->load('permissions');
    }

    /**
     * Remove permissions from role
     */
    public function removePermissionsFromRole($id, array $permissions)
    {
        $role = Role::findOrFail($id);
        $role->revokePermissionTo($permissions);
        return $role->load('permissions');
    }

    /**
     * Get system roles
     */
    public function getSystemRoles()
    {
        return Role::where('is_system', true)->get();
    }

    /**
     * Get roles by level
     */
    public function getRolesByLevel($level)
    {
        return Role::where('level', $level)->get();
    }

    /**
     * Get default role
     */
    public function getDefaultRole()
    {
        return Role::where('is_default', true)->first();
    }
}