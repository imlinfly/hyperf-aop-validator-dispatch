<?php
return [
    'aspect' => [
        // 验证器切面类列表
        'classes' => [
            'App\*\Controller\*',
        ],
        // 验证器切面类优先级
        'priority' => -100,
        // 验证器切面类注解列表
        'annotations' => [
        ],
    ],

];
