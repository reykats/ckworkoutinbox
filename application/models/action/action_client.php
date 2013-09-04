<?php

class action_client extends action_generic {
	
	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// get a search list of clients who have created workouts
	// ==================================================================================================================

	public function getSearchList() {	
		// ---------------------------------------------------------------------------------------------------------
		//
		// Prepair the select options
		//
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$search_check = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$search_check  = "AND client.name LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit']) ) {
			$limit = "LIMIT 0, " . $_GET['limit'] . " ";
		}
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the record entries
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT client.id 'client.id', client.name 'client.name' ";
		$sql .= "FROM client, ";
		$sql .= "library_workout ";
		$sql .= "WHERE library_workout.client_id = client.id ";
		$sql .= $search_check;
		$sql .= "GROUP BY client.name asc ";
		$sql .= $limit;
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
        $entries = array();
        $e = -1;
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row,$p_use_alias=false);
         	// echo json_encode($row) . "<br /><br />\n\n";
            ++$e;
            $entries[$e] = clone $row->client;
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	// ==================================================================================================================
	// get the clients that have a Facebook Page ID and have a Workout Of the Day
	// ==================================================================================================================

	public function getForWithFacebookAndWOD( $p_ccyymmdd, $p_use_alias=true ) {
		// Get all clients with a Facebook Page ID and a WOD on $p_ccyymmdd
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "client.id 'client.id', client.name 'client.name', ";
		$sql .= "client.widget_token 'client.widget_token', client.fb_page_id 'client.fb_page_id', client.fb_page_token 'client.fb_page_token ";
		$sql .= "FROM ";
		$sql .= "client, ";
		$sql .= "calendar_entry_template_wod, ";
		$sql .= "calendar_entry_template_wod_library_workout ";
		$sql .= "WHERE client.fb_page_id IS NOT NULL ";
		$sql .= "AND calendar_entry_template_wod.client_id = client.id ";
		$sql .= "AND calendar_entry_template_wod.yyyymmdd = " . $p_ccyymmdd . " ";
		$sql .= "AND calendar_entry_template_wod_library_workout.calendar_entry_template_wod_id = calendar_entry_template_wod.id ";
		$sql .= "GROUP BY client.id ";
        // echo "$sql<br />";
        
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return $this->return_handler->results(204,"No Entry Found",array());
        }
        $rows = $query->result();
		
        $client = array();
        $c = -1;
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
			
			++$c;
			$client[$c] = clone $row->client;
		}
		
		return $this->return_handler->results(200,"",$library_body_region);
	}
}