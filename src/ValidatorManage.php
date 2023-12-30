<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/15 15:27:29
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Validator;
use InvalidArgumentException;
use Lynnfly\ValidatorDispatch\Contract\ValidatorAliasInterface;
use Lynnfly\ValidatorDispatch\Contract\ValidatorInterface;
use Psr\Container\ContainerInterface;

class ValidatorManage
{
    public function __construct(
        protected ContainerInterface $container
    )
    {
    }

    public function register(ValidatorFactoryInterface $validatorFactory, string|array $instance, string $rule, ?string $message)
    {
        if (is_array($instance)) {
            [$class, $method] = $instance;
            $instance = $this->container->get($class);
            $instance = [$instance, $method];
        } else {
            $instance = $this->container->get($instance);
        }

        if ($instance instanceof ValidatorInterface) {
            $instance = [$instance, 'process'];
        } else if (!is_callable($instance)) {
            throw new InvalidArgumentException('Validator must be implement ' . ValidatorInterface::class . ' or ' . ValidatorAliasInterface::class);
        }

        $callback = function (string $attribute, mixed $value, array $parameters, Validator $validator) use ($instance) {
            $parameters = $validator->getData();

            $result = $instance($value, $parameters, $attribute, $validator);

            // 如果返回值为bool类型，则直接返回
            if (is_bool($result)) {
                return $result;
            }
            // 如果返回值为字符串，则表示验证失败，添加错误信息
            $validator->getMessageBag()->add($attribute, $result);
            return false;
        };

        // 注册验证器
        $validatorFactory->extend($rule, $callback, $message);
    }
}
