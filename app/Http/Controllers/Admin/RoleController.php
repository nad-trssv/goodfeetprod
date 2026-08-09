<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Roles::query()
            ->with(['permissions:id,key,group,action'])
            ->withCount('users')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('action')
            ->get()
            ->groupBy('group');

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('roles', 'name')],
            'appointment_scope' => ['required', Rule::in(['own', 'all'])],
            'is_service_provider' => ['nullable', 'boolean'],
        ]);

        $baseSlug = Str::slug($validated['name']) ?: 'role';
        $slug = $baseSlug;
        $suffix = 2;
        while (Roles::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        Roles::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'appointment_scope' => $validated['appointment_scope'],
            'is_service_provider' => $request->boolean('is_service_provider'),
            'is_system' => false,
        ]);

        return back()->with('success', __('admin_roles.created'));
    }

    public function update(Request $request, Roles $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('roles', 'name')->ignore($role->id)],
            'appointment_scope' => ['required', Rule::in(['own', 'all'])],
            'is_service_provider' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'key')],
        ]);

        if ($role->resolvedSlug() === 'customer') {
            return back()->withErrors(['role' => __('admin_roles.customer_locked')]);
        }

        $permissionKeys = collect($validated['permissions'] ?? [])
            ->intersect(array_keys(config('permissions')))
            ->values();

        if ($role->resolvedSlug() === 'super-admin') {
            $permissionKeys = collect(array_keys(config('permissions')));
            $validated['appointment_scope'] = 'all';
        }

        if ((int) $request->user()->role_id === (int) $role->id && ! $permissionKeys->contains('roles.manage')) {
            return back()->withErrors(['permissions' => __('admin_roles.cannot_remove_own_access')]);
        }

        $role->update([
            'name' => $validated['name'],
            'appointment_scope' => $validated['appointment_scope'],
            'is_service_provider' => $request->boolean('is_service_provider'),
        ]);

        $role->permissions()->sync(
            Permission::whereIn('key', $permissionKeys)->pluck('id')->all()
        );

        return back()->with('success', __('admin_roles.updated'));
    }

    public function destroy(Request $request, Roles $role): RedirectResponse
    {
        if ($role->is_system || $role->users()->exists() || (int) $request->user()->role_id === (int) $role->id) {
            return back()->withErrors(['role' => __('admin_roles.cannot_delete')]);
        }

        $role->delete();

        return back()->with('success', __('admin_roles.deleted'));
    }
}
