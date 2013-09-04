<?php

class action_body_region_part extends action_generic {
	
	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// Get a list of All body regions and their body parts
	// ==================================================================================================================
	
	public function getAll($p_use_alias=true) {
		$sql  = "SELECT ";
		$sql .= "library_body_region.id 'library_body_region.id', library_body_region.name 'library_body_region.name', ";
		$sql .= "library_body_part.id 'library_body_part.id', library_body_part.name 'library_body_part.name' ";
		$sql .= "FROM library_body_region ";
		$sql .= "LEFT OUTER JOIN library_body_region_body_part ";
		$sql .= "LEFT OUTER JOIN library_body_part ";
		$sql .= "ON library_body_part.id = library_body_region_body_part.library_body_part_id ";
		$sql .= "ON library_body_region_body_part.library_body_region_id = library_body_region.id ";
		$sql .= "ORDER BY library_body_region.name, library_body_part.name";
        // echo "$sql<br />";
        
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return $this->return_handler->results(204,"No Entry Found",array());
        }
        $rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->library_body_region = mysql_schema::getTableAlias('workoutdb','library_body_region',$p_use_alias);
		$table->library_body_part = mysql_schema::getTableAlias('workoutdb','library_body_part',$p_use_alias);
		
        $library_body_region = array();
        $r = -1;
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
                      
			if ( $r < 0 || $library_body_region[$r]->id != $row->library_body_region->id ) {
                ++$r;
                $library_body_region[$r] = clone $row->library_body_region;
				// initailize the body part array
                $library_body_region[$r]->{$table->library_body_part} = array();
                $library_body_part = &$library_body_region[$r]->{$table->library_body_part};
                $b = -1;
            }
			if ( !is_null($row->library_body_part->id) ) {
				if ( $b < 0 || $library_body_part[$b]->id != $row->library_body_part->id) {
	                ++$b;
					$library_body_part[$b] = clone $row->library_body_part;
	            }
			}
		}
		return $this->return_handler->results(200,"",$library_body_region);
	}

	// ==================================================================================================================
	// Get a list of the body regions for an exercise and their body parts
	// ==================================================================================================================

	public function getForExercise( $p_library_exercise_id, $p_use_alias=true ) {
		$sql  = "SELECT ";
		$sql .= "library_body_region.id 'library_body_region.id', library_body_region.name 'library_body_region.name', ";
		$sql .= "library_body_part.id 'library_body_part.id', library_body_part.name 'library_body_part.name' ";
		$sql .= "FROM library_exercise_body_part, ";
		$sql .= "library_body_part, ";
		$sql .= "library_body_region_body_part, ";
		$sql .= "library_body_region ";
		$sql .= "WHERE library_exercise_body_part.library_exercise_id = " . $p_library_exercise_id . " ";
		$sql .= "AND library_body_part.id = library_exercise_body_part.library_body_part_id ";
		$sql .= "AND library_body_region_body_part.library_body_part_id = library_body_part.id ";
		$sql .= "AND library_body_region.id = library_body_region_body_part.library_body_region_id ";
		$sql .= "ORDER BY library_body_region.name, library_body_part.name";
        // echo "$sql<br />";
		
		$query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return $this->return_handler->results(204,"No Entry Found",array());
        }
        $rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->library_body_region = mysql_schema::getTableAlias('workoutdb','library_body_region',$p_use_alias);
		$table->library_body_part = mysql_schema::getTableAlias('workoutdb','library_body_part',$p_use_alias);
		
        $library_body_region = array();
        $r = -1;
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row,$p_use_alias);
            //echo json_encode($row) . "<br /><br />\n\n";
            
			if ( $r < 0 || $library_body_region[$r]->id != $row->{$table->library_body_region}->id ) {
                ++$r;
				$library_body_region[$r] = clone $row->{$table->library_body_region};
				// Initialize the body part array
                $library_body_region[$r]->{$table->library_body_part} = array();
                $library_body_part = &$library_body_region[$r]->{$table->library_body_part};
                $b = -1;
            }
			if ( !is_null($row->{$table->library_body_part}->id) ) {
				if ( $b < 0 || $library_body_part[$b]->id != $row->{$table->library_body_part}->id ) {
	                ++$b;
					$library_body_part[$b] = clone $row->{$table->library_body_part};
				}
			}
		}
		return $this->return_handler->results(200,"",$library_body_region);
	}
}