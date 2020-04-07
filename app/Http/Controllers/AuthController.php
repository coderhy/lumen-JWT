<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
  protected $jwt;

  public function __construct(JWTAuth $jwt)
  {
      $this->jwt = $jwt;
  }
  
  /*登录*/
  public function login(Request $request)
  {
      $response = array('code' => '2000');
      $user = User::where('username', $request->input('username'))
                      ->where('password', $request->input('password'))->first();
      if (!$token = Auth::login($user)) {
          return '系统错误，无法生成令牌';
      } else {
       	/*
         	user_id: strval($user->id)
         	token: $token
       	*/
        //   return '登录成功';
          $response['data']['user_id']      = strval($user->id);
          $response['data']['access_token'] = $token;
          $response['data']['expires_in']   = strval(time() + 86400);
          $response['data']['message']   = '登录成功';
          return response()->json($response);
      }
  }
  
  /*用户登出*/
  public function logout()
  {
      $response = array('code' => '2000');

      Auth::invalidate(true);
      $response['data']['message']   = '登出成功';
      return response()->json($response);
  }


  /*更新用户Token*/
  public function refreshToken()
  {

      $response = array('code' => '0');

      if (!$token = Auth::refresh(true, true)) {
          $response['code']     = '5000';
          $response['errorMsg'] = '系统错误，无法生成令牌';
      } else {
          $response['data']['access_token'] = $token;
          $response['data']['expires_in']   = strval(time() + 86400);
      }
      return response()->json($response);
  }

  /*登录后返回用户数据*/
  public function me()
  {
    $response = array('code' => '2000');

    if (Auth::check()) { # JWT同样可以使用Auth门面的check方法
        $response['data'] = Auth::getUser(); # JWT同样可以使用Auth门面的user方法
    } else {
        $response = [
            'status_code' => '4004',
            'msg' => '系统错误，无法查询用户信息'
        ];
    }
    return response()->json($response);

    // return response()->json(Auth::user());
    //   return response()->json(auth()->user());
  }
}
