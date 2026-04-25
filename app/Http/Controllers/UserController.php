<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // Cek admin setiap method
    private function adminOnly()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        $this->adminOnly();
        $users = User::orderBy('role')->orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->adminOnly();
        return view('users.form');
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:admin,pengelola',
            'password' => ['required', Password::min(8)],
        ]);

        // unit_sppg diambil dari config atau hardcode nama SPPG
        User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'role'       => $data['role'],
            'unit_sppg'  => config('app.unit_sppg', 'SPPG Utama'),
            'password'   => Hash::make($data['password']),
        ]);

        return redirect()->route('users.index')
                         ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->adminOnly();
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->adminOnly();

        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role'  => 'required|in:admin,pengelola',
        ]);

        $user->update($data);

        return redirect()->route('users.index')
                         ->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->adminOnly();

        // Tidak boleh hapus diri sendiri
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->adminOnly();

        $data = $request->validate([
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Password user berhasil direset.');
    }
}