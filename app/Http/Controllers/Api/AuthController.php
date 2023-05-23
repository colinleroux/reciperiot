<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
//use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Requests\RegisterUserRequest;
class AuthController extends Controller
{
    use HttpResponses;
    /**
     * success response method.
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
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return $this->sendError('Credentials do not match', [], 401);
        }

        $user = User::where('email', $request->email)->first();

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

        $success['token'] = $user->createToken('RecipeRiotApp')->plainTextToken;
        $success['name'] = $user->name;

        return $this->sendResponse($success, 'User created successfully.', $user->id);
    }

    public function logout(Request $request): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'You have successfully been logged out and your token has been removed'
        ]);
    }
}
