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
---

## 使用

#### 用户模型中加入 Trait
```
class User extends Model
{
    use \HyperfSanctum\Tokens;
}
```

#### 创建 Token
tokenCreate(名称, [权限名]) 会返回 token.
```
$user = User::find(1);
        
$user->tokenCreate('web-token');
$user->tokenCreate('api-token', ['update']);
```

#### 创建 Middleware
只需要继承 HyperfSanctum\Middleware 并实现 failedLogin 方法即可
```
class Authentication extends \HyperfSanctum\Middleware
{
    // 未登陆成功
    protected function failedLogin()
    {
        return $this->response->json([
            'code' => -1,
            'message' => 'failedLogin'
        ]);
    }
}
```

#### 路由放置中间件
```
Router::get( '/user', 'App\Controller\IndexController@user', ['middleware' => [App\Middleware\Authentication::class]]);

// or 注解

/**
 * @Middleware(\App\Middleware\Authentication::class)
 */
public function user()
```

#### 携带 Token
请求只需在 Header 中携带令牌即可
```
Authorization: Bearer {token} 
```

#### 获取前当认证模型
通过中间件的方法可使用以下方式获得当前登陆的用户模型
```
$user = \HyperfSanctum\Manage::user();
```
---
## 登陆
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

#### 推荐密码加密方式
```
User::create([
    'username' => 'user',
    'password' => password_hash('123456', PASSWORD_DEFAULT);
]);
```

---
## 操作令牌
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

---
## 权限验证
每个 token 都可以拥有不同权限和不同名，权限中如果包含 * 将意味着拥有所有权

#### 验证当前用户权限
```
// 满足权限
if($user->tokenCan('update'))

// 满足多个权限
if($user->tokenCan(['update', 'delete'])

// 满足其中一个
if($user->tokenCanOr(['update', 'delete'])
```
#### 获取当前用户权限
```
$user->tokenAbilities();
```
#### 更新当前用户权限
```
$user->tokenAbilities(['update', 'delete']);
```
#### 追加当前用户权限
```
$user->tokenAbilitiesAppend(['delete']);
```

#### 获取当前Token名称
```
$name = $user->tokenName();
```

#### 验证Token名称
```
if($user->tokenNameHas('api-token'))

// 满足其中一个
if($user->tokenNameHas(['api-token', 'admin-token']))
```

## 注解
注解只有在 **中间件通过** 的情况下生效，只能注在类或类方法上，如果类与类方法同时都有 **同名注解** 将会合并处理，可传字符或数组。

#### 权限验证
整个控制器都必须拥有 update 权限
```
use HyperfSanctum\Annotation\Can;

/**
 * @Middleware(\App\Middleware\Authentication::class)
 * @Can("update")
 */
class IndexController
```
都必须拥有 update 和 delete 权限，因合并了 class 的注解
```
/**
 * @Can("delete")
 */
public function delete()
```
同时满足多个权限，同样会合并 class 的注解，权限为 update delete list
```
/**
 * @Can({"delete", "list"})
 */
```
满足其中一个权限（注意，CanOr 不会与 Can 合并）
```
use HyperfSanctum\Annotation\CanOr;

/**
 * @CanOr({"delete", "create"})
 */
```
##### 进阶：叠加使用
```
/**
 * @Can({"update", "admin"})
 * @CanOr({"delete", "create"})
 */
```
+ 权限为 update，admin -> 通过
+ 权限为 delete -> 通过
+ 权限为 create -> 通过
+ 权限为 update -> 不通过
+ 权限为 admin -> 不通过

##### 高级：与类配合叠加使用
```
/**
 * @Middleware(\App\Middleware\Authentication::class)
 * @Can("admin")
 * @CanOr("boss")
 */
class AdminController
{
    public function index()

    /**
     * @Can({"create", "manage"})
     */
    public function create()

    /**
     * @Can("tongji")
     * @CanOr("guest")
     */
    public function tongji()
}
```
当访问 index 方法时：
+ 权限为 admin -> 通过
+ 权限为 boss -> 通过

当访问 create 方法时：
+ 权限为 admin -> 不通过
+ 权限为 admin, create -> 不通过
+ 权限为 admin, create, manage -> 通过
+ 权限为 boss -> 通过

当访问 tongji 方法时：
+ 权限为 tongji -> 不通过
+ 权限为 admin -> 不通过
+ 权限为 tongji, admin -> 通过
+ 权限为 boss -> 通过
+ 权限为 guest -> 通过

#### 名称验证
在创建 token 时会创建名称，用此来验证大局
```
use HyperfSanctum\Annotation\CanName;

/**
 * @Middleware(\App\Middleware\Authentication::class)
 * @CanName("api-token")
 */
class ApiController
```
满足其它中一名称
```
/**
 * @CanName({"api-token", "api"})
 */
```
类与方法合并注解
```
/**
 * @CanName({"api-token", "api"})
 */
class ApiController
{
    public function index()

    /**
     * @CanName("web-token")
     */
    public function web()
}
```
访问 index 时，名称为 api-token、api 其中一个即可。

访问 web 时，名称为 api-token、api、web-token 其中一个即可。

##### 进阶：权限验证与名称验证交叉
```
/**
 * @Middleware(\App\Middleware\Authentication::class)
 * @Can({"admin:index", "admin:create", "admin:tongji"})
 * @CanOr({"admin", "manage"})
 * @CanName({"boss", "landlady"})
 */
class AdminController
{
    public function index()

    /**
     * @Can("create")
     */
    public function create()

    /**
     * @Can("tongji")
     * @CanName("guest")
     */
    public function tongji()
}
```
当访问 index 方法时：
+ 权限为 admin:index -> 不通过
+ 权限为 admin:index, admin:create, admin:tongji -> 通过
+ 权限为 admin:index, admin -> 通过
+ 权限为 admin:index, manage -> 通过
+ 权限为 admin:index，名称为 boss -> 通过
+ 权限为 admin:index，名称为 landlady -> 通过

当访问 create 方法时：
+ 权限为 admin:index, admin:create, admin:tongji -> 不通过
+ 权限为 admin:index, admin:create, admin:tongji, create -> 通过
+ 权限为 admin -> 通过
+ 权限为 manage -> 通过
+ 权限为 admin:index，名称为 boss -> 通过
+ 权限为 admin:index，名称为 landlady -> 通过

当访问 tongji 方法时：
+ 权限为 admin:index, admin:create, admin:tongji, create -> 不通过
+ 权限为 admin:index, admin:create, admin:tongji, tongji -> 通过
+ 权限为 admin -> 通过
+ 权限为 manage -> 通过
+ 权限为 admin:create，名称为 boss -> 通过
+ 权限为 admin:create，名称为 landlady -> 通过
+ 权限为 admin:create，名称为 guest -> 通过

#### 中间件权限验证失败
当授权未成功时，将执行中间件的 unauthenticated 方法
```
class Authentication extends \HyperfSanctum\Middleware
{
    // 未登陆成功
    protected function failedLogin()
    {
        return $this->response->json([
            'code' => -1,
            'message' => 'failedLogin',
        ]);
    }
    
    // 未通过权限验证
    protected function unauthenticated()
    {
        return $this->response->json([
            'code' => -2,
            'message' => 'Unauthenticated',
        ]);
    }
}
```