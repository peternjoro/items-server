<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function sendResponse($data, $message, $status = 200)
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $data
        ];
        return response()->json($response, $status);
    }
    public function sendError($errorData, $message, $status = 200)
    {
        $response = [];
        $response['status'] = false;
        $response['message'] = $message;
        if (!empty($errorData)) {
            $response['data'] = $errorData;
        }
        return response()->json($response, $status);
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $input = $request->only('name', 'email', 'password', 'c_password');
        $validator = Validator::make($input, [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error');
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        $success['user'] = $user;
        return $this->sendResponse($success, 'user registered successfully');
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
    	$validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error');
        }

        try
        {
            if (!$token = JWTAuth::attempt($input)) {
                return $this->sendError([], "invalid login credentials");
            }
        }
        catch (JWTException $e) {
            return $this->sendError([], $e->getMessage());
        }

        $user = auth()->user();
        $user['token'] = $token;
        $success = [
            $user,
            'token' => $token,
        ];

        return $this->sendResponse($user, 'successful login');
    }
    public function getUser()
    {
        $user = auth()->user();
        if(!$user){
            return $this->sendError([], "user not found");
        }
        return $this->sendResponse($user, "user data retrieved");
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->invalidate(); // invalidate the active auth token
        auth()->logout();
        return response()->json(['status' => true,'message' => 'User successfully signed out']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $newToken = auth()->refresh();
        if($newToken){
            $success = [
                'token' => $newToken,
            ];
            return $this->sendResponse($success, 'token refreshed successfully');
        }
        return $this->sendError([], 'token could not be refreshed');
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
