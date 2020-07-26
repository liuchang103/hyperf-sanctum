<?php

declare(strict_types=1);

namespace HyperfSanctum;

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
        return $this->tokenCurrent()->setAbilities($abilities);
    }

    // token 创建
    public function tokenCreate(string $name, array $abilities = ['*'])
    {
        $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $token = Manage::buildToken()),
            'abilities' => $abilities,
        ]);
        
        return $token;
    }

    // 当前 token
    public function tokenCurrent()
    {
        return $this->token;
    }

    // 写入 token
    public function TokenWith($token)
    {
        $this->token = $token;

        return $this;
    }
}