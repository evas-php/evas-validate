<?php
/**
 * Тест валидатора набора полей.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\tests;

use Codeception\Util\Autoload;
use Evas\Validate\Field;
use Evas\Validate\Fieldset;
use Evas\Validate\Fields\EmailField;
use Evas\Validate\ValidateErrorBuilder;

Autoload::addNamespace('Evas\\Validate', 'vendor/evas-php/evas-validate/src');

class FieldsetTest extends \Codeception\Test\Unit
{
    public $fieldset;

    protected function templateByType(string $type)
    {
        return ValidateErrorBuilder::templateByType($type);
    }

    protected function makeRegistrationFieldset()
    {
        return new Fieldset([
            'email' => new EmailField,
            'password' => new Field([
                'min' => 6,
                'max' => 30,
                'same' => 'password_repeat',
                'sameLabel' => 'Password Repeat',
            ]),
        ]);
    }

    protected function checkValues(array $values)
    {
        // assert value before
        $this->assertEquals($values, $this->fieldset->valuesBefore);
    }

    protected function checkError(string $key, string $type, $values)
    {
        $this->assertFalse($this->fieldset->isValid($values));
        // assert error
        $error = $this->templateByType($type);
        $this->assertEquals($error, $this->fieldset->errors()->last());
        $this->assertEquals($key, $this->fieldset->errors()->lastKey());
        $this->checkValues($values);
    }

    protected function checkErrors(array $types, $values)
    {
        $this->assertFalse($this->fieldset->isValid($values, true));
        // assert error
        $errors = [];
        foreach ($types as $name => $type) {
            $errors[$name] = $this->templateByType($type);
        }
        $actualErrors = $this->fieldset->errors();
        // codecept_debug($this->fieldset->errors()->map());
        $this->assertEquals($errors, $actualErrors->map());
        $this->assertEquals(array_values($errors), $actualErrors->list());
        $this->checkValues($values);
    }

    protected function _before()
    {
        $this->fieldset = $this->makeRegistrationFieldset();
    }

    public function testSame()
    {
        $data = ['email' => 'test@test.test', 'password' => '1234567'];
        $this->checkError('password', 'same', $data);

        $data['password_repeat'] = '123';
        $this->checkError('password', 'same', $data);

        $data['password_repeat'] = $data['password'];
        $this->assertTrue($this->fieldset->isValid($data));
    }

    public function testSingleError()
    {
        $invalidData = ['email' => 'test@test.t', 'password' => '1234'];
        $expected = [
            'email' => $this->templateByType('pattern'),
        ];
        $this->assertFalse($this->fieldset->isValid($invalidData));
        $actual = $this->fieldset->errors();
        $this->assertEquals($expected, $actual->map());
        $this->assertEquals(array_values($expected), $actual->list());
        $this->assertEquals(array_keys($expected), $actual->keys());
    }

    public function testMultipleErrors()
    {
        $invalidData = ['email' => 'test@test.t', 'password' => '1234'];
        $expected = [
            'email' => $this->templateByType('pattern'),
            'password' => $this->templateByType('length'),
        ];
        $this->assertFalse($this->fieldset->isValid($invalidData, true));
        $actual = $this->fieldset->errors();
        $this->assertEquals($expected, $actual->map());
        $this->assertEquals(array_values($expected), $actual->list());
        $this->assertEquals(array_keys($expected), $actual->keys());
    }
}
