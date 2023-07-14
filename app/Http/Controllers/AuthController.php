<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Commons\HttpResponse;
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'user-profile']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ],
        [
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không đúng định dạng',
            'password.required' => 'Password không được để trống',
            'password.min' => 'Password có độ dài tối thiểu 6 kí tự'
        ]);

        if ($validator->fails()) {
            return HttpResponse::error($validator->errors(), 422);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return HttpResponse::error('Tài khoản hoặc mật khẩu không chính xác', 422);
        }

        return HttpResponse::success(['access_token' => $token, 'token_type' => 'bearer', 'expires_in' => auth()->factory()->getTTL() * 180]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();
        return HttpResponse::success('Đăng xuất thành công');
    }

}
