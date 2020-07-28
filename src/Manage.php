<?php

declare(strict_types=1);

namespace HyperfSanctum;

use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

class Manage
{
    // 获取当前上下文
    public static function user()
    {
        return Context::get('sanctum');
    }
    
    // 保存上下文
    public static function contextToken($token)
    {
        // 相关用户是否存在
        if($token->tokenable)
        {
            // 写入关联模型和协程中
            return Context::set('sanctum', $token->tokenable->tokenWith($token));
        }
    }
    
    // 从 request 中获取 token
    public static function requestToken(ServerRequestInterface $request)
    {
        // 头信息
        $header = $request->getHeaderLine('Authorization', '');

        if(strpos($header, 'Bearer ') === 0)
        {
            return substr($header, 7);
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

        return false;
    }
}