<?php

class table_workoutdb_library_exercise_media extends mysql_table {

	public function __construct() {
		parent::__construct();
		
		$this->initializeTable('workoutdb','library_exercise_media');
	}

	public function insert( $p_fields ) {
		$fields = (object) $p_fields;
		if ( property_exists($fields,'url') && !is_null($fields->url) ) {
			// -------------------------------------------------------------------------------------------------------------
			// move and resize an image from the temp directory to its perminate directory
			// -------------------------------------------------------------------------------------------------------------
			$this->load->model('file/file_upload');
			$return = $this->file_upload->copy_temp_to_dir_resize_image($fields->url,'exercise');
			if ( $return['status'] >= 300 ) {
				$return['response']->id = null;
				return $return;
			}
			// -------------------------------------------------------------------------------------------------------------
			// Create the Media Entry
			// -------------------------------------------------------------------------------------------------------------
			return $this->insertTableFields($fields);
		}
		$response = new stdClass();
		$response->id = null;
		return $this->return_handler->results(200,"Nothing can be inserted",$response);
	}
	
	public function update( $p_fields ) {
		return $this->return_handler->results(200,"Nothing can be updated",new stdClass());
	}

	public function delete( $p_id = null ) {
		// -------------------------------------------------------------------------------------------------------------
		// Has the id been provided?
		// -------------------------------------------------------------------------------------------------------------
		if ( is_null($p_id) || empty($p_id) || !is_numeric($p_id) ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " ID not provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Is this a valid ID?
		// -------------------------------------------------------------------------------------------------------------
		$query = $this->db->get_where($this->table_name,array('id' => $p_id));
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(202,$this->database_name . ":" . $this->table_name . " ID alread deleted",new stdClass());
		}
		$row = $query->row();
		$filename = $row->media_url;
		$this->load->model('file/file_upload');
		$return = $this->file_upload->delete('exercise',$filename);
		if ( $return['status'] >= 300 ) {
			$return['response']->id = null;
			return $return;
		}
		// -------------------------------------------------------------------------------------------------------------
		// delete the Media entry
		// -------------------------------------------------------------------------------------------------------------
		return $this->deleteTable( $p_id );
	}

}