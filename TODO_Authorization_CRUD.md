# TODO: Implement Authorization CRUD under Shifts Menu

## Current Work
Creating a CRUD interface for managing Authorization passwords dynamically, integrated under the Shifts menu in the sidebar. This allows users to add, edit, and delete authorizations without manual controller changes.

## Key Technical Concepts
- Laravel: Model updates (fillable, casts, relationships), Controller with CRUD methods, Resource routes with middleware.
- Vue.js/Inertia: Form handling, validation, pagination, consistent UI with existing Shifts pages.
- Permissions: Using existing 'view_shifts' permission for access control.
- Security: Password hashing with Hash::make, store-specific filtering.

## Relevant Files and Code
- **app/Models/Authorization.php**: Updated with fillable ['name', 'password', 'store_id'], hidden 'password', casts 'password' => 'hashed', belongsTo Store relationship.
- **app/Http/Controllers/AuthorizationController.php**: New controller with index (paginated by store), create/store (validation, hashing), show/edit/update (optional password), destroy (store check).
- **routes/web.php**: Added Route::resource('shifts/authorizations', AuthorizationController::class)->names('shifts.authorizations')->middleware('check.permission:view_shifts');
- **resources/js/Pages/Shifts/Authorizations/Index.vue**: New page with table view, pagination, delete confirmation, empty state.

## Problem Solving
- Ensured store-specific access to prevent cross-store data access.
- Password handling: Required on create, optional on update; always hashed.
- UI consistency: Matched Shifts/Index.vue styling (backdrop-blur, gradients, responsive table).

## Pending Tasks and Next Steps
- [ ] Create resources/js/Pages/Shifts/Authorizations/Create.vue: Form for adding new authorization (name, password fields, validation handling).
  - "Next: Implement form submission to store method, redirect to index on success."
- [ ] Create resources/js/Pages/Shifts/Authorizations/Edit.vue: Form for editing existing authorization (prefill name, optional password, validation).
  - "Next: Implement form submission to update method, handle optional password."
- [ ] Update resources/js/Layouts/AuthenticatedLayout.vue: Add sub-menu item under Shifts: { route: 'shifts.authorizations.index', label: 'Kelola Authorization' }.
  - "Next: Ensure it appears only if user has 'view_shifts' permission."
- [ ] Add permission 'manage_authorizations' if needed (via RolePermissionController), but initially use 'view_shifts'.
  - "Next: Update middleware in routes if new permission added."
- [ ] Test CRUD: Use execute_command to run php artisan serve, browser_action to navigate and test forms.
  - "Recent conversation: User approved plan to proceed with model, controller, routes, and Index.vue. Now implementing forms and sidebar."

Update this file after each step completion.
