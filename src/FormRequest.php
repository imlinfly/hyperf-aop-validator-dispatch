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
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取验证数据
     * @return array
     */
    public function validationData(): array
    {
        $scene = $this->getScene();
        $key = __METHOD__ . '.' . ($scene ?? 'default');

        return Context::getOrSet($key, fn() => array_merge(
            $this->callValidatorMethod('', 'formData'),
            $this->callValidatorMethod($scene, 'formData')
        ));
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
     * 获取验证消息
     * @return array
     */
    public function messages(): array
    {
        return array_merge(
            $this->callValidatorMethod('common', 'messages'),
            $this->callValidatorMethod($this->getScene(), 'messages')
        );
    }

    /**
     * 获取验证属性
     * @return array
     */
    public function attributes(): array
    {
        return array_merge(
            $this->callValidatorMethod('common', 'attributes'),
            $this->callValidatorMethod($this->getScene(), 'attributes')
        );
    }

    /**
     * 获取验证规则
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            $this->callValidatorMethod('common', 'rules'),
            $this->callValidatorMethod($this->getScene(), 'rules')
        );
    }

    /**
     * 调用验证器方法
     * @param string|null $prefix 前缀
     * @param string $function 方法名
     * @return array
     */
    protected function callValidatorMethod(?string $prefix, string $function): array
    {
        if (is_null($prefix)) {
            return [];
        }

        if ($prefix === '') {
            $method = $function;
        } else {
            $method = $prefix . ucfirst($function);
        }

        return method_exists($this, $method) ? call_user_func([$this, $method]) : [];
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

    /**
     * 表单数据获取器
     * @param array|null $data 表单数据
     * @return DataGetter
     */
    protected function makeDataGetter(array $data = null): DataGetter
    {
        $data ??= parent::validationData();
        return DataGetter::make($data);
    }

    /**
     * 获取数据传输对象
     * @param string|null $dto 数据传输对象类名
     * @param array|null $data 表单数据
     * @return object
     */
    public function dto(string $dto = null, array $data = null): object
    {
        $class = '\Lynnfly\HyperfDto\AbstractDataTransferObject';

        if (!class_exists($class)) {
            throw new RuntimeException('Please install lynnfly/hyperf-dto to use this method.');
        }

        $dto ??= $this->dto ?? throw new InvalidArgumentException('dto parameter is required');

        $data ??= $this->validated();

        if (!is_subclass_of($dto, $class)) {
            throw new InvalidArgumentException("{$dto} is not subclass of " . $class);
        }

        /** @var object $dto */
        return $dto::make($data);
    }
}
