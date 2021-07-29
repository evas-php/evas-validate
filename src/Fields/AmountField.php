<?php
/**
 * Валидатор поля денежная сумма.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class AmountField extends Field
{
    public $type = ['string', 'float'];
    public $pattern = '/^\d+\.\d{2}$/';
}
