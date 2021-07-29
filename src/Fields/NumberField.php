<?php
/**
 * Валидатор поля число.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class NumberField extends Field
{
    public $type = ['string', 'numeric'];
    public $pattern = '/^\d+\.?\d*$/';
}
