<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/18 18:39:18
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch\Contract;

abstract class AbstractValidator implements ValidatorInterface, ValidatorAliasInterface
{
    use ValidatorAlias;
}
