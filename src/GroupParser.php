<?php


namespace SliQ\Zuora;

use SliQ\Group;
use SliQ\Conditional;
use SliQ\Statement\GroupParserInterface;
use SliQ\Statement\FieldParserInterface;

/**
 * Class GroupParser
 * @package SliQ\Zuora
 */
class GroupParser implements GroupParserInterface
{
    const GROUP_OPEN = '(';
    const GROUP_CLOSE = ')';

    /**
     * @var array
     */
    public static $TYPES = array(
        Conditional::TYPE_AND => 'AND',
        Conditional::TYPE_OR => 'OR',
        Conditional::TYPE_NEITHER => null,
    );

    /**
     * @var FieldParserInterface
     */
    protected $fieldParser;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param Group $group
     * @return string
     */
    public function parse(Group $group)
    {
        $self = $this;
        $parts = array_reduce($group->getConditionals(), function ($carry, $conditional) use ($self) {
            /** @var Conditional $conditional */
            $type = '';
            if (count($carry) !== 0) {
                $type = static::$TYPES[$conditional->getType()];
            }

            if ($conditional instanceof Group) {
                $carry[] = $type . ' ' . $this::GROUP_OPEN . $self->parse($conditional) . $self::GROUP_CLOSE;
                return $carry;
            }

            $field = $self->getFieldParser()->parse($conditional->getField());

            $carry[] = "{$type} {$field}";

            return $carry;
        }, array());

        return trim(implode(' ', $parts));
    }

    /**
     * @param FieldParserInterface $parser
     * @return $this
     */
    public function setFieldParser(FieldParserInterface $parser)
    {
        $this->fieldParser = $parser;
        return $this;
    }

    /**
     * @return FieldParserInterface
     */
    public function getFieldParser()
    {
        return $this->fieldParser;
    }
}