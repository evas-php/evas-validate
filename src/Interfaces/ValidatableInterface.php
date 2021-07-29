<?php
/**
 * Интерфейс валидируемого объекта.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Interfaces;

interface ValidatableInterface
{
    /**
     * Проверка значения на валидность.
     * @param mixed значение
     * @return bool
     */
    public function isValid($value): bool;

    /**
     * Проверка значения на валидность с выбрасом исключения.
     * @param mixed значение
     * @throws ValidateException
     */
    public function throwIfNotValid($value);
}
