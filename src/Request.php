<?php

declare(strict_types=1);

namespace HyperfSanctum;

use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ServerRequestInterface;

class Request
{
    protected $request;

    protected $route;

    // 载入请求
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;

        // 解析路由
        $this->route = new Route($request->getAttribute(Dispatched::class));
    }

    // 路由
    public function route()
    {
        return $this->route;
    }

    // 获取 token
    public function token()
    {
        // 头信息
        $header = $this->request->getHeaderLine('Authorization', '');

        if(strpos($header, 'Bearer ') === 0)
        {
            return substr($header, 7);
        }
    }
}