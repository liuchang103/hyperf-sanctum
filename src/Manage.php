<?php

declare(strict_types=1);

namespace HyperfSanctum;

use Hyperf\Utils\Context;

class Manage
{
    // 获取当前上下文
    public static function user()
    {
        return Context::get('sanctum');
    }

    // 认证
    public static function auth($token)
    {
        // 获取令牌模型 并 检查相关模型存在
        if($token = Model::findToken($token))
        {
            // 相关用户是否存在
            if($token->tokenable)
            {
                // 写入关联模型和协程中
                return Context::set('sanctum', $token->tokenable->tokenWith($token));
            }
        }
    }

    // 登陆验证
    public static function login($model, array $data)
    {
        $where = [];

        foreach($data as $name => $value)
        {
            // 不加入条件字段
            if($name <> $model->tokenLoginPassword())
            {
                $where[] = [$name, $value];
            }
        }

        // 模型查询
        if($model = $model->where($where)->first())
        {
            // 验证密码
            if($model->tokenLoginVerify($data[$model->tokenLoginPassword()], $model->{$model->tokenLoginPassword()}))
            {
                return $model;
            }
        }
    }

    // 注解权限认证
    public static function annotation(Route $route)
    {
        // Token模型
        $token = static::user()->tokenCurrent();

        // 从注解收集器中取中
        $data = Annotation::get($route->callback()) ?? Annotation::get($route->controller());

        // 没有注解，放行
        if(!$data) return true;

        // 验证
        return $token->canAnd($data['and'] ?? []) ||
            $token->canOr($data['or'] ?? []) ||
            $token->nameOr($data['name'] ?? []);
    }
}