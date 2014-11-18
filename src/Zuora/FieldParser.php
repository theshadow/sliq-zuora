<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 11/14/14
 * Time: 2:26 PM
 */

namespace SliQ\Zuora;

use SliQ\Field;
use SliQ\Statement\FieldParserInterface;

/**
 * Class FieldParser
 * @package SliQ\Zuora
 */
class FieldParser implements FieldParserInterface
{
    /**
     * @var array
     */
    public static $OPERANDS = array(
        Field::OP_EQUAL_TO => '=',
        Field::OP_NOT_EQUAL_TO => '<>',
        Field::OP_GREATER_THAN => '>',
        Field::OP_GREATER_THAN_EQUAL_TO => '>=',
        Field::OP_LESS_THAN => '<',
        Field::OP_LESS_THAN_EQUAL_TO => '<=',
        Field::OP_LIKE => null,
        Field::OP_NOT_LIKE => null,
    );

    /**
     * @param Field $field
     * @return string
     * @throws \LogicException
     */
    public function parse(Field $field)
    {
        if (in_array($field->getOperand(), array(Field::OP_IN, Field::OP_NOT_IN))) {
            return $this->parseIn($field);
        } elseif (in_array($field->getOperand(), array(Field::OP_BETWEEN, Field::OP_NOT_BETWEEN))) {
            return $this->parseBetween($field);
        } elseif (in_array($field->getOperand(), array(Field::OP_LIKE, Field::OP_NOT_LIKE))) {
            throw new \LogicException("The Zuora field parser does not support the LIKE or NOT LIKE operand for field: " . $field->getName(), 1);
        }

        return $this->parseDefault($field);
    }

    /**
     * @param Field $field
     * @return string
     * @throws \InvalidArgumentException
     */
    public function parseBetween(Field $field)
    {
        if (!is_array($field->getValues())) {
            throw new \InvalidArgumentException("Field '" . $field->getName() . "' value must be of type array when using BETWEEN", 1);
        }

        list($x, $y) = $this->parseValues($field->getValues());

        return sprintf(
            "%s %s %s AND %s %s %s",
            $field->getName(),
            static::$OPERANDS[Field::OP_GREATER_THAN],
            $x,
            $field->getName(),
            static::$OPERANDS[Field::OP_LESS_THAN],
            $y
        );
    }

    /**
     * @param Field $field
     * @return string
     * @throws \InvalidArgumentException
     */
    public function parseDefault(Field $field)
    {
        if (is_array($field->getValues())) {
            throw new \InvalidArgumentException("Value must be a basic non-array type received: " . var_export($field->getValues(), TRUE), 1);
        }

        $value = $this->parseValues($field->getValues());

        return sprintf("%s %s %s", $field->getName(), static::$OPERANDS[$field->getOperand()], $value);
    }

    /**
     * @param Field $field
     * @return string
     */
    public function parseIn(Field $field)
    {

        $values = $this->parseValues($field->getValues());

        $in = array_reduce($values, function ($carry, $value) use ($field) {
            /** @var Field $field */
            $carry[] = "{$field->getName()} " . static::$OPERANDS[Field::OP_EQUAL_TO] . " {$value}";
        }, array());

        return implode(' OR ', $in);
    }

    /**
     * @param $values
     * @return array|string
     */
    public function parseValues($values)
    {
        if (!is_array($values)) {
            return $this->parseType($values);
        }

        $values = array_map(function ($value) use ($this) {
            $value = urlencode($this->parseType($value));
            return $value;
        }, $values);

        return $values;
    }

    /**
     * @param $value
     * @return string
     */
    public function parseType($value)
    {
        if (is_string($value)) {
            return "'{$value}'";
        }

        return $value;
    }
}
