<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/14 14:39:24
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch\Contract;

interface ValidatorAliasInterface
{
    /**
     * 获取验证器别名
     * @param string $method 方法名，如果为空则返回类别名
     * @return string
     */
    public static function alias(string $method = ''): string;
}
