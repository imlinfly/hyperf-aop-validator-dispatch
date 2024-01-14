<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2024/01/11 11:30:03
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch;

class DataGetter
{
    /**
     * 空值返回默认值，如果获取到的值为字符串空值，则返回默认值
     * @var bool
     */
    public bool $emptyReturnDefault = true;

    public function __construct(
        protected array $data = [],
    )
    {
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    /**
     * 获取string类型的值
     * @param string $key
     * @param mixed|null $default
     * @param string $filter
     * @param bool $trim
     * @return string|null
     */
    public function string(string $key, ?string $default = '', string $filter = '', bool $trim = false): ?string
    {
        if ($trim) {
            $filter = 'trim|' . $filter;
        }

        return $this->getTypedValue('string', $key, $default, $filter);
    }

    /**
     * 获取string类型的值，不存在返回null
     * @param string $key
     * @param string $filter
     * @param bool $trim
     * @return string|null
     */
    public function stringOrNull(string $key, string $filter = '', bool $trim = false): ?string
    {
        return $this->string($key, null, $filter, $trim);
    }

    /**
     * 获取int类型的值
     * @param string $key
     * @param int|null $default
     * @param string $filter
     * @return int|null
     */
    public function int(string $key, ?int $default = 0, string $filter = ''): ?int
    {
        return $this->getTypedValue('int', $key, $default, $filter);
    }

    /**
     * 获取int类型的值，不存在返回null
     * @param string $key
     * @param string $filter
     * @return int|null
     */
    public function intOrNull(string $key, string $filter = ''): ?int
    {
        return $this->int($key, null, $filter);
    }

    /**
     * 获取bool类型的值
     * @param string $key
     * @param bool|null $default
     * @param string $filter
     * @return bool|null
     */
    public function bool(string $key, ?bool $default = false, string $filter = ''): ?bool
    {
        return $this->getTypedValue('bool', $key, $default, $filter);
    }

    /**
     * 获取bool类型的值，不存在返回null
     * @param string $key
     * @param string $filter
     * @return bool|null
     */
    public function boolOrNull(string $key, string $filter = ''): ?bool
    {
        return $this->bool($key, null, $filter);
    }

    /**
     * 获取array类型的值
     * @param string $key
     * @param array|null $default
     * @param string $filter
     * @return array|null
     */
    public function array(string $key, ?array $default = [], string $filter = ''): ?array
    {
        return $this->getTypedValue('array', $key, $default, $filter);
    }

    /**
     * 获取array类型的值，不存在返回null
     * @param string $key
     * @param string $filter
     * @return array|null
     */
    public function arrayOrNull(string $key, string $filter = ''): ?array
    {
        return $this->array($key, null, $filter);
    }

    /**
     * 获取object类型的值
     * @param string $key
     * @param object|null $default
     * @param string $filter
     * @return object|null
     */
    public function object(string $key, ?object $default = null, string $filter = ''): ?object
    {
        return $this->getTypedValue('object', $key, $default, $filter);
    }

    /**
     * 获取float类型的值
     * @param string $key
     * @param float|null $default
     * @param string $filter
     * @return float|null
     */
    public function float(string $key, ?float $default = 0, string $filter = ''): ?float
    {
        return $this->getTypedValue('float', $key, $default, $filter);
    }

    /**
     * 获取float类型的值，不存在返回null
     * @param string $key
     * @param string $filter
     * @return float|null
     */
    public function floatOrNull(string $key, string $filter = ''): ?float
    {
        return $this->float($key, null, $filter);
    }

    /**
     * 获取值
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        return \Hyperf\Collection\data_get($this->data, $key, $default);
    }

    /**
     * 获取强类型值
     * @param string $type
     * @param string $key
     * @param mixed $default
     * @param string $filter
     * @return mixed
     */
    public function getTypedValue(string $type, string $key, mixed $default, string $filter = ''): mixed
    {
        $value = $this->getValue($key, $default);

        // 如果值为默认值或者不是字符串类型且值为空，则直接返回默认值
        if ($value === $default || ($this->emptyReturnDefault && $type !== 'string' && $value === '')) {
            return $default;
        }

        $value = match ($type) {
            'string' => (string)$value,
            'int' => (int)$value,
            'bool' => (bool)$value,
            'array' => (array)$value,
            'object' => (object)$value,
            'float' => (float)$value,
            default => $value,
        };

        if ($filter) {
            return $this->filterValue($value, $filter);
        }

        return $value;
    }

    /**
     * 过滤值
     * @param mixed|null $value
     * @param string $filter
     * @return mixed
     */
    public function filterValue(mixed $value = null, string $filter = ''): mixed
    {
        if ($filter) {
            $value = filter_var($value, FILTER_CALLBACK, ['options' => $filter]);
        }

        return $value;
    }

    /**
     * 获取空值返回默认值
     * @return bool
     */
    public function isEmptyReturnDefault(): bool
    {
        return $this->emptyReturnDefault;
    }

    /**
     * 设置空值返回默认值
     * @param bool $emptyReturnDefault
     * @return $this
     */
    public function setEmptyReturnDefault(bool $emptyReturnDefault): static
    {
        $this->emptyReturnDefault = $emptyReturnDefault;
        return $this;
    }
}
