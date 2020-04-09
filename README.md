# Lumen PHP Framework


# lumen-JWT
api


### 开发者

-   何渊([@aceld](https://github.com/coderheyuan))
-   刘汝涛([@zhngcho](https://github.com/liurutao))

## 一、修改vendor下的auth.php
/vendor/laravel/config/auth.php
### 修改如下
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




