<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Notifications\ResetPasswordNotification;
use App\Traits\HttpResponses;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
//use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use HttpResponses;
    /**
     * success response method. Used custom response method as opposed to
     * extending BaseController for future flexibility
     *
     * @return JsonResponse
     */
    protected function sendResponse($result, $message, $userId = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];
        if (!is_null($userId)) {
            $response['data']['userid'] = $userId;
        }
        return response()->json($response, 200);
    }
    protected function sendError($message, $data = [], $statusCode = 200)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public function login(LoginUserRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return $this->sendError('Credentials do not match', [], 401);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->hasVerifiedEmail()) {
            Auth::logout(); // Log out the user if their email is not verified
            return $this->sendError('Email not verified. Please check your email for verification instructions.', [], 401);
        }

        return $this->sendResponse([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken
        ], 'User signed in', $user->id);
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $input = $request->validated();

        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);

        if (config('app.email_verification')) {
            $user->sendEmailVerificationNotification();

            return $this->sendResponse([], 'User created successfully. Please check your email for verification instructions.', $user->id);
        } else {
            $user->markEmailAsVerified();

            // Handle the login process or provide an access token if using an API

            return $this->sendResponse([], 'User created successfully. Email verified.', $user->id);
        }
    }
    public function verify(Request $request)
    {
        $user = User::findOrFail($request->id);

        if (! hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return $this->sendError('Invalid verification link', [], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendError('Email already verified', [], 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $loginUrl = URL::route('login');

        $links = [
            'self' => URL::current(),
            'login' => $loginUrl,
        ];

        $response = [
            'data' => [],
            'links' => $links,
            'message' => 'Email verified successfully.',
        ];

        return response()->json($response, 200);
    }



    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Current password is incorrect', [], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->sendResponse([], 'Password changed successfully.');
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->sendError('User not found', [$email], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendError('Email already verified', [], 400);
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
        );

        $user->sendEmailVerificationNotification();

        return $this->sendResponse([], 'Verification email resent. Please check your email, including the spam folder, for the verification link.');
    }

    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->sendError('User not found', [], 404);
        }
        $token = Str::random(64);
        $hashedToken = Hash::make($token);

        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $hashedToken,
            'created_at' => now()
        ]);

        // Send the reset password email
        $user->notify(new ResetPasswordNotification($token));
        return $this->sendResponse([], 'Reset password email sent. Please check your email for further instructions.');
    }
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        $token = $request->route('token');
        $email = $request->input('email');

        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetToken || !Hash::check($token, $resetToken->token)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid or expired password reset token.',
            ]);
        }

        DB::table('users')
            ->where('email', $email)
            ->update([
                'password' => Hash::make($request->input('new_password')),
            ]);

        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();



        return $this->sendResponse([], 'Password updated successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'You have successfully been logged out and your token has been removed'
        ]);
    }
}
