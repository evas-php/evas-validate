<?php
/**
 * @package evas-php\evas-validate
 */
namespace Evas\Validate;

/**
 * Класс ошибок валидации.
 * @author Egor Vasyakin <egor@evas-php.com>
 * @since 1.0
 */
class Errors
{
    /**
     * @param array маппинг ошибок
     */
    protected $errors = [];

    /**
     * Установка ошибки/ошибок.
     * @param string|array ключ или маппинг ошибок [ключ => сообщение]
     * @param string|null сообщение
     * @return self
     */
    public function set($key, string $value = null)
    {
        assert(is_string($key) || is_array($key));
        if (is_string($key)) {
            $this->errors[$key] = $value;
        } else foreach ($key as $subkey => $subvalue) {
            $this->set($subkey, $subvalue);
        }
        return $this;
    }

    // /**
    //  * Установка ошибки с подстановкой переменных объекта.
    //  */
    // public function setWithReplace(string $message, object &$obj)
    // {
    //     $message = preg_replace_callback(
    //         '/<(?<before>[^<]*):(?<key>[a-zA-Z]*)(?<after>[^<]*)>|:(?<key2>[a-zA-Z]*)/', 
    //         function ($matches) {
    //             extract($matches);
    //             if (empty($key)) $key = $key2;
    //             $value = $this->$key ?? null;
    //             if (empty($value)) {
    //                 $alias = static::ERROR_KEYS_ALIASES[$key] ?? null;
    //                 if (!$alias) {
    //                     return '';
    //                 }
    //                 $value = $this->$alias;
    //             }
    //             return $before . $value . $after;
    //         }, 
    //         $message);
    //     return $this->set($message);
    // }

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
     * @return string|false
     */
    public function first()
    {
        $error = reset($this->errors);
        return $error !== false ? $error : null;
    }

    /**
     * Получение последней ошибки.
     * @return string|false
     */
    public function last()
    {
        $error = end($this->errors);
        return $error !== false ? $error : null;
    }

    /**
     * Получение ключа текущей ошибки.
     * @return string|null
     */
    public function key()
    {
        return key($this->errors);
    }

    /**
     * Получение ключа первой ошибки.
     * @return string|null
     */
    public function firstKey()
    {
        $this->first();
        return $this->key();
    }

    /**
     * Получение ключа последней ошибки.
     * @return string|null
     */
    public function lastKey()
    {
        $this->last();
        return $this->key();
    }

    /**
     * Очистка ошибок.
     * @param null|array|string|numeric ключ или ключи
     * @return self
     */
    public function clear($key = null)
    {
        assert(is_null($key) || is_string($key) || is_numeric($key) || is_array($key));
        if (is_null($key)) {
            $this->errors = [];
        } else if (is_array($key)) foreach ($key as &$subkey) {
            $this->clear($subkey);
        } else {
            unset($this->errors[$key]);
        }
        return $this;
    }
}
