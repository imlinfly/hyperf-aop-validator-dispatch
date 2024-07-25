<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/15 15:28:15
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch;

/**
 * 表单数据获取器
 * Class FormGetter
 */
class FormGetter
{
    protected array $form = [];

    public function __construct(
        /** 表单数据 */
        protected array $data,
        /** 默认是否必须传参 */
        protected bool  $required = false,
        protected array $rules = [],
    )
    {

    }

    public static function make(array $data = [], bool $required = false, array $rules = []): static
    {
        return new static($data, $required, $rules);
    }

    /**
     * 子级数据
     * @param string $name
     * @param callable|null $callback
     * @param bool $required
     * @return $this
     */
    public function child(string $name, callable $callback = null, bool $required = false): static
    {
        $that = new static($this->data[$name] ?? [], $required);

        if ($callback) {

            if (isset($this->data[$name]) || $required) {
                $callback($that);
                $this->set($name, $that);
            }

            return $this;
        }

        return $that;
    }

    /**
     * 获取表单数据
     * @return array
     */
    public function toArray(): array
    {
        $converters = [
            'string' => 'strval',
            'int' => 'intval',
            'float' => 'floatval',
            'bool' => 'boolval',
        ];

        // 根据规则获取数据
        foreach ($this->rules as $rule) {
            [$type, $arguments] = $rule;
            [$name, $default, $required] = $arguments;

            if (!isset($this->data[$name])) {
                if ($required) {
                    $this->form[$name] = $default;
                }
                continue;
            }

            $value = $this->data[$name];

            if ($type === 'array') {
                $this->form[$name] = (array)$value;
            } else if ($type == 'mixed') {
                $this->form[$name] = $value;
            } else {
                $converter = $converters[$type] ?? 'strval';
                $this->form[$name] = $converter($value);
            }

            // 去除字符串两边的空格
            if ($type === 'string' && $arguments[3] ?? false) {
                $this->form[$name] = trim($this->form[$name]);
            }
        }

        return $this->form;
    }

    /**
     * 赋值表单数据
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set(string $name, mixed $value): static
    {
        if ($value instanceof FormGetter) {
            $value = $value->toArray();
        }

        $this->form[$name] = $value;
        return $this;
    }

    /**
     * 获取字符串参数值
     * @param string $name 参数名
     * @param string $default 默认值
     * @param bool $required 是否必须
     * @param bool $trim 是否去除两边空格
     * @return $this
     */
    public function string(
        string $name,
        string $default = '',
        bool   $required = false,
        bool   $trim = true,
    ): static
    {
        $this->rules[] = ['string', [$name, $default, $required, $trim]];
        return $this;
    }

    /**
     * 获取整数参数值
     * @param string $name 参数名
     * @param int $default 默认值
     * @param bool $required 是否必须
     * @return $this
     */
    public function int(string $name, int $default = 0, bool $required = false): static
    {
        $this->rules[] = ['int', [$name, $default, $required]];
        return $this;
    }

    /**
     * 获取数组参数值
     * @param string $name 参数名
     * @param array $default 默认值
     * @param bool $required 是否必须
     * @return $this
     */
    public function array(string $name, array $default = [], bool $required = false): static
    {
        $this->rules[] = ['array', [$name, $default, $required]];
        return $this;
    }

    /**
     * 获取浮点数参数值
     * @param string $name 参数名
     * @param float $default 默认值
     * @param bool $required 是否必须
     * @return $this
     */
    public function float(string $name, float $default = 0.0, bool $required = false): static
    {
        $this->rules[] = ['float', [$name, $default, $required]];
        return $this;
    }

    /**
     * 获取布尔值参数值
     * @param string $name 参数名
     * @param bool $default 默认值
     * @param bool $required 是否必须
     * @return $this
     */
    public function bool(string $name, bool $default = false, bool $required = false): static
    {
        $this->rules[] = ['bool', [$name, $default, $required]];
        return $this;
    }

    /**
     * 获取混合类型参数值
     * @param string $name 参数名
     * @param mixed $default 默认值
     * @param bool $required 是否必须
     * @return $this
     */
    public function mixed(string $name, mixed $default = '', bool $required = false): static
    {
        $this->rules[] = ['mixed', [$name, $default, $required]];
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * 设置默认是否必须传参
     * @param bool $required
     * @return static
     */
    public function required(bool $required): static
    {
        $this->required = $required;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
