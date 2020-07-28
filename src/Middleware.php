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
        // 放入请求
        $requestManage = new Request($request);

        // 检查 token 令牌
        if($token = $requestManage->token())
        {
            // 开始认证
            if(Manage::auth($token))
            {
                // 注解权限检查
                if(!Manage::annotation($requestManage->route()))
                {
                    return $this->unauthenticated();
                }

                return $handler->handle($request); 
            }
        }
        
        // 未登陆
        return $this->failedLogin();
    }

    // 登陆失败
    protected function failedLogin()
    {

    }
    
    // 无权限
    protected function unauthenticated()
    {

    }
}