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
        
$user->tokenCreate('user-token');
$user->tokenCreate('user-level-token', ['update']);
```

#### 创建 Middleware
只需要继承 HyperfSanctum\Middleware 并实现 unauthenticated 方法即可
```
class Authentication extends \HyperfSanctum\Middleware
{
    // 未通过验证
    protected function unauthenticated()
    {
        return $this->response->json(
            [
                'code' => -1,
                'message' => 'Unauthenticated',
            ]
        );
    }
}
```

#### 携带 Token
只需要在 Header 中携带令牌即可
```
Authorization: Bearer {token} 
```

#### 获取前当认证模型
```
$user = \HyperfSanctum\Manage::user();
```

#### 删除当前Token (登出)
```
$user->tokenCurrent()->delete();
```

#### 获取所有Token
```
$user->tokens();
```

#### 删除所有Token (登出所有用户)
```
$user->tokens()->delete();
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

## 建议
#### 密码加密
```
User::create([
    'username' => 'user',
    'password' => password_hash('123456', PASSWORD_DEFAULT);
]);
```

#### 登陆验证 并 颁发令牌
```
$username = 'user';
$password = '123456';

$user = User::where('username', $username)->first();

// 验证密码
if($user && password_verify($password, $user->password))
{
    // 颁发令牌
    return $user->tokenCreate('user');
}
```