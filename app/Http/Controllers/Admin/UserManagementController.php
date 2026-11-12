<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(['all', 'admin', 'user'])],
            'provider' => ['nullable', Rule::in(['all', 'google', 'email'])],
        ]);

        $q = trim($filters['q'] ?? '');
        $role = $filters['role'] ?? 'all';
        $provider = $filters['provider'] ?? 'all';

        $usersQuery = User::query()->select([
            'id',
            'name',
            'email',
            'role',
            'google_id',
            'email_verified_at',
            'created_at',
        ]);

        if ($q !== '') {
            // Escape LIKE wildcard: pencarian berdasarkan teks literal.
            $term = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
            $usersQuery->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($role !== 'all') {
            $usersQuery->where('role', $role);
        }

        if ($provider === 'google') {
            $usersQuery->whereNotNull('google_id');
        } elseif ($provider === 'email') {
            $usersQuery->whereNull('google_id');
        }

        $users = $usersQuery
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderByRaw('LOWER(name) ASC')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        $counts = User::query()
            ->selectRaw(
                "COUNT(*) AS total, "
                . "COUNT(CASE WHEN role = 'admin' THEN 1 END) AS admins, "
                . "COUNT(CASE WHEN role = 'user' THEN 1 END) AS regular_users, "
                . "COUNT(google_id) AS google"
            )
            ->first();

        $stats = [
            'total' => (int) $counts->total,
            'admin' => (int) $counts->admins,
            'user' => (int) $counts->regular_users,
            'google' => (int) $counts->google,
        ];

        return view('admin.users.index', compact('users', 'stats', 'q', 'role', 'provider'));
    }

    public function show(User $user): View
    {
        $reports = \App\Models\LostFoundItem::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['lost', 'found'])
            ->selectRaw('type, COUNT(*) AS total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $activity = [
            'lost' => (int) $reports->get('lost', 0),
            'found' => (int) $reports->get('found', 0),
            'claims' => \App\Models\ItemClaim::query()->where('user_id', $user->id)->count(),
        ];

        return view('admin.users.show', compact('user', 'activity'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'user'])],
        ]);

        // Dilarang mengubah role akun yang sedang digunakan.
        if ((int) $user->id === (int) $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun sendiri.');
        }

        $result = DB::transaction(function () use ($user, $validated) {
            // Kunci seluruh baris admin saat melakukan perubahan role.
            // Ini mencegah dua permintaan bersamaan menghapus admin terakhir.
            $admins = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $target = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $newRole = $validated['role'];

            if ($target->role === $newRole) {
                return 'unchanged';
            }

            if ($target->role === 'admin' && $newRole === 'user' && $admins->count() <= 1) {
                return 'last_admin';
            }

            $previousRole = $target->role;
            $target->role = $newRole;
            $target->save();
            Log::info('FindBack admin user role changed', [
                'actor_id' => auth()->id(),
                'target_user_id' => $target->id,
                'old_role' => $previousRole,
                'new_role' => $newRole,
            ]);

            return 'updated';
        }, 3);

        if ($result === 'last_admin') {
            return back()->with(
                'error',
                'Administrator terakhir tidak boleh diturunkan menjadi user.',
            );
        }

        if ($result === 'unchanged') {
            return back()->with('info', 'Role akun tidak berubah.');
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Role pengguna berhasil diperbarui.');
    }
}
