<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/18 18:39:18
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch\Contract;

trait ValidatorAlias
{
    /**
     * 获取验证器别名
     * @param string $method
     * @return string
     */
    public static function alias(string $method = ''): string
    {
        $class = static::class;

        if ($method !== '') {
            return $class . '@' . $method;
        }

        return $class;
    }
}
