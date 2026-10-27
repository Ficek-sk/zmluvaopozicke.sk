<?php

/**
 * Tabulka form
 *
 */
class MessageRepository extends Repository {

	public function find($id) {
		return $this->findBy(['id' => $id])->fetch();
	}

	public function create($values) {
		return $this->getTable()->insert($values);
	}

	public function update($id,$values) {
		return $this->findBy(['id'=>$id])->update($values);
	}

	public function delete($id) {
		return $this->findBy(['id'=>$id])->delete();
	}
}
