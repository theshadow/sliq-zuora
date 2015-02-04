<?php

namespace SliQ\Zuora;

use SliQ\SelectStatement;
use SliQ\Statement\GroupParserInterface;
use SliQ\Statement\SelectStatementParserInterface;

class SelectStatementParser implements SelectStatementParserInterface
{
    const FIELDS_ALL = '*';

    /**
     * @var GroupParserInterface
     */
    protected $groupParser;

    /**
     * @return GroupParserInterface
     */
    public function getGroupParser()
    {
        return $this->groupParser;
    }

    /**
     * @param GroupParserInterface $groupParser
     * @return static
     */
    public function setGroupParser($groupParser)
    {
        $this->groupParser = $groupParser;
        return $this;
    }

    /**
     * @param SelectStatement $statement
     * @return string
     */
    public function parse(SelectStatement $statement)
    {
        $query = 'SELECT '
            . $this->parseFields($statement->getFields())
            . ' FROM ' . $statement->getObject();

        if (!is_null($statement->getWhere())) {
            $query .= ' WHERE ' . $this->getGroupParser()->parse($statement->getWhere());
        }

        return $query;
    }

    public function parseFields(array $fields)
    {
        if ($fields[0] === static::FIELDS_ALL) {
            return static::FIELDS_ALL;
        }

        return implode(', ', $fields);
    }
}