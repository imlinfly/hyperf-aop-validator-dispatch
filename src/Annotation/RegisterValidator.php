<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/14 14:32:40
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Lynnfly\ValidatorDispatch\Contract\AbstractValidator;
use Lynnfly\ValidatorDispatch\Contract\ValidatorAliasInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RegisterValidator extends AbstractAnnotation
{
    /**
     * @param string|null $alias 规则别名 为空时在类上使用则使用类名，在方法上使用则使用方法名
     * @param string|null $message 验证失败提示信息
     */
    public function __construct(
        public ?string $alias = null,
        public ?string $message = null,
    )
    {
    }

    public function collectClass(string $className): void
    {
        static::collectRule($this->getAlias($className), $className);
    }

    public function collectMethod(string $className, ?string $target): void
    {
        static::collectRule($this->getAlias($className, $target), [$className, $target]);
    }

    protected function getAlias(string $className, ?string $target = ''): string
    {
        if ($this->alias !== null) {
            return $this->alias;
        }

        $target ??= '';

        if (is_subclass_of($className, ValidatorAliasInterface::class)) {
            return $className::alias($target);
        }

        return AbstractValidator::alias($className);
    }

    public function collectRule(string $alias, array|string $callback): void
    {
        $key = 'register_validator_rule';
        $rules = AnnotationCollector::get($key, []);
        $rules[] = [$callback, $alias, $this->message];

        AnnotationCollector::set($key, $rules);
    }

    public static function getRules(): array
    {
        return AnnotationCollector::get('register_validator_rule', []);
    }
}
