<?php

class action_uom extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	public function get( $p_use_alias=true ) {
		// initialize the response data
		$entry = new stdClass();
		//
		// select
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "library_measurement.id 'library_measurement.id', library_measurement.name 'library_measurement.name', ";
		$sql .= "library_measurement_system.id 'library_measurement_system.id', library_measurement_system.name 'library_measurement_system.name', ";
		$sql .= "library_measurement_system_unit.id 'library_measurement_system_unit.id', library_measurement_system_unit.name 'library_measurement_system_unit.name', ";
		$sql .= "library_measurement_system_unit.abbr 'library_measurement_system_unit.abbr' ";
		$sql .= "FROM ";
		$sql .= "library_measurement, ";
		$sql .= "library_measurement_system_unit, ";
		$sql .= "library_measurement_system ";
		$sql .= "WHERE library_measurement_system_unit.library_measurement_id = library_measurement.id ";
		$sql .= "AND library_measurement_system.id = library_measurement_system_unit.library_measurement_system_id ";
		$sql .= "ORDER BY library_measurement.id, library_measurement_system.id, library_measurement_system_unit.id ";
        // echo "$sql<br />";
        
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return $this->return_handler->results(204,"No Entry Found",array());
        }
        $rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->library_measurement = mysql_schema::getTableAlias('workoutdb','library_measurement',$p_use_alias);
		$table->library_measurement_system = mysql_schema::getTableAlias('workoutdb','library_measurement_system',$p_use_alias);
		$table->library_measurement_system_unit = mysql_schema::getTableAlias('workoutdb','library_measurement_system_unit',$p_use_alias);
		// print_r($table);
		
        $library_measurement = array();
        $m = -1;
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
			// echo json_encode($row) . "<br /><br />\n\n";
			
			if ( $m < 0 || $library_measurement[$m]->id != $row->library_measurement->id ) {
				++$m;
				$library_measurement[$m] = clone $row->library_measurement;
				// initailize the measurement system
				$library_measurement[$m]->{$table->library_measurement_system} = array();
				$library_measurement_system = &$library_measurement[$m]->{$table->library_measurement_system};
				$s = -1;
			}
			if ( $s < 0 || $library_measurement_system[$s]->id != $row->library_measurement_system->id ) {
				++$s;
				$library_measurement_system[$s] = clone $row->library_measurement_system;
				// initailize the system unit
				$library_measurement_system[$s]->{$table->library_measurement_system_unit} = array();
				$library_measurement_system_unit = &$library_measurement_system[$s]->{$table->library_measurement_system_unit};
				$u = -1;
			}
			if ( $u < 0 || $library_measurement_system_unit[$u]->id != $row->library_measurement_system_unit->id ) {
				++$u;
				$library_measurement_system_unit[$u] = clone $row->library_measurement_system_unit;
			}
		}
        
		return $this->return_handler->results(200,"",$library_measurement);   
	}

}