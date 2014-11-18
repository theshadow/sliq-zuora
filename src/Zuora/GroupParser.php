<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 11/16/14
 * Time: 5:34 PM
 */

namespace SliQ\Zuora;

use SliQ\Group;
use SliQ\Statement\GroupParserInterface;

class ZuoraGroupParser implements GroupParserInterface
{
    const GROUP_OPEN = '(';
    const GROUP_CLOSE = ')';

    protected $options = array();

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function parse(Group $group)
    {
        $self = $this;
        $parts = array_reduce($group->getConditionals(), function ($carry, $conditional) use ($this) {
            if ($conditional instanceof Group) {
                $carry[] = static::GROUP_OPEN . $this->parse($conditional) . static::GROUP_CLOSE;
                return $carry;
            }

            $type = '';
            if (count($carry) !== 0) {
                $type = $conditional->getType() . ' ';
            }

            $field = $this->getFieldParser()->parse($conditional->getField());

            $carry[] = "{$type}{$conditional->getField()} {$conditional->getOperand()} {$field}";

        }, array());

        return null;
    }

    public function getFieldParser()
    {
        return new FieldParser();
    }
}