<?php

/**
 * Provádí operace nad databázovou tabulkou.
 */
abstract class Repository {

  /** @var Nette\Database\Connection */
  protected $connection;

    /** @var Nette\Database\Explorer */
    protected $context;

  public function __construct(\Nette\Database\Explorer $context) {
    $this->context = $context;
  }

  /**
   * Vrací objekt reprezentující databázovou tabulku.
   * @return Nette\Database\Table\Selection
   */
  protected function getTable() {
    // název tabulky odvodíme z názvu třídy
    preg_match('#(\w+)Repository$#', get_class($this), $m);
    return $this->context->table(lcfirst($m[1]));
  }

  /**
   * Vrací všechny řádky z tabulky.
   * @return Nette\Database\Table\Selection
   */
  public function findAll() {
    return $this->getTable();
  }

  /**
   * Vrací řádky podle filtru, např. array('name' => 'John').
   * @return Nette\Database\Table\Selection
   */
  public function findBy(array $by) {
    return $this->getTable()->where($by);
  }
}
