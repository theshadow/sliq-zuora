<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 2/1/15
 * Time: 8:29 AM
 */

namespace SliQ\Zuora;

use \PHPUnit_Framework_TestCase;
use Mockery as m;

use SliQ\Field;

/**
 * Class FieldParserTest
 * @package SliQ\Zuora
 */
class FieldParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that the parseType method correctly translates strings in to quoted strings.
     */
    public function testThatParseTypeCorrectlyTranslatesToQuotedString()
    {
        $value = uniqid();

        $fieldParser = new FieldParser();

        $actual = $fieldParser->parseType($value);

        $this->assertTrue(is_string($value));
        $this->assertEquals("'{$value}'", $actual);
    }

    /**
     * Test that the parseType method correctly translates floats
     */
    public function testThatParseTypeCorrectlyTranslatesFloat()
    {
        $value = (float)(mt_rand() / mt_getrandmax());

        $fieldParser = new FieldParser();

        $actual = $fieldParser->parseType($value);

        $this->assertTrue(is_float($actual));
        $this->assertEquals($value, $actual);
    }

    /**
     * Test that the parseType method correctly translates integers
     */
    public function testThatParseTypeCorrectlyTranslatesInt()
    {
        $value = mt_rand();

        $fieldParser = new FieldParser();

        $actual = $fieldParser->parseType($value);

        $this->assertTrue(is_int($actual));
        $this->assertEquals($value, $actual);
    }

    /**
     * Test that the parseType method correctly translates booleans
     */
    public function testThatParseTypeCorrectlyTranslatesBooleans()
    {
        $value = (bool)mt_rand(0, 1);

        $fieldParser = new FieldParser();

        $actual = $fieldParser->parseType($value);

        $this->assertTrue(is_int($actual));
        $this->assertEquals((int)$value, $actual);
    }

    /**
     * Test that parseValues correctly parses single value
     */
    public function testThatParseValuesCorrectlyParsesSingleValue()
    {
        $values = mt_rand();

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseType]');
        $fieldParser->shouldReceive('parseType')
            ->once()
            ->with($values)
            ->andReturn($values);

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parseValues($values);

        $this->assertTrue(is_int($actual));
        $this->assertEquals($values, $actual);
    }

    /**
     * Test that parseValues correctly parses multiple values
     */
    public function testThatParseValuesCorrectlyParsesMultipleValues()
    {
        $values = array(
            mt_rand(),
            mt_rand(),
        );

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseType]');
        $fieldParser->shouldReceive('parseType')
            ->ordered('parseType')
            ->with($values[0])
            ->andReturn($values[0]);

        $fieldParser->shouldReceive('parseType')
            ->ordered('parseType')
            ->with($values[1])
            ->andReturn($values[1]);

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parseValues($values);

        $this->assertTrue(is_array($actual));
        $this->assertEquals($values, $actual);
    }

    /**
     * Test that the parseIn method properly translates IN
     */
    public function testThatParseInProperlyReturnsQueryStringPart()
    {
        $values = array(
            mt_rand(),
            mt_rand(),
        );

        $fieldName = uniqid();

        $field = m::mock('SliQ\Field[getValues, getName, getOperand]');
        $field->shouldReceive('getValues')
            ->andReturn($values);
        $field->shouldReceive('getName')
            ->andReturn($fieldName);
        $field->shouldReceive('getOperand')
            ->andReturn(Field::OP_IN);

        $expected = "{$fieldName} " . FieldParser::$OPERANDS[Field::OP_EQUAL_TO] . " {$values[0]}"
            . " OR {$fieldName} " . FieldParser::$OPERANDS[Field::OP_EQUAL_TO] . " {$values[1]}";

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseValues]');
        $fieldParser->shouldReceive('parseValues')
            ->with($values)
            ->andReturn($values);

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parseIn($field);

        $this->assertTrue(is_string($actual));
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the parseIn properly translates NOT IN
     */
    public function testThatParseNotInProperlyReturnsQueryStringPart()
    {
        $values = array(
            mt_rand(),
            mt_rand(),
        );

        $fieldName = uniqid();

        $field = m::mock('SliQ\Field[getValues, getName, getOperand]');
        $field->shouldReceive('getValues')
            ->andReturn($values);
        $field->shouldReceive('getName')
            ->andReturn($fieldName);
        $field->shouldReceive('getOperand')
            ->andReturn(Field::OP_NOT_IN);

        $expected = "{$fieldName} " . FieldParser::$OPERANDS[Field::OP_NOT_EQUAL_TO] . " {$values[0]}"
            . " AND {$fieldName} " . FieldParser::$OPERANDS[Field::OP_NOT_EQUAL_TO] . " {$values[1]}";

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseValues]');
        $fieldParser->shouldReceive('parseValues')
            ->with($values)
            ->andReturn($values);

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parseIn($field);

        $this->assertTrue(is_string($actual));
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the parseDefault properly translates to a string
     *
     * @dataProvider defaultOperandDataProvider
     */
    public function testThatParseDefaultProperlyTranslatesToStrings($operand)
    {
        $values = mt_rand();

        $name = uniqid();

        $field = m::mock('SliQ\Field[getValues,getName,getOperand]');
        $field->shouldReceive('getValues')
            ->andReturn($values);
        $field->shouldReceive('getName')
            ->andReturn($name);
        $field->shouldReceive('getOperand')
            ->andReturn($operand);

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseValues]');
        $fieldParser->shouldReceive('parseValues')
            ->andReturn($values);

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parseDefault($field);

        $expected = sprintf(
            "%s %s %s",
            $name,
            FieldParser::$OPERANDS[$operand],
            $values
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the parseBetween method properly translates to a string
     */
    public function testThatParseBetweenProperlyTranslatesToString()
    {
        $values = array(
            mt_rand(),
            mt_rand(),
        );

        $name = uniqid();

        $field = m::mock('SliQ\Field[getValues,getName]');
        $field->shouldReceive('getValues')
            ->andReturn($values);
        $field->shouldReceive('getName')
            ->andReturn($name);

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseValues]');
        $fieldParser->shouldReceive('parseValues')
            ->andReturn($values);

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parseBetween($field);

        list($x, $y) = $values;

        $expected = sprintf(
            "%s %s %s AND %s %s %s",
            $name,
            FieldParser::$OPERANDS[Field::OP_GREATER_THAN],
            $x,
            $name,
            FieldParser::$OPERANDS[Field::OP_LESS_THAN],
            $y
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the parse method properly calls parseIn method when operand is IN
     */
    public function testThatParseProperlyCallsParseInMethodWhenOperandIn()
    {
        $expected = uniqid();

        $field = m::mock('SliQ\Field[getOperand]');
        $field->shouldReceive('getOperand')
            ->andReturn(Field::OP_IN);

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseIn]');
        $fieldParser->shouldReceive('parseIn')
            ->with($field)
            ->andReturn($expected);

        $fieldParser->shouldNotHaveReceived('parseBetween');

        $fieldParser->shouldNotHaveReceived('parseDefault');

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parse($field);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the parse method properly calls parseIn method when operand is NOT IN
     */
    public function testThatParseProperlyCallsParseInMethodWhenOperandNotIn()
    {
        $expected = uniqid();

        $field = m::mock('SliQ\Field[getOperand]');
        $field->shouldReceive('getOperand')
            ->andReturn(Field::OP_NOT_IN);

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseIn]');
        $fieldParser->shouldReceive('parseIn')
            ->with($field)
            ->andReturn($expected);

        $fieldParser->shouldNotHaveReceived('parseBetween');

        $fieldParser->shouldNotHaveReceived('parseDefault');

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parse($field);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the parse method properly calls parseIn method when operand is BETWEEN
     */
    public function testThatParseProperlyCallsParseInMethodWhenOperandBetween()
    {
        $expected = uniqid();

        $field = m::mock('SliQ\Field[getOperand]');
        $field->shouldReceive('getOperand')
            ->andReturn(Field::OP_BETWEEN);

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseBetween]');
        $fieldParser->shouldReceive('parseBetween')
            ->with($field)
            ->andReturn($expected);

        $fieldParser->shouldNotHaveReceived('parseIn');

        $fieldParser->shouldNotHaveReceived('parseDefault');

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parse($field);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the parse method properly calls parseIn method when operand is NOT BETWEEN
     */
    public function testThatParseProperlyCallsParseInMethodWhenOperandNotBetween()
    {
        $expected = uniqid();

        $field = m::mock('SliQ\Field[getOperand]');
        $field->shouldReceive('getOperand')
            ->andReturn(Field::OP_NOT_BETWEEN);

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseBetween]');
        $fieldParser->shouldReceive('parseBetween')
            ->with($field)
            ->andReturn($expected);

        $fieldParser->shouldNotHaveReceived('parseIn');

        $fieldParser->shouldNotHaveReceived('parseDefault');

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parse($field);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the parse method properly throws exception when operand is LIKE
     *
     * @expectedException \LogicException
     */
    public function testThatParseProperlyCallsParseInMethodWhenOperandLike()
    {
        $field = m::mock('SliQ\Field[getOperand]');
        $field->shouldReceive('getOperand')
            ->andReturn(Field::OP_LIKE);

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseBetween]');
        $fieldParser->shouldNotHaveReceived('parseBetween');
        $fieldParser->shouldNotHaveReceived('parseIn');
        $fieldParser->shouldNotHaveReceived('parseDefault');

        /** @var FieldParser $fieldParser */
        $fieldParser->parse($field);
    }

    /**
     * Test that the parse method properly throws exception when operand is NOT LIKE
     *
     * @expectedException \LogicException
     */
    public function testThatParseProperlyCallsParseInMethodWhenOperandNotLike()
    {
        $field = m::mock('SliQ\Field[getOperand]');
        $field->shouldReceive('getOperand')
            ->andReturn(Field::OP_NOT_LIKE);

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseBetween]');
        $fieldParser->shouldNotHaveReceived('parseBetween');
        $fieldParser->shouldNotHaveReceived('parseIn');
        $fieldParser->shouldNotHaveReceived('parseDefault');

        /** @var FieldParser $fieldParser */
        $fieldParser->parse($field);
    }

    /**
     * Test that the parse method properly calls parseDefault based on operand
     *
     * @dataProvider defaultOperandDataProvider
     */
    public function testThatParseProperlyCallsParseDefault($operand)
    {
        $expected = uniqid();

        $field = m::mock('SliQ\Field[getOperand]');
        $field->shouldReceive('getOperand')
            ->andReturn($operand);

        $fieldParser = m::mock('SliQ\Zuora\FieldParser[parseDefault]');
        $fieldParser->shouldReceive('parseDefault')
            ->with($field)
            ->andReturn($expected);

        $fieldParser->shouldNotHaveReceived('parseIn');
        $fieldParser->shouldNotHaveReceived('parseBetween');

        /** @var FieldParser $fieldParser */
        $actual = $fieldParser->parse($field);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function defaultOperandDataProvider()
    {
        $operands = array_keys(array_filter(FieldParser::$OPERANDS, function ($value) {
            return !is_null($value);
        }));

        $operands = array_map(function ($value) {
            return array($value);
        }, $operands);

        return $operands;
    }
}