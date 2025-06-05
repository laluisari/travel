<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ResponseResource;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function adminLoginView(Request $request)
    {
        $credentials = $request->only('no_wa', 'password');
        $title = 'Login Admin';
        return view('auth/loginAdmin', compact('title'));
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'noWa_or_email' => 'required',
            'password' => 'required',
        ]);

        $loginField = filter_var($request->noWa_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'no_wa';

        $credentials = [
            $loginField => $request->noWa_or_email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('token')->plainTextToken;
            return redirect()->route('users.index')->with('success', 'Login successfully.');
        }

        return redirect()->route('login')->with('error', 'Invalid credentials. Please try again.');
    }

    public function customerLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'noWa_or_email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return new ResponseResource(false, $validator->errors()->first(), null, 422);
        }

        $loginField = filter_var($request->noWa_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'no_wa';

        $credentials = [
            $loginField => $request->noWa_or_email,
            'password' => $request->password,
        ];

        if (Auth::guard('customer')->attempt($credentials)) {
            $customer = Auth::guard('customer')->user();
            $token = $customer->createToken('customer')->plainTextToken;


            return new ResponseResource(true, "Login berhasil", ['token' => $token, 'customer' => $customer], 200);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function customerRegister(Request $request)
    {
        $checkCustomer = Customer::where('email', $request->email)
            ->orWhere('no_wa', $request->no_wa)
            ->first();

        if ($checkCustomer) {
            if ($checkCustomer->type == 'offline') {
                $checkCustomer->update([
                    'name' => $request->name,
                    'no_wa' => $request->no_wa,
                    'email' => $request->email,
                    'password' => $request->password,
                    'type' => 'online', 
                ]);

                return new ResponseResource(true, "Data berhasil diperbarui", $checkCustomer, 200);
            } else {
                return new ResponseResource(false, "Anda sudah terdaftar sebagai customer", null, 422);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:customers,email', 
            'no_wa' => 'required|min:2|unique:customers,no_wa', 
            'password' => 'required|min:6',
        ], [
            'no_wa.unique' => 'Nomor WhatsApp sudah terdaftar',
            'email.unique' => 'Email sudah terdaftar',
            'password.min' => 'Password harus memiliki minimal 6 karakter',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return new ResponseResource(false, $errors[0], null, 422);
        }

        // Membuat user baru dan menyimpan password yang sudah di-hash
        $user = Customer::create([
            'name' => $request->name,
            'no_wa' => $request->no_wa,
            'email' => $request->email,
            'password' => $request->password, 
            'type' => 'online',
        ]);

        return new ResponseResource(true, "Data berhasil ditambahkan", $user, 200);
    }


    public function adminLogout(Request $request)
    {
        Auth::logout(); // Logout user dari sesi
        $request->session()->invalidate(); // Hapus semua data sesi
        $request->session()->regenerateToken(); // Regenerasi token CSRF untuk keamanan

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    public function customerLogout(Request $request)
    {
        Auth::guard('customer')->logout(); // Logout user dari guard customer


        return new ResponseResource(true, "Logout berhasil", null, 200);
    }
}
