<?php

class action_exercise extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// get All Exercises
	// ==================================================================================================================

	public function getAll( $p_use_alias=TRUE ) {
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$search_check = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$search_check  = "WHERE concat(";
			$search_check .= "if(isnull(library_exercise.name),'',concat(' ',library_exercise.name))";
			$search_check .= ") LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
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
		$sql  = "SELECT count(library_exercise.id) cnt ";
		$sql .= "FROM library_exercise ";
		$sql .= $search_check;
		
		// echo "$sql<br />";
		
		$row = $this->db->query($sql)->row();
		if ( empty($row) || $row->cnt == 0 ) {
			$response->count = 0;
			$response->results = array();
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$count = $row->cnt;
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the record entries
		//
		// ---------------------------------------------------------------------------------------------------------
		$exercise_sql  = "";
		$exercise_sql .= "SELECT ";
		$exercise_sql .= "library_exercise.* ";
		$exercise_sql .= "FROM ";
		$exercise_sql .= "library_exercise ";
		$exercise_sql .= $search_check;
		$exercise_sql .= "ORDER BY library_exercise.name ";
		$exercise_sql .= $limit;
		// Get the Workout Count for the exercise
		$cnt_wk_sql  = "";
		$cnt_wk_sql .= "SELECT ";
		$cnt_wk_sql .= "library_exercise.id id, ";
		$cnt_wk_sql .= "count(library_workout_library_exercise.id) cnt ";
		$cnt_wk_sql .= "FROM ";
		$cnt_wk_sql .= "( " . $exercise_sql . ") library_exercise, ";
		$cnt_wk_sql .= "library_workout_library_exercise ";
		$cnt_wk_sql .= "WHERE library_workout_library_exercise.library_exercise_id = library_exercise.id ";
		$cnt_wk_sql .= "GROUP by library_exercise.id ";
		// Get the Workout Log Count for the exercise
		$cnt_wl_sql  = "";
		$cnt_wl_sql .= "SELECT ";
		$cnt_wl_sql .= "library_exercise.id id, ";
		$cnt_wl_sql .= "count(workout_log_library_exercise.id) cnt ";
		$cnt_wl_sql .= "FROM ";
		$cnt_wl_sql .= "( " . $exercise_sql . ") library_exercise, ";
		$cnt_wl_sql .= "workout_log_library_exercise ";
		$cnt_wl_sql .= "WHERE workout_log_library_exercise.library_exercise_id = library_exercise.id ";
		$cnt_wl_sql .= "GROUP by library_exercise.id ";
		// Get Exercise total Usage Count
		$cnt_tot_sql  = "";
		$cnt_tot_sql .= "SELECT ";
		$cnt_tot_sql .= "id, sum(cnt) cnt ";
		$cnt_tot_sql .= "FROM ";
		$cnt_tot_sql .= "( ";
		$cnt_tot_sql .= "( " . $cnt_wk_sql . ") ";
		$cnt_tot_sql .= "UNION ";
		$cnt_tot_sql .= "( " . $cnt_wl_sql . ") ";
		$cnt_tot_sql .= ") library_exercise_cnt ";
		$cnt_tot_sql .= "GROUP BY library_exercise_cnt.id ";
		// Get the Exercise, its Media, its level, and if it is Deletable
		$ex_media_sql  = "";
		$ex_media_sql .= "SELECT ";
		$ex_media_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_media_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_media_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_media_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_media_sql .= "if(library_exercise_cnt.cnt IS NULL OR library_exercise_cnt.cnt = 0,1,0) 'library_exercise_cnt.deletable.boolean', ";
		$ex_media_sql .= "library_exercise_media_last_entered.id 'library_exercise_media_last_entered.id', library_exercise_media_last_entered.media_url 'library_exercise_media_last_entered.media_url', ";
		$ex_media_sql .= "library_exercise_level.id 'library_exercise_level.id', library_exercise_level.name 'library_exercise_level.name', ";
		$ex_media_sql .= "null 'library_sport_type.id', null 'library_sport_type.name', ";
		$ex_media_sql .= "null 'library_exercise_type.id', null 'library_exercise_type.name', ";
		$ex_media_sql .= "null 'library_body_part.id', null 'library_body_part.name' ";
		$ex_media_sql .= "FROM ";
		$ex_media_sql .= "( " . $exercise_sql . ") library_exercise ";
		$ex_media_sql .= "LEFT OUTER JOIN ( " . $cnt_tot_sql . ") library_exercise_cnt ";
		$ex_media_sql .= "ON library_exercise_cnt.id = library_exercise.id ";
		$ex_media_sql .= "LEFT OUTER JOIN library_exercise_media_last_entered ";
		$ex_media_sql .= "ON library_exercise_media_last_entered.library_exercise_id = library_exercise.id ";
		$ex_media_sql .= "LEFT OUTER JOIN library_exercise_level ";
		$ex_media_sql .= "ON library_exercise_level.id = library_exercise.library_exercise_level_id ";
		// Get the Exercise and its sport types
		$ex_sport_sql  = "";
		$ex_sport_sql .= "SELECT ";
		$ex_sport_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_sport_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_sport_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_sport_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_sport_sql .= "null 'library_exercise_cnt.deletable', ";
		$ex_sport_sql .= "null 'library_exercise_media_last_entered.id', null 'library_exercise_media_last_entered.media_url', ";
		$ex_sport_sql .= "null 'library_exercise_level.id', null 'library_exercise_level.name', ";
		$ex_sport_sql .= "library_sport_type.id 'library_sport_type.id', library_sport_type.name 'library_sport_type.name', ";
		$ex_sport_sql .= "null 'library_exercise_type.id', null 'library_exercise_type.name', ";
		$ex_sport_sql .= "null 'library_body_part.id', null 'library_body_part.name' ";
		$ex_sport_sql .= "FROM ";
		$ex_sport_sql .= "( " . $exercise_sql . ") library_exercise ";
		$ex_sport_sql .= "LEFT OUTER JOIN library_exercise_sport_type ";
		$ex_sport_sql .= "LEFT OUTER JOIN library_sport_type ";
		$ex_sport_sql .= "ON library_sport_type.id = library_exercise_sport_type.library_sport_type_id ";
		$ex_sport_sql .= "ON library_exercise_sport_type.library_exercise_id = library_exercise.id ";
		// Get the Exercise and its exercise types
		$ex_type_sql  = "";
		$ex_type_sql .= "SELECT ";
		$ex_type_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_type_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_type_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_type_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_type_sql .= "null 'library_exercise_cnt.deletable', ";
		$ex_type_sql .= "null 'library_exercise_media_last_entered.id', null 'library_exercise_media_last_entered.media_url', ";
		$ex_type_sql .= "null 'library_exercise_level.id', null 'library_exercise_level.name', ";
		$ex_type_sql .= "null 'library_sport_type.id', null 'library_sport_type.name', ";
		$ex_type_sql .= "library_exercise_type.id 'library_exercise_type.id', library_exercise_type.name 'library_exercise_type.name', ";
		$ex_type_sql .= "null 'library_body_part.id', null 'library_body_part.name' ";
		$ex_type_sql .= "FROM ";
		$ex_type_sql .= "( " . $exercise_sql . ") library_exercise ";
		$ex_type_sql .= "LEFT OUTER JOIN library_exercise_exercise_type ";
		$ex_type_sql .= "LEFT OUTER JOIN library_exercise_type ";
		$ex_type_sql .= "ON library_exercise_type.id = library_exercise_exercise_type.library_exercise_type_id ";
		$ex_type_sql .= "ON library_exercise_exercise_type.library_exercise_id = library_exercise.id ";
		// Get the Exercise and its body parts
		$ex_part_sql  = "";
		$ex_part_sql .= "SELECT ";
		$ex_part_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_part_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_part_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_part_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_part_sql .= "null 'library_exercise_cnt.deletable', ";
		$ex_part_sql .= "null 'library_exercise_media_last_entered.id', null 'library_exercise_media_last_entered.media_url', ";
		$ex_part_sql .= "null 'library_exercise_level.id', null 'library_exercise_level.name', ";
		$ex_part_sql .= "null 'library_sport_type.id', null 'library_sport_type.name', ";
		$ex_part_sql .= "null 'library_exercise_type.id', null 'library_exercise_type.name', ";
		$ex_part_sql .= "library_body_part.id 'library_body_part.id', library_body_part.name 'library_body_part.name' ";
		$ex_part_sql .= "FROM ";
		$ex_part_sql .= "( " . $exercise_sql . ") library_exercise ";
		$ex_part_sql .= "LEFT OUTER JOIN library_exercise_body_part ";
		$ex_part_sql .= "LEFT OUTER JOIN library_body_part ";
		$ex_part_sql .= "ON library_body_part.id = library_exercise_body_part.library_body_part_id ";
		$ex_part_sql .= "ON library_exercise_body_part.library_exercise_id = library_exercise.id ";
		
		$sql  = "";
		$sql .= "( " . $ex_media_sql . ") ";
		$sql .= "UNION ";
		$sql .= "( " . $ex_sport_sql . ") ";
		$sql .= "UNION ";
		$sql .= "( " . $ex_type_sql . ") ";
		$sql .= "UNION ";
		$sql .= "( " . $ex_part_sql . ") ";
		$sql .= "ORDER BY `library_exercise.name`, `library_exercise.id`";

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
		$table->library_exercise_level = mysql_schema::getTableAlias('workoutdb','library_exercise_level',$p_use_alias);
		$table->library_sport_type = mysql_schema::getTableAlias('workoutdb','library_sport_type',$p_use_alias);
		$table->library_exercise_type = mysql_schema::getTableAlias('workoutdb','library_exercise_type',$p_use_alias);
		$table->library_body_part = mysql_schema::getTableAlias('workoutdb','library_body_part',$p_use_alias);
		
		// print_r($table);
		
		$library_exercise = array();
		$e = -1;
		
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
			
			if ( $e < 0 || $library_exercise[$e]->id != $row->library_exercise->id ) {
				++$e;
				$library_exercise[$e] = clone $row->library_exercise;
				unset($library_exercise[$e]->json_instructions);
				// initialize the deletable switch
				$library_exercise[$e]->deletable = true;
				$library_exercise_deletable = &$library_exercise[$e]->deletable;
				// initialize the media (hardcode the node name to media)
				$library_exercise[$e]->media = new stdClass();
				$library_exercise_media_last_entered = &$library_exercise[$e]->media;
				// initialize the exercise_level
				$library_exercise[$e]->{$table->library_exercise_level} = new stdClass();
				$library_exercise_level = &$library_exercise[$e]->{$table->library_exercise_level};
				// initialize the sport type array
				$library_exercise[$e]->{$table->library_sport_type} = array();
				$library_sport_type = &$library_exercise[$e]->{$table->library_sport_type};
				$sp = -1;
				// initialize the exercise type array
				$library_exercise[$e]->{$table->library_exercise_type} = array();
				$library_exercise_type = &$library_exercise[$e]->{$table->library_exercise_type};
				$et = -1;
				// initialize the body part array
				$library_exercise[$e]->{$table->library_body_part} = array();
				$library_body_part = &$library_exercise[$e]->{$table->library_body_part};
				$bp = -1;
			}
			if ( !is_null($row->library_exercise_cnt->deletable) ) {
				$library_exercise_deletable = $row->library_exercise_cnt->deletable;
			}
			if ( !is_null($row->library_exercise_media_last_entered->id) ) {
				$library_exercise_media_last_entered = clone $row->library_exercise_media_last_entered;
			}
			if ( !is_null($row->library_exercise_level->id) ) {
				$library_exercise_level = clone $row->library_exercise_level;
			}
			if ( !is_null($row->library_sport_type->id) ) {
				if ( $sp < 0 || $library_sport_type[$sp]->id != $row->library_sport_type->id ) {
					++$sp;
					$library_sport_type[$sp] = clone $row->library_sport_type;
				}
			}
			if ( !is_null($row->library_exercise_type->id) ) {
				if ( $et < 0 || $library_exercise_type[$et]->id != $row->library_exercise_type->id ) {
					++$et;
					$library_exercise_type[$et] = clone $row->library_exercise_type;
				}
			}
			if ( !is_null($row->library_body_part->id) ) {
				if ( $bp < 0 || $library_body_part[$bp]->id != $row->library_body_part->id ) {
					++$bp;
					$library_body_part[$bp] = clone $row->library_body_part;
				}
			}
		}

		$response->count = $count;
		$response->results = $library_exercise;
		return $this->return_handler->results(200,"",$response);
	}


	// ==================================================================================================================
	// get a single exercise using its id
	// ==================================================================================================================

	public function getForId( $p_library_exercise_id, $p_use_alias=true ) {
		// Get the Workout Count for the exercise
		$cnt_wk_sql  = "";
		$cnt_wk_sql .= "SELECT ";
		$cnt_wk_sql .= "library_exercise.id id, ";
		$cnt_wk_sql .= "count(library_workout_library_exercise.id) cnt ";
		$cnt_wk_sql .= "FROM ";
		$cnt_wk_sql .= "library_exercise, ";
		$cnt_wk_sql .= "library_workout_library_exercise ";
		$cnt_wk_sql .= "WHERE library_exercise.id = " . $p_library_exercise_id . " ";
		$cnt_wk_sql .= "AND library_workout_library_exercise.library_exercise_id = library_exercise.id ";
		// Get the Workout Log Count for the exercise
		$cnt_wl_sql  = "";
		$cnt_wl_sql .= "SELECT ";
		$cnt_wl_sql .= "library_exercise.id id, ";
		$cnt_wl_sql .= "count(workout_log_library_exercise.id) cnt ";
		$cnt_wl_sql .= "FROM ";
		$cnt_wl_sql .= "library_exercise, ";
		$cnt_wl_sql .= "workout_log_library_exercise ";
		$cnt_wl_sql .= "WHERE library_exercise.id = " . $p_library_exercise_id . " ";
		$cnt_wl_sql .= "AND workout_log_library_exercise.library_exercise_id = library_exercise.id ";
		// Get Exercise total Usage Count
		$cnt_tot_sql  = "";
		$cnt_tot_sql .= "SELECT ";
		$cnt_tot_sql .= "id, sum(cnt) cnt ";
		$cnt_tot_sql .= "FROM ";
		$cnt_tot_sql .= "( ";
		$cnt_tot_sql .= "( " . $cnt_wk_sql . ") ";
		$cnt_tot_sql .= "UNION ";
		$cnt_tot_sql .= "( " . $cnt_wl_sql . ") ";
		$cnt_tot_sql .= ") library_exercise_cnt ";
		// Get exercise and if it is deletable
		$ex_del_sql  = "";
		$ex_del_sql .= "SELECT ";
		$ex_del_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_del_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_del_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_del_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_del_sql .= "if(library_exercise_cnt.cnt IS NULL OR library_exercise_cnt.cnt = 0,1,0) 'library_exercise_cnt.deletable.boolean', ";
		$ex_del_sql .= "library_exercise_level.id 'library_exercise_level.id', library_exercise_level.name 'library_exercise_level.name', ";
		$ex_del_sql .= "null 'library_equipment.id', null 'library_equipment.name', null 'library_equipment.deletable', ";
		$ex_del_sql .= "null 'library_equipment_media_last_entered.id', null 'library_equipment_media_last_entered.media_url', ";
		$ex_del_sql .= "null 'library_measurement.id', null 'library_measurement.name', ";
		$ex_del_sql .= "null 'library_exercise_media.id', null 'library_exercise_media.media_url', ";
		$ex_del_sql .= "null 'library_exercise_instruction.id', null 'library_exercise_instruction.description', ";
		$ex_del_sql .= "null 'library_exercise_instruction_media.id', null 'library_exercise_instruction_media.media_url', ";
		$ex_del_sql .= "null 'library_sport_type.id', null 'library_sport_type.name', ";
		$ex_del_sql .= "null 'library_exercise_type.id', null 'library_exercise_type.name', ";
		$ex_del_sql .= "null 'library_body_part.id', null 'library_body_part.name'  ";
		$ex_del_sql .= "FROM library_exercise ";
		$ex_del_sql .= "LEFT OUTER JOIN (" . $cnt_tot_sql . ") library_exercise_cnt ";
		$ex_del_sql .= "ON library_exercise_cnt.id = library_exercise.id ";
		$ex_del_sql .= "LEFT OUTER JOIN library_exercise_level ";
		$ex_del_sql .= "ON library_exercise_level.id = library_exercise.library_exercise_level_id ";
		$ex_del_sql .= "WHERE library_exercise.id = " . $p_library_exercise_id . " ";
		// Get exercise and it's equipment
		$ex_eq_sql  = "";
		$ex_eq_sql .= "SELECT ";
		$ex_eq_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_eq_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_eq_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_eq_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_eq_sql .= "null 'library_exercise_cnt.deletable', ";
		$ex_eq_sql .= "null 'library_exercise_level.id', null 'library_exercise_level.name', ";
		$ex_eq_sql .= "library_equipment.id 'library_equipment.id', library_equipment.name 'library_equipment.name', ";
		$ex_eq_sql .= "if(library_exercise_equipment.mandatory,0,1) 'library_equipment.deletable.boolean', ";
		$ex_eq_sql .= "library_equipment_media_last_entered.id 'library_equipment_media_last_entered.id', library_equipment_media_last_entered.media_url 'library_equipment_media_last_entered.media_url', ";
		$ex_eq_sql .= "library_measurement.id 'library_measurement.id', library_measurement.name 'library_measurement.name', ";
		$ex_eq_sql .= "null 'library_exercise_media.id', null 'library_exercise_media.media_url', ";
		$ex_eq_sql .= "null 'library_exercise_instruction.id', null 'library_exercise_instruction.description', ";
		$ex_eq_sql .= "null 'library_exercise_instruction_media.id', null 'library_exercise_instruction_media.media_url', ";
		$ex_eq_sql .= "null 'library_sport_type.id', null 'library_sport_type.name', ";
		$ex_eq_sql .= "null 'library_exercise_type.id', null 'library_exercise_type.name', ";
		$ex_eq_sql .= "null 'library_body_part.id', null 'library_body_part.name'  ";
		$ex_eq_sql .= "FROM library_exercise ";
		$ex_eq_sql .= "LEFT OUTER JOIN library_exercise_equipment ";
		$ex_eq_sql .= "LEFT OUTER JOIN library_equipment ";
		$ex_eq_sql .= "LEFT OUTER JOIN library_equipment_measurement ";
		$ex_eq_sql .= "LEFT OUTER JOIN library_measurement ";
		$ex_eq_sql .= "ON library_measurement.id = library_equipment_measurement.library_measurement_id ";
		$ex_eq_sql .= "ON library_equipment_measurement.library_equipment_id = library_equipment.id ";
		$ex_eq_sql .= "LEFT OUTER JOIN library_equipment_media_last_entered ";
		$ex_eq_sql .= "ON library_equipment_media_last_entered.library_equipment_id = library_equipment.id ";
		$ex_eq_sql .= "ON library_equipment.id = library_exercise_equipment.library_equipment_id ";
		$ex_eq_sql .= "ON library_exercise_equipment.library_exercise_id = library_exercise.id ";
		$ex_eq_sql .= "WHERE library_exercise.id = " . $p_library_exercise_id . " ";
		// Get exercise and all media
		$ex_media_sql  = "";
		$ex_media_sql .= "SELECT ";
		$ex_media_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_media_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_media_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_media_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_media_sql .= "null 'library_exercise_cnt.deletable', ";
		$ex_media_sql .= "null 'library_exercise_level.id', null 'library_exercise_level.name', ";
		$ex_media_sql .= "null 'library_equipment.id', null 'library_equipment.name', null 'library_equipment.deletable', ";
		$ex_media_sql .= "null 'library_equipment_media_last_entered.id', null 'library_equipment_media_last_entered.media_url', ";
		$ex_media_sql .= "null 'library_measurement.id', null 'library_measurement.name', ";
		$ex_media_sql .= "library_exercise_media.id 'library_exercise_media.id', library_exercise_media.media_url 'library_exercise_media.media_url', ";
		$ex_media_sql .= "null 'library_exercise_instruction.id', null 'library_exercise_instruction.description', ";
		$ex_media_sql .= "null 'library_exercise_instruction_media.id', null 'library_exercise_instruction_media.media_url', ";
		$ex_media_sql .= "null 'library_sport_type.id', null 'library_sport_type.name', ";
		$ex_media_sql .= "null 'library_exercise_type.id', null 'library_exercise_type.name', ";
		$ex_media_sql .= "null 'library_body_part.id', null 'library_body_part.name'  ";
		$ex_media_sql .= "FROM library_exercise ";
		$ex_media_sql .= "LEFT OUTER JOIN library_exercise_exercise_media ";
		$ex_media_sql .= "LEFT OUTER JOIN library_exercise_media ";
		$ex_media_sql .= "ON library_exercise_media.id = library_exercise_exercise_media.library_exercise_media_id ";
		$ex_media_sql .= "ON library_exercise_exercise_media.library_exercise_id = library_exercise.id ";
		$ex_media_sql .= "WHERE library_exercise.id = " . $p_library_exercise_id . " ";
		// Get exercise and all instructions
		$ex_inst_sql  = "";
		$ex_inst_sql .= "SELECT ";
		$ex_inst_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_inst_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_inst_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_inst_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_inst_sql .= "null 'library_exercise_cnt.deletable', ";
		$ex_inst_sql .= "null 'library_exercise_level.id', null 'library_exercise_level.name', ";
		$ex_inst_sql .= "null 'library_equipment.id', null 'library_equipment.name', null 'library_equipment.deletable', ";
		$ex_inst_sql .= "null 'library_equipment_media_last_entered.id', null 'library_equipment_media_last_entered.media_url', ";
		$ex_inst_sql .= "null 'library_measurement.id', null 'library_measurement.name', ";
		$ex_inst_sql .= "null 'library_exercise_media.id', null 'library_exercise_media.media_url', ";
		$ex_inst_sql .= "library_exercise_instruction.id 'library_exercise_instruction.id', library_exercise_instruction.description 'library_exercise_instruction.description', ";
		$ex_inst_sql .= "library_exercise_media.id 'library_exercise_instruction_media.id', library_exercise_media.media_url 'library_exercise_instruction_media.media_url', ";
		$ex_inst_sql .= "null 'library_sport_type.id', null 'library_sport_type.name', ";
		$ex_inst_sql .= "null 'library_exercise_type.id', null 'library_exercise_type.name', ";
		$ex_inst_sql .= "null 'library_body_part.id', null 'library_body_part.name'  ";
		$ex_inst_sql .= "FROM library_exercise ";
		$ex_inst_sql .= "LEFT OUTER JOIN library_exercise_instruction ";
		$ex_inst_sql .= "LEFT OUTER JOIN library_exercise_media ";
		$ex_inst_sql .= "ON library_exercise_media.id = library_exercise_instruction.library_exercise_media_id ";
		$ex_inst_sql .= "ON library_exercise_instruction.library_exercise_id = library_exercise.id ";
		$ex_inst_sql .= "WHERE library_exercise.id = " . $p_library_exercise_id . " ";
		// Get exercise and all Sports Types
		$ex_sport_sql  = "";
		$ex_sport_sql .= "SELECT ";
		$ex_sport_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_sport_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_sport_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_sport_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_sport_sql .= "null 'library_exercise_cnt.deletable', ";
		$ex_sport_sql .= "null 'library_exercise_level.id', null 'library_exercise_level.name', ";
		$ex_sport_sql .= "null 'library_equipment.id', null 'library_equipment.name', null 'library_equipment.deletable', ";
		$ex_sport_sql .= "null 'library_equipment_media_last_entered.id', null 'library_equipment_media_last_entered.media_url', ";
		$ex_sport_sql .= "null 'library_measurement.id', null 'library_measurement.name', ";
		$ex_sport_sql .= "null 'library_exercise_media.id', null 'library_exercise_media.media_url', ";
		$ex_sport_sql .= "null 'library_exercise_instruction.id', null 'library_exercise_instruction.description', ";
		$ex_sport_sql .= "null 'library_exercise_instruction_media.id', null 'library_exercise_instruction_media.media_url', ";
		$ex_sport_sql .= "library_sport_type.id 'library_sport_type.id', library_sport_type.name 'library_sport_type.name', ";
		$ex_sport_sql .= "null 'library_exercise_type.id', null 'library_exercise_type.name', ";
		$ex_sport_sql .= "null 'library_body_part.id', null 'library_body_part.name'  ";
		$ex_sport_sql .= "FROM library_exercise ";
		$ex_sport_sql .= "LEFT OUTER JOIN library_exercise_sport_type ";
		$ex_sport_sql .= "LEFT OUTER JOIN library_sport_type ";
		$ex_sport_sql .= "ON library_sport_type.id = library_exercise_sport_type.library_sport_type_id ";
		$ex_sport_sql .= "ON library_exercise_sport_type.library_exercise_id = library_exercise.id ";
		$ex_sport_sql .= "WHERE library_exercise.id = " . $p_library_exercise_id . " ";
		// Get exercise and all exercise Types
		$ex_type_sql  = "";
		$ex_type_sql .= "SELECT ";
		$ex_type_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_type_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_type_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_type_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_type_sql .= "null 'library_exercise_cnt.deletable', ";
		$ex_type_sql .= "null 'library_exercise_level.id', null 'library_exercise_level.name', ";
		$ex_type_sql .= "null 'library_equipment.id', null 'library_equipment.name', null 'library_equipment.deletable', ";
		$ex_type_sql .= "null 'library_equipment_media_last_entered.id', null 'library_equipment_media_last_entered.media_url', ";
		$ex_type_sql .= "null 'library_measurement.id', null 'library_measurement.name', ";
		$ex_type_sql .= "null 'library_exercise_media.id', null 'library_exercise_media.media_url', ";
		$ex_type_sql .= "null 'library_exercise_instruction.id', null 'library_exercise_instruction.description', ";
		$ex_type_sql .= "null 'library_exercise_instruction_media.id', null 'library_exercise_instruction_media.media_url', ";
		$ex_type_sql .= "null 'library_sport_type.id', null 'library_sport_type.name', ";
		$ex_type_sql .= "library_exercise_type.id 'library_exercise_type.id', library_exercise_type.name 'library_exercise_type.name', ";
		$ex_type_sql .= "null 'library_body_part.id', null 'library_body_part.name' ";
		$ex_type_sql .= "FROM library_exercise ";
		$ex_type_sql .= "LEFT OUTER JOIN library_exercise_exercise_type ";
		$ex_type_sql .= "LEFT OUTER JOIN library_exercise_type ";
		$ex_type_sql .= "ON library_exercise_type.id = library_exercise_exercise_type.library_exercise_type_id ";
		$ex_type_sql .= "ON library_exercise_exercise_type.library_exercise_id = library_exercise.id ";
		$ex_type_sql .= "WHERE library_exercise.id = " . $p_library_exercise_id . " ";
		// Get exercise and all body parts
		$ex_part_sql  = "";
		$ex_part_sql .= "SELECT ";
		$ex_part_sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$ex_part_sql .= "library_exercise.bodyweight 'library_exercise.bodyweight', library_exercise.distance 'library_exercise.distance', ";
		$ex_part_sql .= "library_exercise.json_instructions 'library_exercise.json_instructions', ";
		$ex_part_sql .= "library_exercise.library_body_region_id 'library_exercise.library_body_region_id', ";
		$ex_part_sql .= "null 'library_exercise_cnt.deletable', ";
		$ex_part_sql .= "null 'library_exercise_level.id', null 'library_exercise_level.name', ";
		$ex_part_sql .= "null 'library_equipment.id', null 'library_equipment.name', null 'library_equipment.deletable', ";
		$ex_part_sql .= "null 'library_equipment_media_last_entered.id', null 'library_equipment_media_last_entered.media_url', ";
		$ex_part_sql .= "null 'library_measurement.id', null 'library_measurement.name', ";
		$ex_part_sql .= "null 'library_exercise_media.id', null 'library_exercise_media.media_url', ";
		$ex_part_sql .= "null 'library_exercise_instruction.id', null 'library_exercise_instruction.description', ";
		$ex_part_sql .= "null 'library_exercise_instruction_media.id', null 'library_exercise_instruction_media.media_url', ";
		$ex_part_sql .= "null 'library_sport_type.id', null 'library_sport_type.name', ";
		$ex_part_sql .= "null 'library_exercise_type.id', null 'library_exercise_type.name', ";
		$ex_part_sql .= "library_body_part.id 'library_body_part.id', library_body_part.name 'library_body_part.name' ";
		$ex_part_sql .= "FROM library_exercise ";
		$ex_part_sql .= "LEFT OUTER JOIN library_exercise_body_part ";
		$ex_part_sql .= "LEFT OUTER JOIN library_body_part ";
		$ex_part_sql .= "ON library_body_part.id = library_exercise_body_part.library_body_part_id ";
		$ex_part_sql .= "ON library_exercise_body_part.library_exercise_id = library_exercise.id ";
		$ex_part_sql .= "WHERE library_exercise.id = " . $p_library_exercise_id . " ";
		
		$sql  = "";
		$sql .= "( " . $ex_del_sql . ") ";
		$sql .= "UNION ";
		$sql .= "( " . $ex_eq_sql . ") ";
		$sql .= "UNION ";
		$sql .= "( " . $ex_media_sql . ") ";
		$sql .= "UNION ";
		$sql .= "( " . $ex_inst_sql . ") ";
		$sql .= "UNION ";
		$sql .= "( " . $ex_sport_sql . ") ";
		$sql .= "UNION ";
		$sql .= "( " . $ex_type_sql . ") ";
		$sql .= "UNION ";
		$sql .= "( " . $ex_part_sql . ") ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->library_exercise_level = mysql_schema::getTableAlias('workoutdb','library_exercise_level',$p_use_alias);
		$table->library_equipment = mysql_schema::getTableAlias('workoutdb','library_equipment',$p_use_alias);
		$table->library_measurement = mysql_schema::getTableAlias('workoutdb','library_measurement',$p_use_alias);
		$table->library_exercise_instruction = mysql_schema::getTableAlias('workoutdb','library_exercise_instruction',$p_use_alias);
		$table->library_sport_type = mysql_schema::getTableAlias('workoutdb','library_sport_type',$p_use_alias);
		$table->library_exercise_type = mysql_schema::getTableAlias('workoutdb','library_exercise_type',$p_use_alias);
		$table->library_body_part = mysql_schema::getTableAlias('workoutdb','library_body_part',$p_use_alias);
		$table->library_exercise_media = mysql_schema::getTableAlias('workoutdb','library_exercise_media',$p_use_alias);
		
		// print_r($table);
		
		$library_exercise = new stdClass();
		$library_exercise->id = null;
		
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
			
			if ( is_null($library_exercise->id) ) {
				$library_exercise = clone $row->{$table->library_exercise};
				unset($library_exercise->json_instructions);
				// initialize the deletable switch
				$library_exercise->deletable = true;
				$library_exercise_deletable = &$library_exercise->deletable;
				// initialize the exercise_level node
				$library_exercise->{$table->library_exercise_level} = new stdClass();
				$library_exercise_level = &$library_exercise->{$table->library_exercise_level};
				// initialize the equipment array
				$library_exercise->{$table->library_equipment} = array();
				$library_equipment = &$library_exercise->{$table->library_equipment};
				$eq = -1;
				// initialize the media array (hardcode the alias to media)
				$library_exercise->media = array();
				$library_exercise_media = &$library_exercise->media;
				$md = -1;
				// initialize the instruction array
				$library_exercise->{$table->library_exercise_instruction} = array();
				$library_exercise_instruction = &$library_exercise->{$table->library_exercise_instruction};
				$in = -1;
				// initialize the sport type array
				$library_exercise->{$table->library_sport_type} = array();
				$library_sport_type = &$library_exercise->{$table->library_sport_type};
				$sp = -1;
				// initialize the exercise type array
				$library_exercise->{$table->library_exercise_type} = array();
				$library_exercise_type = &$library_exercise->{$table->library_exercise_type};
				$et = -1;
				// initialize the body part array
				$library_exercise->{$table->library_body_part} = array();
				$library_body_part = &$library_exercise->{$table->library_body_part};
				$bp = -1;
			}
			if ( !is_null($row->library_exercise_cnt->deletable) ) {
				$library_exercise_deletable = $row->library_exercise_cnt->deletable;
			}
			if ( !is_null($row->library_exercise_level->id) ) {
				$library_exercise_level = clone $row->library_exercise_level;
			}
			if ( !is_null($row->library_equipment->id) ) {
				if ( $eq < 0 || $library_equipment->id != $row->library_equipment->id ) {
					++$eq;
					$library_equipment[$eq] = clone $row->library_equipment;
					$library_equipment[$eq]->media = format_object_with_id($row->library_equipment_media_last_entered);
					// initialize the measurement array
					$library_equipment[$eq]->{$table->library_measurement} = array();
					$library_measurement = &$library_equipment[$eq]->{$table->library_measurement};
					$m = -1;
				}
			}
			if ( !is_null($row->library_measurement->id) ) {
				if ( $m < 0 || $library_measurement[$m]->id != $row->library_measurement->id ) {
					++$m;
					$library_measurement[$m] = clone $row->library_measurement;
				}
			}
			if ( !is_null($row->library_exercise_media->id) ) {
				if ( $md < 0 || $library_exercise_media[$md]->id != $row->library_exercise_media->id ) {
					++$md;
					$library_exercise_media[$md] = clone $row->library_exercise_media;
				}
			}
			if ( !is_null($row->library_exercise_instruction->id) ) {
				if ( $in < 0 || $library_exercise_instruction[$in]->id != $row->library_exercise_instruction->id ) {
					++$in;
					$library_exercise_instruction[$in] = clone $row->library_exercise_instruction;
					$library_exercise_instruction[$in]->media = format_object_with_id($row->library_exercise_instruction_media);
				}
			}
			if ( !is_null($row->library_sport_type->id) ) {
				if ( $sp < 0 || $library_sport_type[$sp]->id != $row->library_sport_type->id ) {
					++$sp;
					$library_sport_type[$sp] = clone $row->library_sport_type;
				}
			}
			if ( !is_null($row->library_exercise_type->id) ) {
				if ( $et < 0 || $library_exercise_type[$et]->id != $row->library_exercise_type->id ) {
					++$et;
					$library_exercise_type[$et] = clone $row->library_exercise_type;
				}
			}
			if ( !is_null($row->library_body_part->id) ) {
				if ( $bp < 0 || $library_body_part[$bp]->id != $row->library_body_part->id ) {
					++$bp;
					$library_body_part[$bp] = clone $row->library_body_part;
				}
			}
		}
		
		return $this->return_handler->results(200,"",$library_exercise);
	}

	// ==================================================================================================================
	// get a search list used for a searchable drop down menu
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
			$search_check  = "WHERE library_exercise.name LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
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
		$sql  = "SELECT library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$sql .= "library_exercise_media_last_entered.id 'library_exercise_media_last_entered.id', library_exercise_media_last_entered.media_url 'library_exercise_media_last_entered.media_url' ";
		$sql .= "FROM library_exercise ";
		$sql .= "LEFT OUTER JOIN library_exercise_media_last_entered ";
		$sql .= "ON library_exercise_media_last_entered.library_exercise_id = library_exercise.id ";
		$sql .= $search_check;
		$sql .= "ORDER BY library_exercise.name asc ";
		$sql .= $limit;

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$library_exercise = array();
		$e = -1;
		
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias=false);
         	// echo json_encode($row) . "<br /><br />\n\n";
         	
			++$e;
			$library_exercise[$e] = clone $row->library_exercise;
			$library_exercise[$e]->media = format_object_with_id($row->library_exercise_media_last_entered);
		}
		
		return $this->return_handler->results(200,"",$library_exercise);
	}

	// ==================================================================================================================
	// create
	// ==================================================================================================================
	
	public function create( $data ) {
		// echo "data:"; print_r($data);
		// post the entry
		$return = $this->perform('table_workoutdb_library_exercise->insert',$data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$this->id = $return['response']->id;
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries for sport type
		if ( isset($data->sport_type) && !empty($data->sport_type) ) {
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_exercise';
			$this->xref_table_name = 'library_exercise_sport_type';
			$this->xrefed_table_name = 'library_sport_type';
			//echo "xref_table_name: " . $this->xref_table_name . " xrefed_table_name: " . $this->xrefed_table_name . "<br />";
			$return = $this->perform('this->post_xref_list',$data->sport_type);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries for exercise type
		if ( isset($data->exercise_type) && !empty($data->exercise_type) ) {
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_exercise';
			$this->xref_table_name = 'library_exercise_exercise_type';
			$this->xrefed_table_name = 'library_exercise_type';
			//echo "xref_table_name: " . $this->xref_table_name . " xrefed_table_name: " . $this->xrefed_table_name . "<br />";
			$return = $this->perform('this->post_xref_list',$data->exercise_type);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries for body part
		if ( isset($data->body_part) && !empty($data->body_part) ) {
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_exercise';
			$this->xref_table_name = 'library_exercise_body_part';
			$this->xrefed_table_name = 'library_body_part';
			//echo "xref_table_name: " . $this->xref_table_name . " xrefed_table_name: " . $this->xrefed_table_name . "<br />";
			$return = $this->perform('this->post_xref_list',$data->body_part);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries for equipment
		if ( isset($data->equipment) && count($data->equipment) > 0 ) {
			// convert the deletable field to the table's mandatory field
			foreach( $data->equipment as &$equipment) {
				if ( $equipment->deletable ) {
					$equipment->mandatory = FALSE;
				} else {
					$equipment->mandatory = TRUE;
				}
			}
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_exercise';
			$this->xref_table_name = 'library_exercise_equipment';
			$this->xrefed_table_name = 'library_equipment';
			//echo "xref_table_name: " . $this->xref_table_name . " xrefed_table_name: " . $this->xrefed_table_name . "<br />";
			$return = $this->perform('this->post_pass_thru_xref_list',$data->equipment);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries and entries for media
		if ( isset($data->media) && is_array($data->media) && count($data->media) > 0 ) {
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_exercise';
			$this->xref_table_name = 'library_exercise_exercise_media';
			$this->xrefed_table_name = 'library_exercise_media';
			// echo "xref_table_name: " . $this->xref_table_name . " xrefed_table_name: " . $this->xrefed_table_name . "<br />";
			// echo "media:"; print_r($data->media); echo "<br />";
			$return = $this->perform('this->post_xrefed_entries',$data->media);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the pass through cross reference instruction entries and entries for media
		if ( isset($data->instruction) && is_array($data->instruction) && count($data->instruction) > 0 ) {
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_exercise';
			$this->xref_table_name = 'library_exercise_instruction';
			$this->xrefed_table_name = 'library_exercise_media';
			$this->order_field = 'json_instructions';
			// echo "xref_table_name: " . $this->xref_table_name . " xrefed_table_name: " . $this->xrefed_table_name . " order_field:" . $this->order_field . "<br />";
			// echo "instruction:"; print_r($data->instruction); echo "<br />";
			$return = $this->perform('this->post_ordered_pass_thru_xref_entries',$data->instruction);
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

	public function update( $data ) {
		// put the entry
		$return = $this->perform('table_workoutdb_library_exercise->update',$data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// get the list of sport type for this exercise
		$sport_type_list = array();
		if ( isset($data->sport_type) && !empty($data->sport_type) ) {
			$sport_type_list = $data->sport_type;
		}
		$this->id = $data->id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->xref_table_name = 'library_exercise_sport_type';
		$this->xrefed_table_name = 'library_sport_type';
		// process the put list
		$return = $this->perform('this->put_xref_list',$sport_type_list);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// get the list of exercise type for this exercise
		$exercise_type_list = array();
		if ( isset($data->exercise_type) && !empty($data->exercise_type) ) {
			$exercise_type_list = $data->exercise_type;
		}
		$this->id = $data->id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->xref_table_name = 'library_exercise_exercise_type';
		$this->xrefed_table_name = 'library_exercise_type';
		// process the put list
		$return = $this->perform('this->put_xref_list',$exercise_type_list);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// get the list of body part for this exercise
		$body_part_list = array();
		if ( isset($data->body_part) && !empty($data->body_part) ) {
			$body_part_list = $data->body_part;
		}
		$this->id = $data->id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->xref_table_name = 'library_exercise_body_part';
		$this->xrefed_table_name = 'library_body_part';
		// process the put list
		$return = $this->perform('this->put_xref_list',$body_part_list);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// get the list of equipment for this exercise
		if ( isset($data->equipment) && is_array($data->equipment) ) {
			// convert the deletable field to the table's mandatory field
			foreach( $data->equipment as &$equipment) {
				if ( $equipment->deletable ) {
					$equipment->mandatory = FALSE;
				} else {
					$equipment->mandatory = TRUE;
				}
			}
			$this->id = $data->id;
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_exercise';
			$this->xref_table_name = 'library_exercise_equipment';
			$this->xrefed_table_name = 'library_equipment';
			
			// process the put list
			$return = $this->perform('this->put_pass_thru_xref_list',$data->equipment);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// get the media xref entry list for this exercise
		$media_list = array();
		if ( isset($data->media) && !empty($data->media) ) {
			$media_list = $data->media;
		}
		$this->id = $data->id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->xref_table_name = 'library_exercise_exercise_media';
		$this->xrefed_table_name = 'library_exercise_media';
		// process the put list
		$return = $this->perform('this->put_xref_entries',$media_list);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// get the instruction pass through media entries for this exercise
		$instruction_list = array();
		if ( isset($data->instruction) && !empty($data->instruction) && is_array($data->instruction) ) {
			// create the new instrction list from the $data->instruction array
			foreach ( $data->instruction as $instruction ) {
				$new = new stdClass();
				$new->id = null;
				if ( property_exists($instruction,'id') && !is_null($instruction->id) ) {
					$new->id = $instruction->id;
				}
				$new->library_exercise_id = $data->id;
				$new->library_exercise_media = new stdClass();
				if ( property_exists($instruction,'media') && is_object($instruction->media) && 
				     property_exists($instruction->media,'url') && !is_null($instruction->media->url) ) {
					$new->library_exercise_media->id = null;
					$new->library_exercise_media->url = $instruction->media->url;
					if ( property_exists($instruction->media,'id') && !is_null($instruction->media->id) ) {
						$new->library_exercise_media->id = $instruction->media->id;
					}
				}
				$new->description = null;
				if ( property_exists($instruction,'description') ) {
					$new->description = $instruction->description;
				}
				$instruction_list[] = clone $new;
				unset($new);
			}
		}
		$this->id = $data->id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->xref_table_name = 'library_exercise_instruction';
		$this->xrefed_table_name = 'library_exercise_media';
		$this->order_field = 'json_instructions';
		// process the put list
		$return = $this->perform('this->put_ordered_pass_thru_xref_entries',$instruction_list);
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
		//  delete the exercise's sport types
		$this->id = $p_id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->linked_table_name = 'library_exercise_sport_type';
		$return = $this->perform('this->delete_linked_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		//  delete the exercise's exercise types
		$this->id = $p_id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->linked_table_name = 'library_exercise_exercise_type';
		$return = $this->perform('this->delete_linked_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		//  delete the exercise's body parts
		$this->id = $p_id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->linked_table_name = 'library_exercise_body_part';
		$return = $this->perform('this->delete_linked_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		//  delete the exercise's equipment
		$this->id = $p_id;
		$this->linked_table_name = 'library_exercise_equipment';
		$return = $this->perform('this->delete_linked_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		//  delete the exercise's media
		$this->id = $p_id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->xref_table_name = 'library_exercise_exercise_media';
		$this->xrefed_table_name = 'library_exercise_media';
		$return = $this->perform('this->delete_xrefed_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		//  delete the exercise's instructions
		$this->id = $p_id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_exercise';
		$this->xref_table_name = 'library_exercise_instruction';
		$this->xrefed_table_name = 'library_exercise_media';
		$return = $this->perform('this->delete_xrefed_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// delete the exercise
		return $this->perform('table_workoutdb_library_exercise->delete',$p_id);
	}
}