# Lumen PHP Framework


# lumen-JWT
api


### 开发者

-   何渊([@suprehe](https://github.com/coderheyuan))
-   汝涛([@tao](https://github.com/liurutao))
-   建陆([@lu](https://github.com/liurutao))

## 零、安装lumen
composer create-project --prefer-dist laravel/lumen blog

./php ./composer.phar create-project --prefer-dist laravel/lumen blog
### 接入组件
composer require tymon/jwt-auth  --(1.*@rc)

### copy出.env文件 并配置
```php
//bash 使用如下命令获取密钥
 php artisan jwt:secret

//配置如下
JWT_SECRET=NnuFEOqOq5QzcroGvKLyqgxUCELgygIYP8jlyxhRSzcaZEVlN8l78CQMfpsRLHXP//bash获取到的密钥
JWT_TTL = 60
JWT_REFRESH_TTL = 20160
JWT_BLACKLIST_GRACE_PERIOD = 60


```
## 一、修改vendor下的auth.php
/vendor/laravel/config/auth.php
### 修改如下
``` php
return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        // 'guard' => env('AUTH_GUARD', 'api'),
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        // 'api' => ['driver' => 'api'],
        'api' => [
            'driver' => 'jwt',                           
            'provider' => 'users',
        ],
    ],

    'providers' => [
        //
        'users' => [
            'driver' => 'eloquent',
            'model'  => \App\User::class,        
        ],
    ],
]
```
## 二、修改‘bootstrap’ 文件夹下的 ‘app.php’
```php
//取消以下注释
$app->withFacades();
$app->withEloquent();
$app->routeMiddleware([
   'auth' => App\Http\Middleware\Authenticate::class,
]);
$app->register(App\Providers\AuthServiceProvider::class);

//新增JWT注册
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
```

## 三、修改app/User.php
```php
use Tymon\JWTAuth\Contracts\JWTSubject;

//实现JWTSubject接口
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject

```
## 四、在 ‘app/Http/Controller’ 文件夹下新建 'AuthController.php’，内容如下所示：
```php
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

```

## 五、添加路由
```php

$router->post('login','AuthController@login');
$router->group(['prefix'=>'/','middleware'=>'auth:api'],function () use ($router){
   	$router->post('logout','AuthController@logout');
		$router->post('refresh','AuthController@refreshToken');
		$router->post('me','AuthController@me');
});

```

## 六、测试
```json
//api.lumen.com/login POST
{
    "code": "2000",
    "data": {
        "user_id": "1",
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hcGkubHVtZW4uY29tXC9sb2dpbiIsImlhdCI6MTU4NjQxMzY1OSwiZXhwIjoxNTg2NDE3MjU5LCJuYmYiOjE1ODY0MTM2NTksImp0aSI6IlVINVJtTGZOVzBYVmFqU2UiLCJzdWIiOjEsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEifQ.4j8hO_9X_05VU2mnfzKMx19BOznzSjRcfDrUYuNaBeM",
        "expires_in": "1586500059",
        "message": "登录成功"
    }
}

```



