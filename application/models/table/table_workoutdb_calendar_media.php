<?php

class table_workoutdb_calendar_media extends mysql_table {

	public function __construct() {
		parent::__construct();
		
		$this->initializeTable('workoutdb','calendar_media');
	}

	// ==================================================================================================================
	// Upload the calendar_media directily to the client_data folder
	// ==================================================================================================================

	public function uploadCreate( $p_calendar_id, $p_date = null ) {
		// -------------------------------------------------------------------------------------------------------------
		// Upload the image directly to the calendar media folder (skipping the temp media folder)
		// -------------------------------------------------------------------------------------------------------------
		$this->load->model('file/file_upload');
		$return = $this->file_upload->upload_dir_image('calendar',false);
		if ( $return['status'] >= 300 ) {
			$return['response']->id = null;
			return $return;
		}
		$filename = $return['response']->filename;
		// -------------------------------------------------------------------------------------------------------------
		// Create the Calendar Media Entry
		// -------------------------------------------------------------------------------------------------------------
		// Create the entry's object
		$columns = new stdClass();
		$columns->media_url = $filename;
		$columns->calendar_id = $p_calendar_id;
		$columns->date = $p_date;
		// Create the entry
		$return = $this->insertTableFields($columns);
		// -------------------------------------------------------------------------------------------------------------
		// add the filename to the response and if the status is less than 300, set the status to 200
		// -------------------------------------------------------------------------------------------------------------
		$return['response']->filename = $filename;
		if ( $return['status'] < 300 ) {
			$return['status'] = 200;
		}
		return $return;
	}

	// ==================================================================================================================
	// Delete the calendar_media from the client_data folder
	// ==================================================================================================================

	public function delete( $id ) {
		// -------------------------------------------------------------------------------------------------------------
		// Get the Calendar Media Entry
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->getForId($id);
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Entry already deleted.",new stdClass());
		}
		$calendar_media = $return['response'];
		// -------------------------------------------------------------------------------------------------------------
		// Remove the image from the calendar media folder (skipping the temp media folder)
		// -------------------------------------------------------------------------------------------------------------
		$this->load->model('file/file_upload');
		$return = $this->file_upload->delete('calendar',$calendar_media->media_url);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// -------------------------------------------------------------------------------------------------------------
		// Delete the Calendar Media Entry
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->deleteTable($id);

		return $return;
	}

}