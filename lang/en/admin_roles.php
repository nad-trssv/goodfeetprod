<?php

return [
    'title' => 'Roles and permissions', 'subtitle' => 'Control which sections employees can access and what they can do.',
    'new_role' => 'New role', 'role_name' => 'Role name', 'role_example' => 'For example: Branch manager',
    'data_scope' => 'Appointment and customer scope', 'scope_own' => 'Own appointments and customers only',
    'scope_all' => 'All specialists, appointments and customers', 'create' => 'Add role',
    'service_provider' => 'Employees with this role provide services and appear as specialists',
    'save' => 'Save permissions', 'delete' => 'Delete role', 'system' => 'System role',
    'employees' => ':count employees', 'customer_locked' => 'The customer role belongs to a separate authentication system and cannot be edited here.',
    'cannot_remove_own_access' => 'You cannot remove role management access from your own role.',
    'cannot_delete' => 'A system, assigned, or your own role cannot be deleted.',
    'created' => 'Role created. You can now configure its permissions.', 'updated' => 'Role permissions saved.',
    'deleted' => 'Role deleted.', 'access_denied' => 'You do not have permission to perform this action.',
    'permission_hint' => 'The server verifies these permissions on every request. Hiding UI controls is not a security check.',
    'groups' => [
        'dashboard'=>'Dashboard','appointments'=>'Appointments','customers'=>'Customers','services'=>'Services','staff'=>'Staff',
        'schedules'=>'Schedules and exceptions','master_services'=>'Employee services','rooms'=>'Rooms','settings'=>'Site settings',
        'promo_codes'=>'Promo codes','notifications'=>'Notifications','reschedule_requests'=>'Reschedule requests',
        'activity_logs'=>'Activity log','roles'=>'Roles and permissions','profile'=>'Own profile',
    ],
    'actions' => ['view'=>'View','create'=>'Create','update'=>'Edit','delete'=>'Delete / cancel','status'=>'Change status','message'=>'Message customer','manage'=>'Full management'],
];
