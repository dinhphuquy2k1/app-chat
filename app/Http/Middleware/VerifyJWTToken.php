<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use \Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Exception;
use Illuminate\Auth\AuthenticationException;
use App\Models\Commons\HttpResponse;
class VerifyJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return HttpResponse::error('Token has expired', 401);
        } catch (TokenInvalidException $e) {
            return HttpResponse::error('Invalid token', 401);
        } catch (Exception $e) {
            return HttpResponse::error('Token not found', 401);
        }
        catch(AuthenticationException $e) {
            return HttpResponse::error('Unauthorized', 401);
        }

        // Lưu dữ liệu người dùng đăng nhập vào request
        $request->merge(['user' => $user]);
        return $next($request);
    }
}
