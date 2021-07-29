<?php
/**
 * Тест сборщика шаблонизированных ошибок.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\tests;

use Codeception\Util\Autoload;
use Evas\Validate\Field;
use Evas\Validate\Fields\EmailField;
use Evas\Validate\ErrorBuilder;

Autoload::addNamespace('Evas\\Validate', 'vendor/evas-php/evas-validate/src');

class ErrorBuilderTest extends \Codeception\Test\Unit
{
    public function testLocalTemplate()
    {
        $field = new EmailField([
            'lengthError' => 'Email должен быть длиной< от :min>< до :max> символов',
            'patternError' => 'Проверьте правильность Email',
        ]);
        $expectedLengthError = 'Email должен быть длиной от 8 до 60 символов';
        $this->assertFalse($field->isValid('test'));
        $this->assertEquals($expectedLengthError, $field->error);

        $expectedPatternError = 'Проверьте правильность Email';
        $this->assertFalse($field->isValid('test@test.t'));
        $this->assertEquals($expectedPatternError, $field->error);
    }

    public function testGlobalTemplate()
    {
        $defaultFile = EVAS_VALIDATE_ERRORS_FILE;
        $defaultValues = include $defaultFile;
        $ruFile = dirname($defaultFile) . '/errors_ru.php';
        $ruValues = include $ruFile;

        $actual = ErrorBuilder::templates();
        $this->assertEquals($defaultValues, $actual);

        ErrorBuilder::includeTemplates($ruFile);
        $actual = ErrorBuilder::templates();
        $this->assertEquals($ruValues, $actual);

        ErrorBuilder::includeTemplates($defaultFile);
        $actual = ErrorBuilder::templates();
        $this->assertEquals($defaultValues, $actual);
    }
}
