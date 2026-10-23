<?php

namespace App\Model\SystemModel;

use Nette\SmartObject;

abstract class BaseTable
{
    use SmartObject;

    /** @var \Nette\Database\Context */
    protected $context;

    /** @var \Nette\Database\Connection */
    protected $connection;

    /** @var string */
    protected $tableName;

    /**
     * @param SystemContext $systemContext
     */
    public function __construct(SystemContext $systemContext)
    {
        $this->context = $systemContext->context;
        if ($this->tableName === NULL) {
            $class = get_class($this);
            throw new \Nette\InvalidStateException("Table name must be defined in $class::\$tableName.");
        }

        $this->connection = $this->context->getConnection();
    }

    /**
     * @return \Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        return $this->context->table($this->tableName);
    }

    public function find($id)
    {
        return $this->context->table($this->tableName)->where(['id' => $id]);
    }

    public function findBy(array $criteria)
    {
        return $this->context->table($this->tableName)->where($criteria);
    }

    public function findOneBy(array $criteria)
    {
        return $this->context->table($this->tableName)->where($criteria);
    }

}