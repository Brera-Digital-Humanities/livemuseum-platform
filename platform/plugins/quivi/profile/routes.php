<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Quivi\Profile\Classes\JwtMiddleware;
use Quivi\Profile\Classes\JwtService;
use Quivi\Profile\Classes\UserResource;
use Winter\Storm\Auth\AuthenticationException;
use Winter\User\Models\Settings as UserSettings;
use Winter\User\Models\User as UserModel;

Route::group(['prefix' => 'api/v1/users'], function () {
    Route::get('check', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|required_without:email|string|between:2,255',
            'email' => 'nullable|required_without:username|email|between:6,255',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $usernameExists = $request->filled('username')
            ? UserModel::where('username', $request->input('username'))->exists()
            : null;

        $emailExists = $request->filled('email')
            ? UserModel::where('email', $request->input('email'))->exists()
            : null;

        return Response::json([
            'username' => [
                'exists' => $usernameExists,
                'available' => $usernameExists === null ? null : !$usernameExists,
            ],
            'email' => [
                'exists' => $emailExists,
                'available' => $emailExists === null ? null : !$emailExists,
            ],
        ]);
    });

    Route::post('register', function (Request $request) {
        if (!UserSettings::get('allow_registration', true)) {
            return Response::json(['error' => 'Registrations are disabled.'], 403);
        }

        if (
            UserSettings::get('use_register_throttle', true) &&
            UserModel::isRegisterThrottled($request->ip())
        ) {
            return Response::json(['error' => 'Registration is throttled.'], 429);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,255',
            'surname' => 'required|string|between:2,255',
            'username' => 'required|string|between:2,255|unique:users,username',
            'email' => 'required|email|between:6,255|unique:users,email',
            'birth_date' => 'required|date_format:Y-m-d|before:today',
            'password' => 'required|string|between:' . UserModel::getMinPasswordLength() . ',255|confirmed',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'name',
            'surname',
            'username',
            'email',
            'birth_date',
            'password',
            'password_confirmation',
        ]);

        $data['created_ip_address'] = $request->ip();
        $data['last_ip_address'] = $request->ip();

        \Event::fire('winter.user.beforeRegister', [&$data]);

        $requireActivation = UserSettings::get('require_activation', true);
        $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
        $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;

        $user = Auth::register($data, $automaticActivation);

        \Event::fire('winter.user.register', [$user, $data]);

        if ($userActivation) {
            $code = implode('!', [$user->id, $user->getActivationCode()]);
            $link = rtrim((string) env('USER_ACTIVATION_URL', config('app.url')), '/') . '?code=' . urlencode($code);

            Mail::send('winter.user::mail.activate', [
                'name' => $user->name,
                'link' => $link,
                'code' => $code,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name);
            });
        }

        $response = [
            'user' => UserResource::make($user),
            'requires_activation' => $requireActivation && !$user->is_activated,
        ];

        if (!$response['requires_activation']) {
            $token = (new JwtService())->issueForUser($user);
            $response['access_token'] = $token['token'];
            $response['token_type'] = $token['token_type'];
            $response['expires_in'] = $token['expires_in'];
        }

        return Response::json($response, 201);
    });

    Route::post('login', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'login' => 'required_without:email|string',
            'email' => 'required_without:login|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $login = $request->input('login', $request->input('email'));
        $credentials = [
            'login' => $login,
            'password' => $request->input('password'),
        ];

        try {
            $user = Auth::authenticate($credentials, false);
        } catch (AuthenticationException $ex) {
            return Response::json(['error' => 'Invalid credentials.'], 401);
        }

        if ($user->isBanned()) {
            Auth::logout();
            return Response::json(['error' => 'User is banned.'], 403);
        }

        if ($request->ip()) {
            $user->touchIpAddress($request->ip());
        }

        $token = (new JwtService())->issueForUser($user);

        return Response::json([
            'access_token' => $token['token'],
            'token_type' => $token['token_type'],
            'expires_in' => $token['expires_in'],
            'user' => UserResource::make($user),
        ]);
    });

    Route::post('password/forgot', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|between:6,255',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $user = UserModel::findByEmail($request->input('email'));

        if (!$user || $user->is_guest) {
            return Response::json(['error' => 'User not found.'], 404);
        }

        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);
        $resetUrl = env('PASSWORD_RESET_URL');
        $link = $resetUrl
            ? rtrim($resetUrl, '/') . '?code=' . urlencode($code)
            : rtrim(config('app.url'), '/') . '/password/reset?code=' . urlencode($code);

        Mail::send('winter.user::mail.restore', [
            'name' => $user->name,
            'username' => $user->username,
            'link' => $link,
            'code' => $code,
        ], function ($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });

        return Response::json(['success' => true]);
    });

    Route::post('password/reset', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'password' => 'required|string|between:' . UserModel::getMinPasswordLength() . ',255|confirmed',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $parts = explode('!', $request->input('code'));

        if (count($parts) !== 2 || !$parts[0] || !$parts[1]) {
            return Response::json(['error' => 'Invalid reset code.'], 422);
        }

        [$userId, $code] = $parts;
        $user = Auth::findUserById($userId);

        if (!$user || !$user->attemptResetPassword($code, $request->input('password'))) {
            return Response::json(['error' => 'Invalid reset code.'], 422);
        }

        return Response::json(['success' => true]);
    });

    Route::get('check', function (Request $request) {
        $response = null;
        $middleware = new JwtMiddleware();
        $middleware->handle($request, function ($req) use (&$response) {
            $user = $req->attributes->get('api_user');
            $response = Response::json([
                'logged'    => true,
                'user_id'   => $user->id,
                'username'  => $user->username,
            ]);
        });
        return $response ?? Response::json(['logged' => false]);
    });

    Route::group(['middleware' => [JwtMiddleware::class]], function () {
        Route::get('logged', function (Request $request) {
            return Response::json(UserResource::make($request->attributes->get('api_user')));
        });

        Route::match(['put', 'patch'], 'profile', function (Request $request) {
            $user = $request->attributes->get('api_user');

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|between:2,255',
                'surname' => 'sometimes|required|string|between:2,255',
                'username' => 'sometimes|required|string|between:2,255|unique:users,username,' . $user->id,
                'email' => 'sometimes|required|email|between:6,255|unique:users,email,' . $user->id,
                'birth_date' => 'sometimes|required|date_format:Y-m-d|before:today',
            ]);

            if ($validator->fails()) {
                return Response::json(['errors' => $validator->errors()], 422);
            }

            $user->fill($request->only([
                'name',
                'surname',
                'username',
                'email',
                'birth_date',
            ]));

            $user->save();

            return Response::json(UserResource::make($user->fresh()));
        });

        Route::post('refresh', function (Request $request) {
            $token = (new JwtService())->issueForUser($request->attributes->get('api_user'));

            return Response::json([
                'access_token' => $token['token'],
                'token_type' => $token['token_type'],
                'expires_in' => $token['expires_in'],
            ]);
        });

        Route::post('logout', function () {
            return Response::json(['success' => true]);
        });
    });
});
