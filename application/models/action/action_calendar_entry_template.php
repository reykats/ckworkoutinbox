<?php

class action_calendar_entry_template extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// Get al list of all calendar_entry_templates for a client
	// ==================================================================================================================

	public function getForClient($p_client_id,$p_use_alias=true) {
		//
		// Prepair optional check for calendar_entry_type_id in the sql statement
		$calendar_entry_type_id_check = "";
		if ( isset($_GET['q_ent']) && !empty($_GET['q_ent']) && is_numeric($_GET['q_ent']) ) {
			$calendar_entry_type_id_check = "AND calendar_entry_template.calendar_entry_type_id = " . $_GET['q_ent'] . " ";
		}
		//
		// select
		$sql  = "SELECT  calendar_entry_template.id 'calendar_entry_template.id',"; 
		$sql .= "calendar_entry_template.rsvp 'calendar_entry_template.rsvp',"; 
		$sql .= "calendar_entry_template.log_participant 'calendar_entry_template.log_participant',";
		$sql .= "calendar_entry_template.wod 'calendar_entry_template.wod',"; 
		$sql .= "calendar_entry_template.log_result 'calendar_entry_template.log_result',"; 
		$sql .= "calendar_entry_template.waiver 'calendar_entry_template.waiver',"; 
		$sql .= "calendar_entry_template.payment 'calendar_entry_template.payment',"; 
		$sql .= "calendar_entry_template.all_day 'calendar_entry_template.all_day',"; 
		$sql .= "calendar_entry_template.duration 'calendar_entry_template.duration',";
		$sql .= "calendar_entry_template.name 'calendar_entry_template.name', ";
		$sql .= "calendar_entry_type.id 'calendar_entry_type.id', calendar_entry_type.name 'calendar_entry_type.name' ";
		$sql .= "FROM calendar_entry_template calendar_entry_template ";
		$sql .= "LEFT OUTER JOIN calendar_entry_type ";
		$sql .= "ON calendar_entry_type.id = calendar_entry_template.calendar_entry_type_id ";
		$sql .= "WHERE calendar_entry_template.client_id = " . $p_client_id . " ";
		$sql .= $calendar_entry_type_id_check;

		// echo "$sql<br />";
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->calendar_entry_type = mysql_schema::getTableAlias('workoutdb','calendar_entry_type',$p_use_alias);
		
		$calendar_entry_template = array();
		$e = -1;
		
		foreach($rows as $row) {
			//echo "fdsfdasfdas";
			// cast the column values in the row to their column type
            mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
            //$temp = mysql_schema::objectify_row($row);
            $row = mysql_schema::objectify_row($row,$p_use_alias);
            
			++$e;
            $calendar_entry_template[$e] = clone $row->calendar_entry_template;
			$calendar_entry_template[$e]->{$table->calendar_entry_type} = format_object_with_id($row->calendar_entry_type);
		}
		return $this->return_handler->results(200,"",$calendar_entry_template);
	}
}