<?php
/**
 * Тест сборщика шаблонизированных ошибок.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\tests;

use Codeception\Util\Autoload;
use Evas\Validate\ErrorBuilder;
use Evas\Validate\Field;
use Evas\Validate\Fieldset;
use Evas\Validate\Fields\EmailField;
use Evas\Validate\JsonFieldset;

Autoload::addNamespace('Evas\\Validate', 'vendor/evas-php/evas-validate/src');

class ErrorBuilderTest extends \Codeception\Test\Unit
{
    /**
     * Тест локальных шаблонов ошибок полей.
     */
    public function testLocalFieldTemplate()
    {
        $customLengthError = 'Email должен быть длиной от 8 до 60 символов';
        $customPatternError = 'Проверьте правильность Email';
        $field = new EmailField([
            'lengthError' => $customLengthError,
            'patternError' => $customPatternError,
        ]);
        $this->assertFalse($field->isValid('test'));
        $this->assertEquals($customLengthError, $field->error);

        $this->assertFalse($field->isValid('test@test.t'));
        $this->assertEquals($customPatternError, $field->error);
    }

    /**
     * Тест локальных шаблонов ошибок набора полей.
     */
    public function testLocalFieldsetTemplate()
    {
        $fieldset = new Fieldset(null, [
            'valuesTypeError' => 'valuesTypeCustomError',
        ]);
        $this->assertFalse($fieldset->isValid(null));
        $this->assertEquals('valuesTypeCustomError', $fieldset->errors()->last());
    }

    /**
     * Тест локальных шаблонов ошибок json набора полей.
     */
    public function testLocalJsonFieldsetTemplate()
    {
        $fieldset = new JsonFieldset(null, [
            'jsonEmptyError' => 'jsonEmptyCustomError',
            'jsonParseError' => 'jsonParseCustomError',
            'jsonTypeError' => 'jsonTypeCustomError',
        ]);
        $this->assertFalse($fieldset->isValid(null));
        $this->assertEquals('jsonEmptyCustomError', $fieldset->errors()->last());

        $this->assertFalse($fieldset->isValid('lol'));
        $this->assertEquals('jsonParseCustomError', $fieldset->errors()->last());

        $this->assertFalse($fieldset->isValid(['lol']));
        $this->assertEquals('jsonTypeCustomError', $fieldset->errors()->last());
    }

    /**
     * Тест изменения глобальных шаблонов ошибок.
     */
    public function testUpdateGlobalTemplate()
    {
        ErrorBuilder::templates();
        $defaultValues = include EVAS_VALIDATE_ERRORS_FILE;
        $ruFile = dirname(EVAS_VALIDATE_ERRORS_FILE) . '/errors_ru.php';
        $ruValues = include $ruFile;

        $actual = ErrorBuilder::templates();
        $this->assertEquals($defaultValues, $actual);

        ErrorBuilder::includeTemplates($ruFile);
        $actual = ErrorBuilder::templates();
        $this->assertEquals($ruValues, $actual);

        ErrorBuilder::includeTemplates(EVAS_VALIDATE_ERRORS_FILE);
        $actual = ErrorBuilder::templates();
        $this->assertEquals($defaultValues, $actual);
    }

    /**
     * Тест глобальных шаблонов ошибок.
     */
    public function testGlobalTemplate()
    {
        ErrorBuilder::templates();
        $ruFile = dirname(EVAS_VALIDATE_ERRORS_FILE) . '/errors_ru.php';

        ErrorBuilder::includeTemplates($ruFile);

        $expectedLengthError = 'Значение поля "Email" должно быть длиной от 8 до 60 символов';
        $expectedPatternError = 'Проверьте правильность поля "Email"';
        $field = new EmailField;
        $this->assertFalse($field->isValid('test'));
        $this->assertEquals($expectedLengthError, $field->error);

        $this->assertFalse($field->isValid('test@test.t'));
        $this->assertEquals($expectedPatternError, $field->error);

        // возвращаем исходные ошибки валидации
        ErrorBuilder::includeTemplates(EVAS_VALIDATE_ERRORS_FILE);
    }
}
