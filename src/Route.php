<?php

declare(strict_types=1);

namespace HyperfSanctum;

use Hyperf\HttpServer\Router\Dispatched;

class Route
{
    protected $dispatched;

    protected $controller;

    protected $callback;

    // 载入请求
    public function __construct(Dispatched $dispatched)
    {
        $this->dispatched = $dispatched;

        // 解析回调
        if(!($this->dispatched->handler->callback instanceof \Closure))
        {
            // 开始解析
            $this->callbackHandle($this->dispatched->handler->callback);
        }
    }


    // 开始解析
    protected function callbackHandle($callback)
    {
        // 处理为数据
        if(!is_array($callback))
        {
            // 替换
            $callback = str_replace('::', '@', $callback);

            // 分割
            $callback = explode('@', $callback);
        }

        // 检测类名和方法存在
        if(isset($callback[0], $callback[1]))
        {
            $this->controller = $callback[0];
            $this->callback = $callback[0] . '@' . $callback[1];
        }
    }

    // 获取当前类
    public function controller()
    {
        return $this->controller;
    }

    // 获取当前完整回调
    public function callback()
    {
        return $this->callback;
    }
}