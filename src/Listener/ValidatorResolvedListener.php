<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/14 14:22:09
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Lynnfly\ValidatorDispatch\Annotation\RegisterValidator;
use Lynnfly\ValidatorDispatch\ValidatorManage;

class ValidatorResolvedListener implements ListenerInterface
{

    public function __construct(
        protected ValidatorManage $validatorManage
    )
    {
    }

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /**  @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        $rules = RegisterValidator::getRules();

        foreach ($rules as $params) {
            [$callback, $alias, $message] = $params;
            // 注册验证器
            $this->validatorManage->register($validatorFactory, $callback, $alias, $message);
        }
    }
}
