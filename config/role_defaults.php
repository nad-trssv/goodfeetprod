<?php

return [
    'super-admin' => [
        'permissions' => array_keys(config('permissions')),
        'appointment_scope' => 'all',
        'is_service_provider' => true,
    ],
    'master' => [
        'permissions' => [
            'dashboard.view', 'appointments.view', 'appointments.create', 'appointments.update',
            'appointments.delete', 'appointments.status', 'appointments.message', 'customers.view',
            'crm.view', 'crm.update', 'crm.documents', 'crm.chat.view', 'crm.chat.reply',
            'services.view', 'schedules.view', 'schedules.update', 'master_services.view',
            'master_services.update', 'notifications.view', 'notifications.update',
            'reschedule_requests.view', 'reschedule_requests.update', 'profile.view', 'profile.update',
        ],
        'appointment_scope' => 'own',
        'is_service_provider' => true,
    ],
    'customer' => [
        'permissions' => [],
        'appointment_scope' => 'own',
        'is_service_provider' => false,
    ],
    'receptionist' => [
        'permissions' => [
            'dashboard.view', 'appointments.view', 'appointments.create', 'appointments.update',
            'appointments.delete', 'appointments.status', 'appointments.message', 'customers.view',
            'customers.update', 'services.view', 'staff.view', 'schedules.view',
            'crm.view', 'crm.update', 'crm.documents', 'crm.chat.view', 'crm.chat.reply',
            'master_services.view', 'rooms.view', 'notifications.view', 'notifications.update',
            'reschedule_requests.view', 'reschedule_requests.update', 'profile.view', 'profile.update',
        ],
        'appointment_scope' => 'all',
        'is_service_provider' => false,
    ],
];
