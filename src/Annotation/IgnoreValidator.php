<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2024/01/22 22:40:11
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class IgnoreValidator extends AbstractAnnotation
{
    public function __construct()
    {

    }
}
