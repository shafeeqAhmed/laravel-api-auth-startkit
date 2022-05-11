<?php

namespace App\Http\Controllers\Api;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        $data = (new CreateNewUser())->create($request->all());
        $user = collect($data)->only('user_uuid', 'name', 'email', 'profile_photo_url');
        event(new Registered($data));

        return $this->respond([
            'status' => true,
            'message' => 'User Has Been Register  Successfully!',
            'data' => [
                'user' => $user
            ]
        ]);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $detail = collect($user)->only(['user_uuid', 'name', 'email', 'profile_photo_path']);
        $detail['role'] = $user->getRoleNames();
        $detail['permissions'] = $user->getAllPermissions();

        $token = $user->createToken('twilio-chat-app')->plainTextToken;

        $data['user'] = $detail;
        $data['accessToken'] = $token;
        $data['refreshToken'] = $token;
        return $this->respond([
            'status' => true,
            'message' => 'User has been login successfully!',
            'data' => $data
        ]);
    }
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink(
            $request->only('email')
        );
        $statusType = $status === Password::RESET_LINK_SENT ? true : false;

        return $this->respond([
            'status' => $statusType,
            'message' => __($status),
            'data' => []
        ]);
    }
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->respond([
                'status' => false,
                'message' => 'This email is already Verified',
                'data' => []
            ]);
            // return $request->wantsJson()
            //     ? new JsonResponse('', 204)
            //     : redirect()->intended(config('fortify.home'));
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->respond([
            'status' => true,
            'message' => 'Verfication email has been send to you, please check your mail Inbox',
            'data' => []
        ]);
    }
    public function VerificationEmail(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['status' => true, 'message' => 'Your email has been verified Successfully', 'data' => []]);
        } else {
            $request->fulfill();

            if ($request->user()->hasVerifiedEmail()) {
                return response()->json(['status' => true, 'message' => 'Your email has been verified Successfully', 'data' => []]);
            } else {
                return response()->json(['status' => false, 'message' => 'Your email has been verified Successfully', 'data' => []]);
            }
        }
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['status' => true, 'message' => __($status), 'data' => []]);
        } else {
            return response()->json(['status' => false, 'message' => __($status), 'data' => []]);
        }
    }
    public function isVerifiedEmail(Request $request)
    {
        $verify = User::where('id', $request->user()->id)->value('email_verified_at');
        $data['isVerified'] = !is_null($verify);

        return response()->json(['status' => true, 'message' => '', 'data' => $data]);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->respond([
            'status' => true,
            'message' => 'user has been logout successfully!',
            'data' => []
        ]);
    }
    public function refresh(Request $request)
    {
        $data['accessToken'] = $request->user()->createToken('refresh_token')->plainTextToken;
        return response()->json(['status' => true, 'message' => '', 'data' => $data]);
    }
    public function userDetail(Request $request)
    {
        $data = User::where('id', $request->user()->id)->first();
        $user = collect($data)->only('user_uuid', 'name', 'email', 'profile_photo_url');
        return $this->respond([
            'status' => true,
            'message' => 'User Has Been Register  Successfully!',
            'data' => [
                'user' => $user
            ]
        ]);
    }
}
