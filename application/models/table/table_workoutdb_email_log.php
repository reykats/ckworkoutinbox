<?php

class table_workoutdb_email_log extends mysql_table {

	public function __construct() {
		parent::__construct();
		
		$this->initializeTable('workoutdb','email_log');
	}

	public function insert($p_status,$p_content,$p_param,$p_emails) {
		// -------------------------------------------------------------------------------------------------------------
		// insert the entry
		// -------------------------------------------------------------------------------------------------------------
		// create the email_log object
		$fields = new stdClass();
		$fields->status = $p_status;
		$email = new stdClass();
		$email->param = $p_param;
		$email->emails = $p_emails;
		$email->content = $p_content;
		$fields->json_email = json_encode($email);
		unset($email);
		// put the email_log entry
		$return = $this->insertTableFields($fields);
		unset($entry);
		return $return;
	}

	public function update($p_email_log_id,$p_status,$p_content,$p_param,$p_emails) {
		// -------------------------------------------------------------------------------------------------------------
		// Update the entry
		// -------------------------------------------------------------------------------------------------------------
		// create the email_log object
		$entry = new stdClass();
		$entry->id = $p_email_log_id;
		$entry->status = $p_status;
		$email = new stdClass();
		$email->param = $p_param;
		$email->emails = $p_emails;
		$email->content = $p_content;
		$entry->json_email = json_encode($email);
		unset($email);
		// put the email_log entry
		$return = $this->updateTableFields($entry);
		unset($entry);
		return $return;
	}
}