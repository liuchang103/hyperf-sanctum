<?php

declare(strict_types=1);

namespace HyperfSanctum\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class CanName extends AbstractAnnotation
{
    public $name;

    public function __construct($value = null)
    {
        $this->bindMainProperty('name', $value);
    }
}