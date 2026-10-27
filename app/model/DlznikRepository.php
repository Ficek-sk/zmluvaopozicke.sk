<?php

/**
 * Tabulka form
 *
 */
class DlznikRepository extends Repository {

	public function find($id) {
		return $this->findBy(['id' => $id])->fetch();
	}

	public function find_by_form_id($form_id) {
		return $this->findBy(['form_id' => $form_id]);
	}

	public function create($values) {
		return $this->getTable()->insert($values);
	}

	public function update($id,$values) {
		return $this->findBy(['id'=>$id])->update($values);
	}
}
