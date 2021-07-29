<?php
/**
 * Пришлось положить эту функцию в файл за пределы теста, 
 * потому что у php есть проблемы с переключением 
 * на глобальное пространство имён :(
 */

/**
 * Проверка значение на соответствие кастомному типу.
 * @see Evas\Validate\tests\FieldTest::testUndefinedType()
 * 
 * @param mixed значение
 * @return bool
 */
function is_amount($value): bool {
    if (!is_string($value) && !is_float($value)) return false;
    return preg_match('/^\d+\.\d{2}$/', (string) $value);
}
