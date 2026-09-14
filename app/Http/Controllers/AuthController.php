<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Support\NetworkHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.login', [
            'mobileHttpUrl'  => NetworkHelper::mobileLoginUrl(false),
            'mobileHttpsUrl' => NetworkHelper::mobileLoginUrl(true),
            'lanIp'          => NetworkHelper::getLanIp(),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'nik'      => 'required_without:email|nullable|regex:/^[0-9]+$/',
            'email'    => 'required_without:nik|string|nullable',
            'password' => 'required|string|min:8|max:10',
        ], [
            'nik.regex'    => 'NIK hanya boleh berisi angka.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.max' => 'Password maksimal harus 10 karakter.',
        ]);

        $remember = $request->boolean('remember');
        $identifier = $request->nik ?: $request->email;

        // Cek Karyawan pakai NIK
        if ($request->nik) {
            $karyawan = Karyawan::where('nik', $request->nik)->first();
            if ($karyawan && Hash::check($request->password, $karyawan->password)) {
                $user = User::where('email', $karyawan->email)->first();
                
                if (!$user) {
                    $user = User::create([
                        'name'     => $karyawan->nama,
                        'email'    => $karyawan->email,
                        'password' => $karyawan->password,
                        'role'     => 'karyawan',
                    ]);
                }

                if ($user) {
                    Auth::login($user, $remember);
                    $request->session()->regenerate();
                    return redirect()->route('presensi');
                }
            }
        }

        // Cek Admin pakai email
        if ($request->email && Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();
            return $this->redirectByRole();
        }

        return back()
            ->withInput($request->only('nik', 'email', 'login_role'))
            ->withErrors(['nik' => 'NIK atau password salah.', 'email' => 'Email atau password salah.']);
    }

    public function registerForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'nik'      => 'required|regex:/^[0-9]+$/|max:20|unique:karyawans,nik',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:10|confirmed',
            'divisi'   => 'required|string',
            'jabatan'  => 'required|string',
        ], [
            'nama.required'      => 'Nama lengkap wajib diisi.',
            'nik.required'       => 'NIK wajib diisi.',
            'nik.regex'          => 'NIK hanya boleh berisi angka.',
            'nik.unique'         => 'NIK sudah terdaftar.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal harus 8 karakter.',
            'password.max'       => 'Password maksimal harus 10 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'divisi.required'    => 'Divisi wajib diisi.',
            'jabatan.required'   => 'Jabatan wajib diisi.',
        ]);

        // Buat user untuk login
        $user = User::create([
            'name'     => $request->input('nama'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role'     => 'karyawan',
        ]);

        // Buat data karyawan
        Karyawan::create([
            'nik'      => $request->input('nik'),
            'nama'     => $request->input('nama'),
            'email'    => $request->input('email'),
            'divisi'   => $request->input('divisi'),
            'jabatan'  => $request->input('jabatan'),
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');
    }

    public function profilForm()
    {
        $karyawan = Karyawan::where('email', Auth::user()->email)->first();
        return view('auth.profil', compact('karyawan'));
    }

    public function gantiPassword(Request $request)
    {
        $request->validate([
            'password_lama'     => 'required|string',
            'password_baru'     => 'required|string|min:8|max:10|confirmed',
        ], [
            'password_lama.required'  => 'Password lama wajib diisi.',
            'password_baru.required'  => 'Password baru wajib diisi.',
            'password_baru.min'       => 'Password baru minimal harus 8 karakter.',
            'password_baru.max'       => 'Password baru maksimal harus 10 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if (!Hash::check($request->password_lama, Auth::user()->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        Auth::user()->update([
            'password' => Hash::make($request->password_baru)
        ]);

        $karyawan = Karyawan::where('email', Auth::user()->email)->first();
        if ($karyawan) {
            $karyawan->update(['password' => Hash::make($request->password_baru)]);
        }

        return back()->with('success', 'Password berhasil diubah!');
    }

    public function forgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'nik'                   => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:8|max:10|confirmed',
        ], [
            'nik.required'          => 'NIK wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'password.required'     => 'Password baru wajib diisi.',
            'password.min'          => 'Password minimal harus 8 karakter.',
            'password.max'          => 'Password maksimal harus 10 karakter.',
            'password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        $karyawan = Karyawan::where('nik', $request->nik)
            ->where('email', $request->email)
            ->first();

        if (!$karyawan) {
            return back()->withErrors(['email' => 'NIK atau Email tidak cocok dengan data karyawan kami.'])->withInput();
        }

        // Update Karyawan Password
        $karyawan->update([
            'password' => Hash::make($request->password)
        ]);

        // Update User Password (if exists)
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        return redirect()->route('login')->with('success', 'Password Anda berhasil disetel ulang. Silakan login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda berhasil logout.');
    }

    private function redirectByRole()
    {
        $role = Auth::user()->role ?? 'karyawan';

        return match ($role) {
            'admin'  => redirect()->route('admin.dashboard'),
            default  => redirect()->route('presensi'),
        };
    }
}