<?php

class action_equipment extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// get All entries
	// ==================================================================================================================
	
	public function getAll( $p_use_alias=true ) {
		//
		// initialize the response data
		$count = 0;
		$entries = array();
		$response->count = $count;
		$response->results = $entries;
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$where_name_like = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$where_name_like  = "WHERE concat(";
			$where_name_like .= "if(isnull(library_equipment.name),'',concat(' ',library_equipment.name))";
			$where_name_like .= ") LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['page']) && !empty($_GET['page']) && is_numeric($_GET['page']) &&
		     isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) ) {
			$limit = "LIMIT " . (($_GET['page'] - 1) * $_GET['page_length']) . ", " . $_GET['page_length'] . " ";
		}
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the total record count without paging limits
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT count(library_equipment.id) cnt ";
		$sql .= "FROM library_equipment ";
		$sql .= $where_name_like;
		
		// echo "$sql<br />";
		
		$row = $this->db->query($sql)->row();
		if ( empty($row) || $row->cnt == 0 ) {
			$response->count = 0;
			$response->results = array();
			return $this->return_handler->results(200,"",$response);
		}
		$count = $row->cnt;
		
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the record entries
		//
		// ---------------------------------------------------------------------------------------------------------
		
		// Get the equipment with filters and limits
		$equipment_sql  = "";
		$equipment_sql .= "SELECT ";
		$equipment_sql .= "library_equipment.id id, library_equipment.name name ";
		$equipment_sql .= "FROM ";
		$equipment_sql .= "library_equipment ";
		$equipment_sql .= $where_name_like;
		$equipment_sql .= "ORDER BY library_equipment.name ";
		$equipment_sql .= $limit;
		
		// Get the Exercise Count for the equipment
		$cnt_ex_sql  = "";
		$cnt_ex_sql .= "SELECT ";
		$cnt_ex_sql .= "library_equipment.id id, ";
		$cnt_ex_sql .= "count(library_exercise_equipment.id) cnt ";
		$cnt_ex_sql .= "FROM ";
		$cnt_ex_sql .= "( " . $equipment_sql . ") library_equipment, ";
		$cnt_ex_sql .= "library_exercise_equipment ";
		$cnt_ex_sql .= "WHERE library_exercise_equipment.library_equipment_id = library_equipment.id ";
		$cnt_ex_sql .= "GROUP by library_equipment.id ";
		// Get the Workout Count for the equipment
		$cnt_wk_sql  = "";
		$cnt_wk_sql .= "SELECT ";
		$cnt_wk_sql .= "library_equipment.id id, ";
		$cnt_wk_sql .= "count(library_workout_library_equipment.id) cnt ";
		$cnt_wk_sql .= "FROM ";
		$cnt_wk_sql .= "( " . $equipment_sql . ") library_equipment, ";
		$cnt_wk_sql .= "library_workout_library_equipment ";
		$cnt_wk_sql .= "WHERE library_workout_library_equipment.library_equipment_id = library_equipment.id ";
		$cnt_wk_sql .= "GROUP by library_equipment.id ";
		// Get the Workout Log Count for the equipment
		$cnt_wl_sql  = "";
		$cnt_wl_sql .= "SELECT ";
		$cnt_wl_sql .= "library_equipment.id id, ";
		$cnt_wl_sql .= "count(workout_log_library_equipment.id) cnt ";
		$cnt_wl_sql .= "FROM ";
		$cnt_wl_sql .= "( " . $equipment_sql . ") library_equipment, ";
		$cnt_wl_sql .= "workout_log_library_equipment ";
		$cnt_wl_sql .= "WHERE workout_log_library_equipment.library_equipment_id = library_equipment.id ";
		$cnt_wl_sql .= "GROUP by library_equipment.id ";
		// Get Exercise total Usage Count
		$cnt_tot_sql  = "";
		$cnt_tot_sql .= "SELECT ";
		$cnt_tot_sql .= "id, sum(cnt) cnt ";
		$cnt_tot_sql .= "FROM ";
		$cnt_tot_sql .= "( ";
		$cnt_tot_sql .= "( " . $cnt_ex_sql . ") ";
		$cnt_tot_sql .= "UNION ";
		$cnt_tot_sql .= "( " . $cnt_wk_sql . ") ";
		$cnt_tot_sql .= "UNION ";
		$cnt_tot_sql .= "( " . $cnt_wl_sql . ") ";
		$cnt_tot_sql .= ") library_equipment_cnt ";
		$cnt_tot_sql .= "GROUP BY library_equipment_cnt.id ";
		
		$sql  = "SELECT library_equipment.id 'library_equipment.id', library_equipment.name 'library_equipment.name', ";
		$sql .= "if(library_equipment_cnt.cnt IS NULL OR library_equipment_cnt.cnt = 0,true,false) 'library_equipment_cnt.deletable.boolean', ";
		$sql .= "library_equipment_media_last_entered.id 'library_equipment_media_last_entered.id', library_equipment_media_last_entered.media_url 'library_equipment_media_last_entered.url', ";
		$sql .= "library_measurement.id 'library_measurement.id', library_measurement.name 'library_measurement.name' ";
		$sql .= "FROM ";
		$sql .= "( " . $equipment_sql . ") library_equipment ";
		$sql .= "LEFT OUTER JOIN ( " . $cnt_tot_sql . ") library_equipment_cnt ";
		$sql .= "ON library_equipment_cnt.id = library_equipment.id ";
		$sql .= "LEFT OUTER JOIN library_equipment_media_last_entered ";
		$sql .= "ON library_equipment_media_last_entered.library_equipment_id = library_equipment.id ";
		$sql .= "LEFT OUTER JOIN library_equipment_measurement ";
		$sql .= "LEFT OUTER JOIN library_measurement ";
		$sql .= "ON library_measurement.id = library_equipment_measurement.library_measurement_id ";
		$sql .= "ON library_equipment_measurement.library_equipment_id = library_equipment.id ";
		$sql .= "ORDER BY library_equipment.name ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			$response->count = 0;
			$response->results = array();
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->library_measurement = mysql_schema::getTableAlias('workoutdb','library_measurement',$p_use_alias);
		
		$library_equipment = array();
		$e = -1;
		
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
			
			if ( $e < 0 || $library_equipment[$e]->id != $row->library_equipment->id ) {
				++$e;
				$library_equipment[$e] = clone $row->library_equipment;
				$library_equipment[$e]->media = format_object_with_id($row->library_equipment_media_last_entered);
				// initialize the measurement array
				$library_equipment[$e]->{$table->library_measurement} = array();
				$library_measurement = &$library_equipment[$e]->{$table->library_measurement};
				$m = -1;
			}
			if ( !is_null($row->library_measurement->id) ) {
				if ( $m < 0 || $library_measurement[$m]->id != $row->library_measurement->id ) {
					++$m;
					$library_measurement[$m] = clone $row->library_measurement;
				}
			}
		}
				
		$response->count = $count;
		$response->results = $library_equipment;
		return $this->return_handler->results(200,"",$response);
		
	}

	// ==================================================================================================================
	// get a single entry for an id
	// ==================================================================================================================

	public function getForId( $p_library_equipment_id, $p_use_alias=true ) {
		
		// Get the Exercise Count for the equipment
		$cnt_ex_sql  = "";
		$cnt_ex_sql .= "SELECT ";
		$cnt_ex_sql .= "library_equipment.id id, ";
		$cnt_ex_sql .= "count(library_exercise_equipment.id) cnt ";
		$cnt_ex_sql .= "FROM ";
		$cnt_ex_sql .= "library_equipment, ";
		$cnt_ex_sql .= "library_exercise_equipment ";
		$cnt_ex_sql .= "WHERE library_equipment_id = " . $p_library_equipment_id . " ";
		$cnt_ex_sql .= "AND library_exercise_equipment.library_equipment_id = library_equipment.id ";
		$cnt_ex_sql .= "GROUP by library_equipment.id ";
		// Get the Workout Count for the equipment
		$cnt_wk_sql  = "";
		$cnt_wk_sql .= "SELECT ";
		$cnt_wk_sql .= "library_equipment.id id, ";
		$cnt_wk_sql .= "count(library_workout_library_equipment.id) cnt ";
		$cnt_wk_sql .= "FROM ";
		$cnt_wk_sql .= "library_equipment, ";
		$cnt_wk_sql .= "library_workout_library_equipment ";
		$cnt_wk_sql .= "WHERE library_equipment_id = " . $p_library_equipment_id . " ";
		$cnt_wk_sql .= "AND library_workout_library_equipment.library_equipment_id = library_equipment.id ";
		$cnt_wk_sql .= "GROUP by library_equipment.id ";
		// Get the Workout Log Count for the equipment
		$cnt_wl_sql  = "";
		$cnt_wl_sql .= "SELECT ";
		$cnt_wl_sql .= "library_equipment.id id, ";
		$cnt_wl_sql .= "count(workout_log_library_equipment.id) cnt ";
		$cnt_wl_sql .= "FROM ";
		$cnt_wl_sql .= "library_equipment, ";
		$cnt_wl_sql .= "workout_log_library_equipment ";
		$cnt_wl_sql .= "WHERE library_equipment_id = " . $p_library_equipment_id . " ";
		$cnt_wl_sql .= "AND workout_log_library_equipment.library_equipment_id = library_equipment.id ";
		$cnt_wl_sql .= "GROUP by library_equipment.id ";
		// Get Exercise total Usage Count
		$cnt_tot_sql  = "";
		$cnt_tot_sql .= "SELECT ";
		$cnt_tot_sql .= "id, sum(cnt) cnt ";
		$cnt_tot_sql .= "FROM ";
		$cnt_tot_sql .= "( ";
		$cnt_tot_sql .= "( " . $cnt_ex_sql . ") ";
		$cnt_tot_sql .= "UNION ";
		$cnt_tot_sql .= "( " . $cnt_wk_sql . ") ";
		$cnt_tot_sql .= "UNION ";
		$cnt_tot_sql .= "( " . $cnt_wl_sql . ") ";
		$cnt_tot_sql .= ") library_equipment_cnt ";
		$cnt_tot_sql .= "GROUP BY library_equipment_cnt.id ";
		// Get the list of media for the equipment
		$eq_media_sql  = "";
		$eq_media_sql .= "SELECT ";
		$eq_media_sql .= "library_equipment.id 'library_equipment.id', library_equipment.name 'library_equipment.name', ";
		$eq_media_sql .= "if(library_equipment_cnt.cnt IS NULL OR library_equipment_cnt.cnt = 0,true,false) 'library_equipment_cnt.deletable.boolean', ";
		$eq_media_sql .= "library_equipment_media.id 'library_equipment_media.id', library_equipment_media.media_url 'library_equipment_media.media_url', ";
		$eq_media_sql .= "null 'library_measurement.id', null 'library_measurement.name' ";
		$eq_media_sql .= "FROM ";
		$eq_media_sql .= "library_equipment ";
		$eq_media_sql .= "LEFT OUTER JOIN ( " . $cnt_tot_sql . ") library_equipment_cnt ";
		$eq_media_sql .= "ON library_equipment_cnt.id = library_equipment.id ";
		$eq_media_sql .= "LEFT OUTER JOIN library_equipment_media ";
		$eq_media_sql .= "ON library_equipment_media.library_equipment_id = library_equipment.id ";
		$eq_media_sql .= "WHERE library_equipment.id = " . $p_library_equipment_id . " ";
		// Get the list of measurements for the equipment
		$eq_measure_sql  = "";
		$eq_measure_sql .= "SELECT ";
		$eq_measure_sql .= "library_equipment.id 'library_equipment.id', library_equipment.name 'library_equipment.name', ";
		$eq_measure_sql .= "null 'library_equipment_cnt.deletable', ";
		$eq_measure_sql .= "null 'library_equipment_media.id', null 'library_equipment_media.media_url', ";
		$eq_measure_sql .= "library_measurement.id 'library_measurement.id', library_measurement.name 'library_measurement.name' ";
		$eq_measure_sql .= "FROM ";
		$eq_measure_sql .= "library_equipment ";
		$eq_measure_sql .= "LEFT OUTER JOIN library_equipment_measurement ";
		$eq_measure_sql .= "LEFT OUTER JOIN library_measurement ";
		$eq_measure_sql .= "ON library_measurement.id = library_equipment_measurement.library_measurement_id ";
		$eq_measure_sql .= "ON library_equipment_measurement.library_equipment_id = library_equipment.id ";
		$eq_measure_sql .= "WHERE library_equipment.id = " . $p_library_equipment_id . " ";
		
		$sql  = "";
		$sql .= "(" . $eq_media_sql . ") ";
		$sql .= "UNION ";
		$sql .= "(" . $eq_measure_sql . ") ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->library_measurement = mysql_schema::getTableAlias('workoutdb','library_measurement',$p_use_alias);
		
		$library_equipment = new stdClass();
		$library_equipment->id = null;
		
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
			
			if ( is_null($library_equipment->id) ) {
				$library_equipment = clone $row->library_equipment;
				// initialize deletable
				$library_equipment->deletable = true;
				$library_equipment_cnt = &$library_equipment->deletable;
				// initialize the media array
				$library_equipment->media = array();
				$library_equipment_media = &$library_equipment->media;
				$md = -1;
				// initialize the measure array
				$library_equipment->{$table->library_measurement} = array();
				$library_measurement = &$library_equipment->{$table->library_measurement};
				$ms = -1;
			}
			if ( !is_null($row->library_equipment_cnt->deletable) ) {
				$library_equipment_cnt = $row->library_equipment_cnt->deletable;
			}
			if ( !is_null($row->library_equipment_media->id) ) {
				if ( $md < 0 || $library_equipment_media[$md]->id != $row->library_equipment_media->id ) {
					$md++;
					$library_equipment_media[$md] = clone $row->library_equipment_media;
				}
			}
			if ( !is_null($row->library_measurement->id) ) {
				if ( $ms < 0 || $library_equipment[$ms]->id != $row->library_measurement->id ) {
					$ms++;
					$library_measurement[$ms] = clone $row->library_measurement;
				}
			}
		}
		
		return $this->return_handler->results(200,"",$library_equipment); 
	}

	// ==================================================================================================================
	// get a search list used for a searchable drop down menu
	// ==================================================================================================================

	public function getSearchList($p_use_alias=true) {
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$where_name_like = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$where_name_like  = "WHERE concat(";
			$where_name_like .= "if(isnull(library_equipment.name),'',concat(' ',library_equipment.name))";
			$where_name_like .= ") LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit']) ) {
			$limit = "LIMIT 0, " . $_GET['limit'] . " ";
		}
		
		// Get the equipment with filters and limits
		$equipment_sql  = "";
		$equipment_sql .= "SELECT ";
		$equipment_sql .= "library_equipment.id id, library_equipment.name name ";
		$equipment_sql .= "FROM ";
		$equipment_sql .= "library_equipment ";
		$equipment_sql .= $where_name_like;
		$equipment_sql .= "ORDER BY library_equipment.name ";
		$equipment_sql .= $limit;
		
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "library_equipment.id 'library_equipment.id', library_equipment.name 'library_equipment.name', ";
		$sql .= "library_equipment_media_last_entered.id 'library_equipment_media_last_entered.id', library_equipment_media_last_entered.media_url 'library_equipment_media_last_entered.media_url', ";
		$sql .= "library_measurement.id 'library_measurement.id', library_measurement.name 'library_measurement.name' ";
		$sql .= "FROM ";
		$sql .= "( " . $equipment_sql . ") library_equipment ";
		$sql .= "LEFT OUTER JOIN library_equipment_media_last_entered ";
		$sql .= "ON library_equipment_media_last_entered.library_equipment_id = library_equipment.id ";
		$sql .= "LEFT OUTER JOIN library_equipment_measurement ";
		$sql .= "LEFT OUTER JOIN library_measurement ";
		$sql .= "ON library_measurement.id = library_equipment_measurement.library_measurement_id ";
		$sql .= "ON library_equipment_measurement.library_equipment_id = library_equipment.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->library_measurement = mysql_schema::getTableAlias('workoutdb','library_measurement',$p_use_alias);
		
		$library_equipment = array();
		$e = -1;
		
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
			
			if ( $e < 0 || $library_equipment[$e]->id != $row->library_equipment->id ) {
				++$e;
				$library_equipment[$e] = clone $row->library_equipment;
				$library_equipment[$e]->media = format_object_with_id($row->library_equipment_media_last_entered);
				// initialize the measurement array
				$library_equipment[$e]->{$table->library_measurement} = array();
				$library_measurement = &$library_equipment[$e]->{$table->library_measurement};
				$m = -1;
			}
			if ( !is_null($row->library_measurement->id) ) {
				if ( $m < 0 || $library_measurement[$m]->id != $row->library_measurement->id ) {
					++$m;
					$library_measurement[$m] = clone $row->library_measurement;
				}
			}
		}
		
		return $this->return_handler->results(200,"",$library_equipment);
	}

	// ==================================================================================================================
	// create
	// ==================================================================================================================
	
	public function create( $data ) {
		//echo "data:"; print_r($data);
		// post the entry
		$return = $this->perform('table_workoutdb_library_equipment->insert',$data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$this->id = $return['response']->id;
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries and entries for media
		if ( isset($data->media) && !empty($data->media) ) {
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_equipment';
			$this->linked_table_name = 'library_equipment_media';
			$return = $this->perform('this->post_linked_entry_list',$data->media);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries for measurement
		if ( isset($data->measurement) && !empty($data->measurement) ) {
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_equipment';
			$this->xref_table_name = 'library_equipment_measurement';
			$this->xrefed_table_name = 'library_measurement';
			$return = $this->perform('this->post_xref_list',$data->measurement);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		return $return;
	}

	// ==================================================================================================================
	// update
	// ==================================================================================================================

	public function update ( $data ) {
		// put the entry
		$return = $this->perform('table_workoutdb_library_equipment->update',$data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// get the list of media for this piece of equipment
		$media_list = array();
		if ( isset($data->media) && !empty($data->media) ) {
			$media_list = $data->media;
		}
		// process the put list
		$this->id = $data->id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_equipment';
		$this->linked_table_name = 'library_equipment_media';
		$return = $this->perform('this->put_linked_entry_list',$media_list);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// get the list of measurement for this equipment
		$measurement_list = array();
		if ( isset($data->measurement) && !empty($data->measurement) ) {
			$measurement_list = $data->measurement;
		}
		// process the put list
		$this->id = $data->id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_equipment';
		$this->xref_table_name = 'library_equipment_measurement';
		$this->xrefed_table_name = 'library_measurement';
		$return = $this->perform('this->put_xref_list',$measurement_list);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		return $return;
	}

	// ==================================================================================================================
	// delete
	// ==================================================================================================================

	public function delete( $p_id = null ) {
		if ( is_null($p_id) || empty($p_id) ) {
			return $this->return_handler->results(204,"Id must be provided",array());
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		//  delete the equipment's media
		$this->id = $p_id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_equipment';
		$this->linked_table_name = 'library_equipment_media';
		$return = $this->perform('this->delete_linked_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		//  delete the equipment's measurement
		$this->id = $p_id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_equipment';
		$this->linked_table_name = 'library_equipment_measurement';
		$return = $this->perform('this->delete_linked_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// delete the equipment
		return $this->perform('table_workoutdb_library_equipment->delete',$p_id);
	}

}