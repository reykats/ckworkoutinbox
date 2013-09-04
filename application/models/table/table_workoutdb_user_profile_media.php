<?php

class table_workoutdb_user_profile_media extends mysql_table {

	public function __construct() {
		parent::__construct();
		
		$this->initializeTable('workoutdb','user_profile_media');
	}

	public function insert( $p_fields ) {
		$fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------------------
		// move and resize an image from the temp directory to its perminate directory
		// -------------------------------------------------------------------------------------------------------------
		$this->load->model('file/file_upload');
		$return = $this->file_upload->copy_temp_to_dir_resize_image($fields->url,'profile',$crop=true);
		if ( $return['status'] >= 300 ) {
			$return['response']->id = null;
			return $return;
		}
		// -------------------------------------------------------------------------------------------------------------
		// Create the Calendar Media Entry
		// -------------------------------------------------------------------------------------------------------------
		// Create the entry
		$return = $this->insertTableFields($fields);
		return $return;
	}

}