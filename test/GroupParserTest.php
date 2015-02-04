<?php

namespace SliQ\Zuora;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use SliQ\Conditional;

/**
 * Class GroupParserTest
 * @package SliQ\Zuora
 */
class GroupParserTest extends PHPUnit_Framework_TestCase
{

    /**
     * test that the parse() method properly parses group without sub-groups
     */
    public function testThatParseProperlyParsesGroupWithoutSubGroup()
    {
        $name = uniqid();
        $value = mt_rand();

        $expected = $name . ' = ' . $value;

        $field = m::mock('SliQ\Field');

        $conditional = m::mock('SliQ\Conditional[getType,getField]');
        $conditional->shouldReceive('getType')
            ->andReturn(Conditional::TYPE_AND);
        $conditional->shouldReceive('getField')
            ->andReturn($field);

        $conditionals = array($conditional);

        $group = m::mock('SliQ\Group[getConditionals]');
        $group->shouldReceive('getConditionals')
            ->andReturn($conditionals);

        $fieldParser = m::mock('SliQ\Statement\FieldParserInterface[parse]');
        $fieldParser->shouldReceive('parse')
            ->with($field)
            ->andReturn($expected);

        $groupParser = m::mock('SliQ\Zuora\GroupParser[getFieldParser]');
        $groupParser->shouldReceive('getFieldParser')
            ->andReturn($fieldParser);

        /** @var GroupParser $groupParser */
        $actual = $groupParser->parse($group);

        $this->assertEquals($expected, $actual);
    }

    /**
     * test that the parse() method properly parses group with multiple conditionals and without sub-groups
     */
    public function testThatParseProperlyParsesGroupWithMultipleConditionalsAndWithoutSubGroup()
    {
        $firstFieldName = uniqid();
        $firstFieldValue = mt_rand();
        $secondFieldName = uniqid();
        $secondFieldValue = mt_rand();

        $firstFieldParsed = $firstFieldName . ' = ' . $firstFieldValue;
        $secondFieldParsed = $secondFieldName . ' = ' . $secondFieldValue;

        $expected = implode(' AND ', array($firstFieldParsed, $secondFieldParsed));

        $firstField = m::mock('SliQ\Field');
        $secondField = m::mock('SliQ\Field');

        $firstConditional = m::mock('SliQ\Conditional[getType,getField]');
        $firstConditional->shouldReceive('getType')
            ->andReturn(Conditional::TYPE_AND);
        $firstConditional->shouldReceive('getField')
            ->andReturn($firstField);

        $secondConditional = m::mock('SliQ\Conditional[getType,getField]');
        $secondConditional->shouldReceive('getType')
            ->andReturn(Conditional::TYPE_AND);
        $secondConditional->shouldReceive('getField')
            ->andReturn($secondField);

        $conditionals = array($firstConditional, $secondConditional);

        $group = m::mock('SliQ\Group[getConditionals]');
        $group->shouldReceive('getConditionals')
            ->andReturn($conditionals);

        $fieldParser = m::mock('SliQ\Statement\FieldParserInterface[parse]');
        $fieldParser->shouldReceive('parse')
            ->ordered()
            ->with($firstField)
            ->andReturn($firstFieldParsed);
        $fieldParser->shouldReceive('parse')
            ->ordered()
            ->with($secondField)
            ->andReturn($secondFieldParsed);

        $groupParser = m::mock('SliQ\Zuora\GroupParser[getFieldParser]');
        $groupParser->shouldReceive('getFieldParser')
            ->andReturn($fieldParser);

        /** @var GroupParser $groupParser */
        $actual = $groupParser->parse($group);

        $this->assertEquals($expected, $actual);
    }

    /**
     * test that the parse() method properly parses group with multiple conditionals and with sub-groups
     */
    public function testThatParseProperlyParsesGroupWithMultipleConditionalsAndWithSubGroup()
    {
        $firstFieldName = uniqid();
        $firstFieldValue = mt_rand();
        $secondFieldName = uniqid();
        $secondFieldValue = mt_rand();

        $firstFieldParsed = $firstFieldName . ' = ' . $firstFieldValue;
        $secondFieldParsed = $secondFieldName . ' = ' . $secondFieldValue;

        $expected = implode(
            ' ' . GroupParser::$TYPES[Conditional::TYPE_AND] . ' ',
            array($firstFieldParsed, $secondFieldParsed)
        );
        $expected = $expected . ' ' . GroupParser::$TYPES[Conditional::TYPE_OR] . ' ' . GroupParser::GROUP_OPEN
            . $expected . GroupParser::GROUP_CLOSE;

        $firstField = m::mock('SliQ\Field');
        $secondField = m::mock('SliQ\Field');

        $firstConditional = m::mock('SliQ\Conditional[getType,getField]');
        $firstConditional->shouldReceive('getType')
            ->andReturn(Conditional::TYPE_AND);
        $firstConditional->shouldReceive('getField')
            ->andReturn($firstField);

        $secondConditional = m::mock('SliQ\Conditional[getType,getField]');
        $secondConditional->shouldReceive('getType')
            ->andReturn(Conditional::TYPE_AND);
        $secondConditional->shouldReceive('getField')
            ->andReturn($secondField);

        $conditionals = array($firstConditional, $secondConditional);

        $subGroup = m::mock('SliQ\Group');
        $subGroup->shouldReceive('getConditionals')
            ->andReturn($conditionals);
        $subGroup->shouldReceive('getType')
            ->andReturn(Conditional::TYPE_OR);

        $conditionals[] = $subGroup;

        $group = m::mock('SliQ\Group[getConditionals]');
        $group->shouldReceive('getConditionals')
            ->andReturn($conditionals);

        $fieldParser = m::mock('SliQ\Statement\FieldParserInterface[parse]');
        $fieldParser->shouldReceive('parse')
            ->ordered('field parser')
            ->with($firstField)
            ->andReturn($firstFieldParsed);
        $fieldParser->shouldReceive('parse')
            ->ordered('field parser')
            ->with($secondField)
            ->andReturn($secondFieldParsed);
        $fieldParser->shouldReceive('parse')
            ->ordered('field parser')
            ->with($firstField)
            ->andReturn($firstFieldParsed);
        $fieldParser->shouldReceive('parse')
            ->ordered('field parser')
            ->with($secondField)
            ->andReturn($secondFieldParsed);

        $groupParser = m::mock('SliQ\Zuora\GroupParser[getFieldParser]');
        $groupParser->shouldReceive('getFieldParser')
            ->andReturn($fieldParser);

        /** @var GroupParser $groupParser */
        $actual = $groupParser->parse($group);

        $this->assertEquals($expected, $actual);
    }
}