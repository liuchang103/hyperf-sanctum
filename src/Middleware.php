<?php

declare(strict_types=1);

namespace HyperfSanctum;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 检查 token 令牌
        if($token = Manage::requestToken($request))
        {
            // 开始认证
            if($this->authentication($token))
            {
                return $handler->handle($request); 
            }
        }
        
        // 未认证的
        return $this->unauthenticated();
    }
    
    // 开始验证
    protected function authentication($token)
    {
        // 获取令牌模型 并 检查相关模型存在
        if($token = Model::findToken($token))
        {
            // 保存用户到上下文中
            return Manage::contextToken($token);
        }
    }
    
    // 未通过验证
    protected function unauthenticated()
    {

    }
}