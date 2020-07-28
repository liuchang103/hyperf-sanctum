<?php

declare(strict_types=1);

namespace HyperfSanctum;

use Hyperf\Utils\Str;

trait Tokens
{
    // 当前 token 模型
    protected $token;
    
    // 多态关联
    public function tokens()
    {
        return $this->morphMany(Model::class, 'tokenable');
    }

    // 查询权限
    public function tokenCan(string $ability)
    {
        return $this->tokenCurrent() ? $this->tokenCurrent()->can($ability) : false;
    }
    
    // 更新 token 权限
    public function tokenAbilities(array $abilities = ['*'])
    {
        return $this->tokenCurrent() ? $this->tokenCurrent()->setAbilities($abilities) : false;
    }

    // token 创建
    public function tokenCreate(string $name, array $abilities = ['*'])
    {
        $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $token = Str::random(80)),
            'abilities' => $abilities,
        ]);
        
        return $token;
    }

    // 删除 token
    public function tokenDelete()
    {
        return $this->tokenCurrent() ? $this->tokenCurrent()->delete() : false;
    }

    // 清空所有 token
    public function tokenFlush()
    {
        return $this->tokens()->delete();
    }

    // 当前 token name
    public function tokenName()
    {
        return $this->tokenCurrent() ? $this->tokenCurrent()->name : null;
    }

    // 判断 token name
    public function tokenNameHas($name)
    {
        return $this->tokenName() == $name;
    }

    // 当前 token
    public function tokenCurrent()
    {
        return $this->token;
    }

    // 写入 token
    public function tokenWith($token)
    {
        $this->token = $token;

        return $this;
    }

    // 登陆验证
    public static function tokenLogin(array $data)
    {
        return Manage::login(new static, $data);
    }

    // 验证字段
    public function tokenLoginPassword()
    {
        return 'password';
    }

    // 验证方式
    public function tokenLoginVerify($input, $origin)
    {
        return password_verify($input, $origin);
    }
}