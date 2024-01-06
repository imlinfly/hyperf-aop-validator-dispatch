<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/14 14:11:28
 * E-mail: fly@eyabc.cn
 */
declare(strict_types=1);

namespace Lynnfly\ValidatorDispatch;

use Lynnfly\ValidatorDispatch\Aspect\ValidatorDispatchAspect;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'aspects' => [
                ValidatorDispatchAspect::class,
            ],
            'listeners' => [
                Listener\ValidatorResolvedListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of ',
                    'source' => __DIR__ . '/../publish/validator.php',
                    'destination' => BASE_PATH . '/config/autoload/validator.php',
                ],
            ],
        ];
    }
}
