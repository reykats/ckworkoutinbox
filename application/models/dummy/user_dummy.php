<?php

class user_dummy extends CI_Model {
	
	protected $support_email;
	
	public function __construct() {
		parent::__construct();
		
		// Setup the support email address
		$this->support_email = "support" . '@' . "workoutinbox.com";
		
		$this->database_name = 'workoutdb';
		$this->table_name = 'user';
	}
	
	public function image() {
		// load the image model
		$this->load->model('file/image');
		
		$master_filename = '../../../scripts/library/dummy.jpg';
		// resize the master image to our max size and store it.  We only want to store file at a max of this size.
		$this->image->load_master($master_filename);
		
		$sql  = "SELECT * ";
		$sql .= "FROM user_profile_media ";

		// echo "$sql<br />";
				
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			$count = 0;
			foreach ( $rows as $row ) {
				$count++;
				
				$filename = $row->media_url;
				
				$profile_folder = $this->config->item('workoutinbox_client_data') . '/media/profile';
				$original_filename = $profile_folder . '/image/original/' . $filename;
				$large_filename = $profile_folder . '/image/large/' . $filename;
				$medium_filename = $profile_folder . '/image/medium/' . $filename;
				$small_filename = $profile_folder . './image/small/' . $filename;
				$icon_filename = $profile_folder . '/image/icon/' . $filename;
				
				unlink($original_filename);
				unlink($large_filename);
				unlink($medium_filename);
				unlink($small_filename);
				unlink($icon_filename);
				
				$this->image->copy_resize(null,$width=false,$height=false,$original_filename);
				$this->image->copy_resize(null,$width=700,$height=700,$large_filename);
				$this->image->copy_resize(null,$width=400,$height=400,$medium_filename);
				$this->image->copy_resize(null,$width=100,$height=100,$small_filename);
				$this->image->copy_resize(null,$width=50,$height=50,$icon_filename);
			}
			// echo "count: $count<br />";
		}
	}

	public function user() {
		$sql  = "SELECT * ";
		$sql .= "FROM user ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			$count = 0;
			foreach ( $rows as $row ) {
				$count++;
				if ( $row->email != $this->support_email ) {
					// Create a number for the User based on thier user id
					$number = substr('00000' . $row->id, -6);
					
					// Create an populate the update object
					$update = new stdClass();
					
					$update->first_name = 'first' . $number;
					$update->last_name = 'last' . $number;
					if ( !is_null($row->address) && !empty($row->address) ) {
						$update->address = $number .' A Street';
					}
					
					$update->email = 'email' . $number . '@domain.com';
					if ( !is_null($row->phone) && !empty($row->phone) ) {
						$update->phone = '4080' . $number;
					}
					
					if ( !is_null($row->password) && !empty($row->password) ) {
						$update->password = md5('mailfininc');
					}
					
					// Put the entry
					$this->db->update('user',$update,array('id' => $row->id));
					
					// Remove the update object from memory
					unset($update);
				} else {
					
					// Create and populate the update object
					$update = new stdClass();
					
					$update->email = $this->support_email;
					$update->password = md5('mailfininc');
					
					// Put the entry
					$this->db->update('user',$update,array('id' => $row->id));
					
					// Remove the update object from memory
					unset($update);
				}
			}
			// echo "count: $count<br />";
		}
	}
}