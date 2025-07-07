<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OtpVerification;
use App\Services\CustomSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(CustomSmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate phone number format
        if (!$this->smsService->validatePhoneNumber($request->phone)) {
            return response()->json([
                'message' => 'Invalid phone number format'
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'phone_verified_at' => null,
            ]);

            // Generate and send OTP
            $this->generateAndSendOtp($user->phone);

            return response()->json([
                'message' => 'Registration successful. OTP sent to your phone.',
                'user_id' => $user->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate phone number format
        if (!$this->smsService->validatePhoneNumber($request->phone)) {
            return response()->json([
                'message' => 'Invalid phone number format'
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        try {
            // Generate and send OTP for login
            $this->generateAndSendOtp($user->phone);

            return response()->json([
                'message' => 'OTP sent to your phone for verification.'
            ]);

        } catch (\Exception $e) {
            Log::error('Login OTP failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $otpRecord = OtpVerification::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Mark phone as verified
        $user->phone_verified_at = Carbon::now();
        $user->save();

        // Delete used OTP
        $otpRecord->delete();

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Phone verified successfully',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        try {
            $this->generateAndSendOtp($user->phone);

            return response()->json([
                'message' => 'OTP resent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Resend OTP failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to resend OTP. Please try again.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    private function generateAndSendOtp($phone)
    {
        // Delete any existing OTP for this phone
        OtpVerification::where('phone', $phone)->delete();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in database
        OtpVerification::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(config('sms.otp.expires_in_minutes', 10)),
        ]);

        // Send SMS using our custom service
        $result = $this->smsService->sendOtp($phone, $otp);

        if (!$result['success']) {
            throw new \Exception('Failed to send SMS: ' . ($result['error'] ?? 'Unknown error'));
        }

        Log::info("OTP sent successfully to {$phone}", [
            'message_id' => $result['message_id'] ?? null,
            'method' => $result['method'] ?? 'custom'
        ]);
    }

    private function sendSms($phone, $otp)
    {
        // This method is deprecated - use generateAndSendOtp instead
        // Keeping for backward compatibility
        return $this->generateAndSendOtp($phone);
    }
}
