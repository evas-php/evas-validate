<?php
/**
 * Ошибки валидации набора полей.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate;

class Errors
{
    /** @param array маппинг ошибок */
    protected $errors = [];

    /**
     * Установка ошибки/ошибок.
     * @param string|array ключ или маппинг ошибок [ключ => сообщение]
     * @param string|null сообщение
     * @return self
     * @throws \InvalidArgumentException
     */
    public function set($key, string $value = null)
    {
        if (is_string($key)) {
            $this->errors[$key] = $value;
        } else if (is_array($key)) foreach ($key as $subkey => $subvalue) {
            $this->set($subkey, $subvalue);
        } else {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to %s() must be a string or an array, %s given', __METHOD__, gettype($key)));
        }
        return $this;
    }

    /**
     * Проверка наличия сообщений ошибок.
     * @return bool
     */
    public function has(): bool
    {
        return !empty($this->errors) ? true : false;
    }

    /**
     * Получение ошибок в виде маппинга.
     * @return array [ключ => сообщение]
     */
    public function map(): array
    {
        return $this->errors;
    }

    /**
     * Получение ошибок в виде списка.
     * @return array
     */
    public function list(): array
    {
        return array_values($this->errors);
    }

    /**
     * Получение ключей ошибок.
     * @return array|null
     */
    public function keys(): ?array
    {
        return array_keys($this->errors);
    }

    /**
     * Получение первой ошибки.
     * @return string|null
     */
    public function first(): ?string
    {
        $error = reset($this->errors);
        return $error !== false ? $error : null;
    }

    /**
     * Получение последней ошибки.
     * @return string|null
     */
    public function last(): ?string
    {
        $error = end($this->errors);
        return $error !== false ? $error : null;
    }

    /**
     * Получение ключа текущей ошибки.
     * @return string|null
     */
    public function key(): ?string
    {
        return key($this->errors);
    }

    /**
     * Получение ключа первой ошибки.
     * @return string|null
     */
    public function firstKey(): ?string
    {
        $this->first();
        return $this->key();
    }

    /**
     * Получение ключа последней ошибки.
     * @return string|null
     */
    public function lastKey(): ?string
    {
        $this->last();
        return $this->key();
    }

    /**
     * Очистка ошибок.
     * @param null|array|string|numeric ключ или ключи
     * @return self
     * @throws \InvalidArgumentException
     */
    public function clear($key = null)
    {
        if (is_null($key)) $this->errors = [];
        else if (is_string($key)) unset($this->errors[$key]);
        else if (is_array($key)) foreach ($key as &$subkey) 
            $this->clear($subkey);
        else throw new \InvalidArgumentException(sprintf('Argument 1 passed to %s() must be 
            a null or a string or an array, %s given', __METHOD__, gettype($key)));
        return $this;
    }
}
