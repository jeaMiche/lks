<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoanController;

// Login → dapat token
Route::post('/login', function (Request $request) {
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah.',
        ], 401);
    }

    $user  = User::where('email', $request->email)->first();
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'token'   => $token,
        'user'    => [
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ],
    ]);
});

// Route yang butuh token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/loans',                  [LoanController::class, 'index']);
    Route::post('/loans',                 [LoanController::class, 'store']);
    Route::patch('/loans/{loan}/review',  [LoanController::class, 'review']);
    Route::patch('/loans/{loan}/approve', [LoanController::class, 'approve']);
    Route::patch('/loans/{loan}/reject',  [LoanController::class, 'reject']);
    Route::patch('/loans/{loan}/disburse',[LoanController::class, 'disburse']);
    Route::post('/loans/{loan}/pay',      [LoanController::class, 'pay']);
});