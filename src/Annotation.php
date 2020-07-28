<?php

declare(strict_types=1);

namespace HyperfSanctum;

use Hyperf\Di\Annotation\AnnotationCollector;

use HyperfSanctum\Annotation\Can;
use HyperfSanctum\Annotation\CanOr;
use HyperfSanctum\Annotation\CanName;

// 注解处理器
class Annotation
{
    // 完整回调
    protected static $callback = [];

    // 收集标识
    protected static $over = false;

    // 获取注解
    public static function get($name)
    {
        // 只收集一次
        if(!static::$over)
        {
            static::annotation();

            static::$over = true;
        }

        return static::$callback[$name] ?? null;
    }

    // 收集注解
    protected static function annotation()
    {
        static::controller(Can::class, 'and');
        static::controller(Can::class, 'or');
        static::controller(CanName::class, 'name');

        static::method(Can::class, 'and');
        static::method(CanOr::class, 'or');
        static::method(CanName::class, 'name');
    }

    // 收集控制器类
    protected static function controller($class, $name)
    {
        $annotation = AnnotationCollector::getClassByAnnotation($class);

        foreach($annotation as $controller => $annotation)
        {
            // 组合数组
            if(!isset(static::$callback[$controller]))
            {
                static::$callback[$controller] = [];
            }

            static::$callback[$controller][$name] = (array) $annotation->name;
        }
    }

    // 收集方法类
    protected static function method($class, $name)
    {
        $annotation = AnnotationCollector::getMethodByAnnotation($class);

        foreach($annotation as $item)
        {
            // 取出完整名
            $callback = $item['class'] . '@' . $item['method'];

            // 继承控制级数组
            if(!isset(static::$callback[$callback]))
            {
                static::$callback[$callback] = static::$callback[$item['class']] ?? [];
            }

            // 空数据
            if(!isset(static::$callback[$callback][$name]))
            {
                static::$callback[$callback][$name] = [];
            }
            
            // 合并控制器数据
            static::$callback[$callback][$name] = array_merge(static::$callback[$callback][$name], (array) $item['annotation']->name);
        }
    }
}