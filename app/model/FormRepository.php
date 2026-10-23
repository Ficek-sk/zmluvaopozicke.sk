<?php

use Nette\Templating,
	Nette\Latte;

/**
 * Tabulka form
 *
 */
class FormRepository extends Repository {
	
	public function find($id) {
		return $this->findBy(['id' => $id])->fetch();
	}

	public function create($values) {
		return $this->getTable()->insert($values);
	}

	public function update($id,$values) {
		return $this->findBy(['id'=>$id])->update($values);
	}
}
