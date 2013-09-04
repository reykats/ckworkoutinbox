<?php

class table_workoutdb_location_media extends mysql_table {

	public function __construct() {
		parent::__construct();
		
		$this->initializeTable('workoutdb','location_media');
	}

	public function insert( $p_fields ) {
		$fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------------------
		// move and resize an image from the temp directory to its perminate directory
		// -------------------------------------------------------------------------------------------------------------
		$this->load->model('file/file_upload');
		$return = $this->file_upload->copy_temp_to_dir_resize_image($fields->url,'location');
		if ( $return['status'] >= 300 ) {
			$return['response']->id = null;
			return $return;
		}
		// -------------------------------------------------------------------------------------------------------------
		// Create the Media Entry
		// -------------------------------------------------------------------------------------------------------------
		return $this->insertTableFields($fields);
	}

	public function delete( $p_id = null ) {
		// -------------------------------------------------------------------------------------------------------------
		// Has the id been provided?
		// -------------------------------------------------------------------------------------------------------------
		if ( is_null($p_id) || empty($p_id) || !is_numeric($p_id) ) {
			return $this->return_handler->results(400,"ID not provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Is this a valid ID?
		// -------------------------------------------------------------------------------------------------------------
		$query = $this->db->get_where($this->table_name,array('id' => $p_id));
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(202,"ID alread deleted",new stdClass());
		}
		$filename = $return['response']->media_url;
		$this->load->model('file/file_upload');
		$return = $this->file_upload->delete('location',$filename);
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