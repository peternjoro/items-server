<?php

namespace App\Http\Controllers\Api;

use JWTAuth;
use Validator;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class JWTAuthController extends Controller
{
    public $token = true;

    public function registerUser(Request $request)
    {
        $requestMethod = $request->method();
        if(strtolower($requestMethod) == 'get'){
            return response()->json(['error'=> 'method is not supported for route api/register'], 401);
        }
        $validator = Validator::make($request->all(), [
                        'name' => 'required',
                        'email' => 'required|email',
                        'password' => 'required',
                        'c_password' => 'required|same:password',
                    ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['success' => true,'data' => $user], Response::HTTP_OK);
    }
    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $jwt_token = null;

        if (!$jwt_token = JWTAuth::attempt($input)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'success' => true,
            'token' => $jwt_token,
        ]);
    }
    public function logout(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        try
        {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        }
        catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getUser(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
        $user = JWTAuth::authenticate($request->token);
        return response()->json(['user' => $user]);
    }
}
