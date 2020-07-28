## hyperf-sanctum
Hyperf 的轻量级API认证系统，借鉴 Laravel Sanctum

## 安装

#### 引入包
```
composer require liuchang103/hyperf-sanctum
```

#### 发布文件
```
php bin/hyperf.php vendor:publish liuchang103/hyperf-sanctum
```

#### 数据库迁移
```
php bin/hyperf.php migrate
```

## 使用

#### 模型中加入 Trait
```
class User extends Model
{
    use \HyperfSanctum\Tokens;
}
```

#### 创建 Token
tokenCreate(名称, [权限名]) 会返回 hash 过的 token.

权限名：只要存在 * 将为所有权，默认为 *
```
$user = User::find(1);
        
$user->tokenCreate('web-token');
$user->tokenCreate('api-token', ['update']);
```

#### 创建 Middleware
只需要继承 HyperfSanctum\Middleware 并实现 unauthenticated 方法即可
```
class Authentication extends \HyperfSanctum\Middleware
{
    // 未通过验证
    protected function unauthenticated()
    {
        return $this->response->json([
            'code' => -1,
            'message' => 'Unauthenticated'
        ]);
    }
}
```

#### 路由放置中间件
```
Router::get( '/user', 'App\Controller\IndexController@user', ['middleware' => [App\Middleware\Authentication::class]]);
```

#### 携带 Token
只需要在 Header 中携带令牌即可
```
Authorization: Bearer {token} 
```

#### 获取前当认证模型
通过中间件即可
```
$user = \HyperfSanctum\Manage::user();
```

#### 当前认证模型的Token
```
$user->tokenCurrent();
```

#### 删除当前Token (登出)
```
$user->tokenDelete();
// or
$user->tokenCurrent()->delete();
```

#### 获取模型所有Token
```
$user->tokens;
```

#### 删除所有Token (登出所有用户)
```
$user->tokenFlush();
// or
$user->tokens()->delete();
```

#### 同时只允许一人登陆
```
// 先撤销所有令牌
$user->tokenFlush();

// 颁发令牌
return $user->tokenCreate('web-token');
```

#### 验证当前用户权限
```
if($user->tokenCan('update')) {
    
}
```

#### 更新当前用户权限
```
$user->tokenAbilities(['update', 'delete']);
```

#### 获取当前Token名称
```
$name = $user->tokenName();
```

#### 验证Token名称
```
if($user->tokenNameHas('api-token')) {

}
```

#### 登陆验证用户
组件中自带帐号密码验证方式，只需要传对应字段即可
```
// 默认方式
$user = User::tokenLogin([
    'username' => 'user',
    'password' => '123456'
]);

// 附加字段，用户状态正常
$user = User::tokenLogin([
    'username' => 'user',
    'password' => '123456',
    'status'   => 1
]);

// 颁发令牌
if($user) {
    return $user->tokenCreate('web-token');
}
```

#### 自定义密码字段
默认密码字段为 password，自定义可在模型中重写 tokenLoginPassword 方法返回字段名
```
class User extends Model
{
    use \HyperfSanctum\Tokens;

    public function tokenLoginPassword()
    {
        return 'pass';
    }
}
```

#### 自定义密码验证方式
默认密码验证方式为 password_verify()，可在模型中覆盖验证方式即可
```
class User extends Model
{
    use \HyperfSanctum\Tokens;

    // input 为用户输入, origin 为原数据
    public function tokenLoginVerify($input, $origin)
    {
        return md5($input) == $origin;
    }
}
```

## 建议
#### 密码加密
```
User::create([
    'username' => 'user',
    'password' => password_hash('123456', PASSWORD_DEFAULT);
]);
```