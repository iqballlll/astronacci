<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:6',
            ], [
                'name.required' => 'Nama nggak boleh kosong ya, isi dulu dong',
                'email.required' => 'Email-nya mana nih? Belum diisi tuh!',
                'email.email' => 'Yah, format email-nya nggak valid. Coba dicek lagi',
                'email.unique' => 'Email ini udah kepake nih, coba yang lain ya!',
                'password.required' => 'Password-nya harus diisi dong, masa kosong?',
                'password.confirmed' => 'Password-nya nggak cocok nih, coba ketik ulang biar sama!',
                'password.min' => 'Password minimal 6 karakter ya, biar aman juga',
            ]);



            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'image_profile' => ''
            ]);

            return response()->json(['code' => 201, 'message' => 'Berhasil daftar', 'error' => '', 'data' => (object)[]]);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return response()->json([
                'code' => 422,
                'message' => 'Error Validasi',
                'error' => $firstError,
                'data' => (object)[],
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => 'Terjadi Error', 'error' => $e->getMessage(),  'data' => (object)[],], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            if (!auth()->attempt($request->only('email', 'password'))) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['code' => 200, 'message' => 'Berhasil masuk', 'error' => '', 'data' => ['token' => $token, 'user' => $user]]);
        } catch (\Throwable $th) {
            return response()->json(['code' => 500, 'message' => 'Terjadi Error', 'error' => $th->getMessage(),  'data' => (object)[],], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'code' => 404,
                    'message' => 'Email tidak ditemukan!',
                    'error' => '',
                    'data' => (object)[]
                ], 404);
            }

            $otp = rand(100000, 999999);
            $ttl = now()->addMinutes(10);


            Cache::put('otp_' . $user->email, $otp, $ttl);


            Mail::send('otp', ['otp' => $otp], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Kode OTP Reset Password');
            });

            return response()->json([
                'code' => 200,
                'message' => 'OTP telah dikirim ke email kamu!',
                'error' => '',
                'data' => (object)[]
            ]);
        } catch (\Throwable $th) {
            return response()->json(['code' => 500, 'message' => 'Terjadi Error', 'error' => $th->getMessage(),  'data' => (object)[],], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|digits:6',
                'password' => 'required|confirmed|min:6',
            ], [
                'email.required' => 'Email nggak boleh kosong, dong!',
                'email.email' => 'Format email-nya salah, cek lagi ya.',

                'otp.required' => 'Kode OTP-nya harus diisi ya!',
                'otp.digits' => 'OTP harus 6 digit angka yaa, jangan kurang!',

                'password.required' => 'Password baru wajib diisi.',
                'password.confirmed' => 'Password dan konfirmasi password-nya beda tuh!',
                'password.min' => 'Password minimal 6 karakter dong, biar aman!',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'code' => 404,
                    'message' => 'Email tidak ditemukan!',
                    'error' => '',
                    'data' => (object)[]
                ], 404);
            }

            $cachedOtp = Cache::get('otp_' . $user->email);

            if (!$cachedOtp) {
                return response()->json([
                    'code' => 422,
                    'message' => 'OTP sudah kedaluwarsa atau tidak ditemukan!',
                    'error' => '',
                    'data' => (object)[]
                ], 422);
            }

            if ($cachedOtp != $request->otp) {
                return response()->json([
                    'code' => 400,
                    'message' => 'OTP yang kamu masukkan salah!',
                    'error' => '',
                    'data' => (object)[]
                ], 400);
            }


            $user->password = Hash::make($request->password);
            $user->save();

            Cache::forget('otp_' . $user->email);

            return response()->json([
                'code' => 200,
                'message' => 'Password berhasil direset!',
                'error' => '',
                'data' => (object)[]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => 'Terjadi error saat reset password.',
                'error' => $th->getMessage(),
                'data' => (object)[]
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'name' => 'required|string|max:255',
                'image_profile' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ], [
                'name.required' => 'Nama wajib diisi yaa!',
                'image_profile.image' => 'Avatar harus berupa gambar.',
            ]);

            $user->name = $request->name;

            if ($request->hasFile('image_profile')) {
                $imageProfile = $request->file('image_profile')->store('image_profiles', 'public');
                $user->image_profile = $imageProfile;
            }

            $user->save();

            return response()->json([
                'code' => 200,
                'message' => 'Profil berhasil diupdate!',
                'data' => $user,
                'error' => ''
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => 'Gagal update profile',
                'error' => $th->getMessage(),
                'data' => (object)[]
            ]);
        }
    }

    public function getUsers(Request $request)
    {
        try {
            $query = User::query();

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            }

            $users = $query->simplePaginate(10);

            return response()->json([
                'code' => 200,
                'message' => 'List user berhasil diambil.',
                'error' => '',
                'data' => $users,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => 'Gagal mengambil data user.',
                'error' => $th->getMessage(),
                'data' => (object)[]
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'code' => 200,
                'message' => 'Logout berhasil!',
                'data' => (object)[],
                'error' => ''
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => 'Gagal logout',
                'error' => $th->getMessage(),
                'data' => (object)[]
            ]);
        }
    }
}
