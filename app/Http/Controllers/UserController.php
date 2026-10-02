<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manajemen pengguna (staf) dalam satu tenant.
 *
 * Model User tidak memakai trait BelongsToTenant (karena autentikasi harus
 * bekerja sebelum tenant context aktif), jadi setiap query di sini WAJIB
 * memfilter tenant_id secara eksplisit agar tidak bocor antar tenant.
 */
class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('tenant_id', $this->tenantId())
            ->with('roles')
            ->orderBy('name')
            ->get();

        $roles = Role::query()->orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'string'],
        ]);

        $user = User::query()->create([
            'tenant_id' => $this->tenantId(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => true,
        ]);

        if (! empty($validated['role'])) {
            $user->assignRole($this->roleInTenant($validated['role']));
        }

        return back()->with('status', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($user);

        $validated = $request->validate([
            'role' => ['required', 'string'],
        ]);

        $role = $this->roleInTenant($validated['role']);

        $user->roles()->sync([
            $role->getKey() => ['tenant_id' => $this->tenantId()],
        ]);

        return back()->with('status', "Role {$user->name} diperbarui menjadi {$role->name}.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($user);

        abort_if(
            $user->getKey() === $request->user()->getKey(),
            403,
            'Anda tidak dapat menonaktifkan akun sendiri.',
        );

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        $state = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('status', "Akun {$user->name} berhasil {$state}.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($user);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->forceFill(['password' => $validated['password']])->save();

        return back()->with('status', "Password {$user->name} berhasil direset.");
    }

    private function tenantId(): string
    {
        return (string) request()->user()->tenant_id;
    }

    /**
     * Tolak akses ke pengguna tenant lain seolah-olah tidak ada (404).
     */
    private function authorizeTarget(User $user): void
    {
        abort_unless((string) $user->tenant_id === $this->tenantId(), 404);
    }

    private function roleInTenant(string $slug): Role
    {
        return Role::query()
            ->where('tenant_id', $this->tenantId())
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
