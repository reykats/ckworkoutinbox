<?php

class action_location extends action_generic {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function getForClient($p_client_id) {
		//
		// initialize the response data
		$entries = array();
		// -----------------------------------------------------------------------------------
		// Create the select statemant
		// -----------------------------------------------------------------------------------
		$sql  = "SELECT * ";
		$sql .= "FROM location ";
		$sql .= "WHERE client_id = " . $p_client_id . " ";
		$sql .= "ORDER BY name ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			foreach ( $rows as $row ) {
				$entry = new stdClass();
				$entry->id = cast_int($row->id);
				$entry->name = $row->name;
				$entry->phone = $row->phone;
				$entry->address = $row->address;
				array_push($entries,$entry);
				unset($entry);
			}
				
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(204,"No Entries Found",$entries);
		}		
		
	}

}