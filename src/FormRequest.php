<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/13 13:46:24
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch;

use Hyperf\Context\Context;
use Hyperf\Validation\Request\FormRequest as HyperfFormRequest;
use InvalidArgumentException;
use Lynnfly\ValidatorDispatch\Contract\ValidatorAlias;
use Lynnfly\ValidatorDispatch\Contract\ValidatorAliasInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

abstract class FormRequest extends HyperfFormRequest implements ValidatorAliasInterface
{
    use ValidatorAlias;

    protected string $dto;

    public function __construct(
        ContainerInterface        $container,
        protected ValidatorManage $validatorManage,
    )
    {
        parent::__construct($container);
    }

    /**
     * 获取验证数据
     * @return array
     */
    protected function validationData(): array
    {
        return Context::getOrSet(__METHOD__, function () {
            return $this->formData();
        });
    }

    /**
     * 获取表单数据，该方法配合validationData使用只会调用一次，之后会从上下文中获取
     * @return array
     */
    protected function formData(): array
    {
        return parent::validationData();
    }

    /**
     * 表单参数获取器
     * @param array|null $data 表单数据
     * @param bool $required 是否必须 如果必须但是参数值为空则返回默认值
     * @return FormGetter
     */
    protected function makeFormGetter(array $data = null, bool $required = false): FormGetter
    {
        $data ??= parent::validationData();
        return FormGetter::make($data, $required);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function dto(string $dto = null, array $data = null): object
    {
        if (!class_exists(AbstractDataTransferObject::class)) {
            throw new RuntimeException('Please install lynnfly/hyperf-dto to use this method.');
        }

        $dto ??= $this->dto ?? throw new InvalidArgumentException('dto parameter is required');

        $data ??= $this->validated();

        if (!is_subclass_of($dto, AbstractDataTransferObject::class)) {
            throw new InvalidArgumentException("{$dto} is not subclass of " . AbstractDataTransferObject::class);
        }

        return $dto::make($data);
    }
}
