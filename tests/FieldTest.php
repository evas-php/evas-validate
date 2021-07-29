<?php
/**
 * Тест валидатора поля.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\tests;

use Codeception\Util\Autoload;
use Evas\Validate\ErrorBuilder;
use Evas\Validate\Field;
use Evas\Validate\Fields\EmailField;
use Evas\Validate\ValidateException;

Autoload::addNamespace('Evas\\Validate', 'vendor/evas-php/evas-validate/src');

class FieldTest extends \Codeception\Test\Unit
{
    protected $field;

    protected function templateByType(string $type)
    {
        return ErrorBuilder::templateByType($type);
    }

    protected function makeEmailField()
    {
        return new Field([
            'label' => 'Email',
            'min' => 8,
            'max' => 60,
            'pattern' => '/^.{2,}@.{2,}\..{2,}$/',
        ]);
    }

    protected function makeRatingField()
    {
        return new Field([
            'label' => 'Rating',
            'type' => 'float',
            'min' => 0,
            'max' => 5,
        ]);
    }

    protected function makeWeekDaysField()
    {
        return new Field([
            'options' => ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        ]);
    }

    protected function makeDateField()
    {
        return new Field([
            'pattern' => '/^(\d{4})-(\d{2})-(\d{2})$/',
        ]);
    }

    protected function checkError(string $type, $value)
    {
        $this->assertFalse($this->field->isValid($value));
        // assert error
        // codecept_debug($this->field->error);
        $error = $this->templateByType($type);
        $this->assertEquals($error, $this->field->error);
        // assert value before
        $this->assertEquals($value, $this->field->valueBefore);
        // assert value
        $this->assertEquals($value, $this->field->value);
    }

    protected function _before()
    {
        $this->field = $this->makeEmailField();
    }

    public function testRequired()
    {
        $this->checkError('required', null);

        $this->field->required = false;
        $this->checkError('length', null);
        $this->field->required = true;
    }

    public function testLength()
    {
        $this->checkError('length', 'test');
        $this->checkError('length', str_repeat('t', 61));
    }

    public function testPattern()
    {
        $this->checkError('pattern', 'test@test');
        $this->checkError('pattern', 'test.test');
        $this->checkError('pattern', 't@test.test');
        $this->checkError('pattern', 'test@t.test');
        $this->checkError('pattern', 'test@test.t');
    }

    public function testSuccess()
    {
        $this->assertTrue($this->field->isValid('test@test.test'));
        $this->assertEmpty($this->field->error);
    }

    public function testDefault()
    {
        $this->checkError('required', null);
        $this->checkError('length', 'test');
        $this->checkError('pattern', 'test@test');

        $default = 'default@test.test';
        $this->field->default = $default;
        $this->assertTrue($this->field->isValid(null));
        $this->assertEquals($default, $this->field->value);
        $this->checkError('length', '');
    }



    public function testRange()
    {
        $this->field = $this->makeRatingField();
        $this->checkError('required', null);
        $this->checkError('range', -1);
        $this->checkError('range', 6);
        $this->assertTrue($this->field->isValid(4.93));
        $this->assertTrue($this->field->isValid('4.93'));
    }

    public function testType()
    {
        $this->field = $this->makeRatingField();
        $this->assertTrue($this->field->isValid(4.93));
        $this->assertTrue($this->field->isValid('4.93'));
        // включаем проверку типа
        $this->field->checkType = true;
        $this->assertTrue($this->field->isValid(4.93));
        $this->checkError('type', '4.93');
    }


    public function testOptions()
    {
        $this->field = $this->makeWeekDaysField();
        $this->checkError('required', null);
        $this->checkError('options', 'ru');
        $this->assertTrue($this->field->isValid('Пн'));
        $this->assertTrue($this->field->isValid('Вт'));
    }

    public function testMatches()
    {
        $this->field = $this->makeDateField();
        $this->checkError('required', null);
        $this->checkError('pattern', '2021');
        $this->assertTrue($this->field->isValid('2021-07-29'));
        $expected = ['2021-07-29', '2021', '07', '29'];
        codecept_debug($this->field->matches);
        $this->assertEquals($expected, $this->field->matches);
    }

    public function testOptionsSetting()
    {
        $this->field = new Field([
            'options' => 'invalid options',
        ]);
        $this->checkError('required', null);
        $this->checkError('optionsSetting', 'random value');
    }

    public function testUndefinedType()
    {
        // свой кастомный тип с методом проверки
        include_once __DIR__ . '/help/is_amount.php';
        $this->field = new Field([
            'type' => 'amount',
            'checkType' => true,
        ]);
        $this->checkError('required', null);
        $this->checkError('type', 'random');
        $this->assertTrue($this->field->isValid('99.95'));
        $this->assertTrue($this->field->isValid(99.95));
        
        // свой кастомный тип без метода проверки
        $this->field = new Field([
            'type' => 'unknown_type',
            'checkType' => true,
        ]);
        $this->checkError('required', null);
        $this->checkError('undefinedType', 'random');

    }

    public function testThrowException()
    {
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage($this->templateByType('length'));
        $this->field->throwIfNotValid('test');
    }
}
