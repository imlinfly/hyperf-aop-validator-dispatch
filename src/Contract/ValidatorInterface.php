<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/14 14:39:24
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch\Contract;

use Hyperf\Validation\Validator;

interface ValidatorInterface
{
    /**
     * 数据验证处理
     * @param mixed $value 验证的值
     * @param array $data 验证的表单数据
     * @param string $name 验证的字段
     * @param Validator $validator 验证器
     * @return bool|string
     */
    public function process(mixed $value, array $data, string $name, Validator $validator): bool|string;
}
