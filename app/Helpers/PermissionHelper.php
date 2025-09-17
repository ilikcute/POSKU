<?php

// Add to app/Helpers/PermissionHelper.php

if (! function_exists('canAccess')) {
    /**
     * Check if current user has permission
     */
    function canAccess($permission)
    {
        return auth()->check() && auth()->user()->can($permission);
    }
}

if (! function_exists('hasRole')) {
    /**
     * Check if current user has role
     */
    function hasRole($role)
    {
        return auth()->check() && auth()->user()->hasRole($role);
    }
}

if (! function_exists('canAnyPermission')) {
    /**
     * Check if current user has any of the given permissions
     */
    function canAnyPermission($permissions)
    {
        if (! auth()->check()) {
            return false;
        }

        if (is_string($permissions)) {
            return auth()->user()->can($permissions);
        }

        foreach ($permissions as $permission) {
            if (auth()->user()->can($permission)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('isSuperAdmin')) {
    /**
     * Check if current user is Super Admin
     */
    function isSuperAdmin()
    {
        return auth()->check() && auth()->user()->hasRole('Super Admin');
    }
}

if (! function_exists('userPermissions')) {
    /**
     * Get all permissions for current user
     */
    function userPermissions()
    {
        if (! auth()->check()) {
            return collect([]);
        }

        return auth()->user()->getAllPermissions()->pluck('name');
    }
}
