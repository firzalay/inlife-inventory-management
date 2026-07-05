<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a list of users with search, status, and role filters.
     */
    public function index(Request $request): View
    {
        $query = User::with(['roles', 'approvedBy'])->latest();

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        // Filter: Role
        if ($request->filled('role')) {
            $query->role($request->string('role'));
        }

        // Search: Name or Email
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show details of a specific user.
     */
    public function show(User $user): View
    {
        $roles = Role::all();

        return view('users.show', compact('user', 'roles'));
    }

    /**
     * Approve user registration.
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        $user->status = 'approved';
        $user->approved_at = now();
        $user->approved_by = auth()->id();

        if ($request->filled('role')) {
            $user->syncRoles($request->input('role'));
        }

        $user->save();

        return redirect()->back()->with('success', "Pengguna {$user->name} telah disetujui.");
    }

    /**
     * Reject user registration.
     */
    public function reject(Request $request, User $user): RedirectResponse
    {
        $user->status = 'rejected';
        $user->save();

        return redirect()->back()->with('success', "Pendaftaran pengguna {$user->name} telah ditolak.");
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user->syncRoles($request->input('role'));

        return redirect()->back()->with('success', "Role pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} berhasil dihapus.");
    }
}
