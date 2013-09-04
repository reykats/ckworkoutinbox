<?php

class table_default extends mysql_table {

	public function __construct() {
		parent::__construct();
	}
	
	public function initializeTableDefault($p_database_name,$p_table_name){
		return $this->initializeTable($p_database_name,$p_table_name);
	}
	
	public function insert($p_fields) {
		return $this->insertTableFields($p_fields);
	}
	
	public function reactivate($p_fields) {
		return $this->reactivateTable($p_fields);
	}
	
	public function update($p_fields) {
		return $this->updateTableFields($p_fields);
	}
	
	public function delete($p_id) {
		return $this->deleteTable($p_id);
	}
	
	public function deleteForAndKeys($p_keys) {
		return $this->deleteTableForAndKeys($p_keys);
	}
	
	public function deactivate($p_id) {
		return $this->deactivateTable($p_id);
	}
	
}
