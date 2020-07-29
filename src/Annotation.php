<?php

declare(strict_types=1);

namespace HyperfSanctum;

use Hyperf\Di\Annotation\AnnotationCollector;

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
        static::controller(Annotation\Can::class);
        static::controller(Annotation\CanWhite::class);
        static::controller(Annotation\CanCard::class);

        static::method(Annotation\Can::class);
        static::method(Annotation\CanWhite::class);
        static::method(Annotation\CanCard::class);
    }

    // 收集控制器类
    protected static function controller($class)
    {
        $annotation = AnnotationCollector::getClassByAnnotation($class);

        foreach($annotation as $controller => $annotation)
        {
            // 组合数组
            if(!isset(static::$callback[$controller]))
            {
                static::$callback[$controller] = [];
            }

            static::$callback[$controller][$class] = (array) $annotation->name;
        }
    }

    // 收集方法类
    protected static function method($class)
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
            if(!isset(static::$callback[$callback][$class]))
            {
                static::$callback[$callback][$class] = [];
            }
            
            // 合并控制器数据
            static::$callback[$callback][$class] = array_merge(static::$callback[$callback][$class], (array) $item['annotation']->name);
        }
    }
}