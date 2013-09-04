<?php

class dummy_user extends action_generic {
	
	protected $support_email;
	
	public function __construct() {
		parent::__construct();
		
		// Setup the support email address
		$this->support_email = "support" . '@' . "workoutinbox.com";
	}

	public function update() {
		$count = 0;
		
		$sql  = "SELECT id, phone, email, address ";
		$sql .= "FROM user ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			if ( $this->config->item('workoutinbox_test_server') ) {
				foreach ( $rows as $row ) {
					$count++;
					if ( $row->email != $this->support_email ) {
						// Create a number for the User based on thier user id
						$fields = new stdClass();
						$fields->id = $row->id;
						$fields->email = 'email' . substr('00000000' . $row->id, -8) . "@workoutinbox.com";
						$fields->phone = null;
						$fields->address = null;
						$return = $this->perform('table_workoutdb_user->update',$fields);
						unset($fields);
						if ( $return['status'] >= 300 ) {
							return $return;
						}
					}
				}
			}
		}
		
		$response = new stdClass();
		$response->count = $count;
		return $this->return_handler->results(200,"",$count);
	}
}