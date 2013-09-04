<?php
class file_upload extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	public function upload_dir_image($type,$crop) {
		$response = new stdClass();
		$response->media = null;
		$files_idx = null;
		if ( isset($_FILES) || is_null($_FILES) ) {
			if ( isset($_FILES['file']) ) {
				$files_idx = 'file';
			} else if ( isset($_FILES['Filedata']) ) {
				$files_idx = 'Filedata';
			} else if ( isset($_FILES['userfile']) ) {
				$files_idx = 'userfile';
			} else {
				return $this->return_handler->results(400,"Invalid _FILE index",$response);
			}
		} else {
			return $this->return_handler->results(400,"No file Selected",$response);
		}
		if (!empty($_FILES[$files_idx])) {
			// $response->Filedata = $_FILES[$files_idx];
			// get the file extension
			$ext = substr(strrchr($_FILES[$files_idx]['name'], '.'), 1);
			$filename = uniqid('IMG_') . '.' . $ext;
			$response->filename = $filename;
			if ( in_array(strtolower($ext), array("jpg","jpeg","png","gif")) ) {
				$original_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/original/' . $filename;

				$tempFile = $_FILES[$files_idx]['tmp_name'];
				move_uploaded_file($tempFile,$original_filename);

				// load the image model
				$this->load->model('file/file_image');
				// resize the master image to our max size and store it.  We only want to store file at a max of this size.
				$return = $this->file_image->copy_resize($original_filename);
				if ( $return['status'] > 299 ) {
					return $return;
				}
				// copy and resize the image in memory to type directory and resize
				$return = $this->copy_resize_image($filename,$type,$crop);
				if ( $return['status'] > 299 ) {
					return $return;
				}
				return $this->return_handler->results(200,"File uploaded to the server",$response);
			} else {
				return $this->return_handler->results(400,"Invalid file extension",$response);
			}
		} else {
			return $this->return_handler->results(400,"No file Selected",$response);
		}
	}

	public function upload_temp_image() {
		$response = new stdClass();
		$response->media = null;
		$files_idx = null;
		if ( isset($_FILES) || is_null($_FILES) ) {
			if ( isset($_FILES['file']) ) {
				$files_idx = 'file';
			} else if ( isset($_FILES['Filedata']) ) {
				$files_idx = 'Filedata';
			} else if ( isset($_FILES['userfile']) ) {
				$files_idx = 'userfile';
			} else {
				return $this->return_handler->results(400,"Invalid _FILE index",$response);
			}
		} else {
			return $this->return_handler->results(400,"No file Selected",$response);
		}
		if (!empty($_FILES[$files_idx])) {
			// $response->Filedata = $_FILES[$files_idx];
			$crop = true;
			// get the file extension
			$ext = substr(strrchr($_FILES[$files_idx]['name'], '.'), 1);
			$filename = uniqid('IMG_') . '.' . $ext;
			$response->filename = $filename;
			if ( in_array(strtolower($ext), array("jpg","jpeg","png","gif")) ) {
				$targetPath = $this->config->item('workoutinbox_client_data') . '/temp/image/original/';
				$master_filename =  str_replace('//','/',$targetPath) . $filename;

				$targetPath = $this->config->item('workoutinbox_client_data') . '/temp/image/small/';
				$small_filename =  str_replace('//','/',$targetPath) . $filename;

				$tempFile = $_FILES[$files_idx]['tmp_name'];
				move_uploaded_file($tempFile,$master_filename);

				// load the image model
				$this->load->model('file/file_image');
				// resize the master image to our max size and store it.  We only want to store file at a max of this size.
				$return = $this->file_image->copy_resize($master_filename);
				if ( $return['status'] > 299 ) {
					return $return;
				}

				// Get the image information
				$image_info = array();
				$image_info = getimagesize($master_filename);
				// Get proportion
				$proportion = $image_info[1] / $image_info[0];

				// create small version of the file
				if ( $crop ) {
					$return = $this->file_image->copy_resize(null,$width=300,$height=300,$small_filename,true);
				} else {
					$return = $this->file_image->copy_resize(null,$width=300,300 * $proportion,$small_filename,false);
				}
				if ( $return['status'] > 299 ) {
					return $return;
				}
				// clear the master image from memory
				$return = $this->file_image->clear_memory();
				if ( $return['status'] > 299 ) {
					return $return;
				}
				return $this->return_handler->results(200,"File uploaded to the server",$response);
			} else {
				return $this->return_handler->results(400,"Invalid file extension",$response);
			}
		} else {
			return $this->return_handler->results(400,"No file Selected",$response);
		}
	}

	public function delete_temp_image($filename) {
		$targetPath = $this->config->item('workoutinbox_client_data') . '/temp/image/original/';
		$master_filename =  str_replace('//','/',$targetPath) . $filename;
		if ( file_exists($master_filename) ) {
			unlink($master_filename);
		}

		$targetPath = $this->config->item('workoutinbox_client_data') . '/temp/image/small/';
		$small_filename =  str_replace('//','/',$targetPath) . $filename;
		if ( file_exists($small_filename) ) {
			unlink($small_filename);
		}

		return $this->return_handler->results(202,"Entry deleted.",new stdClass());
	}

	public function copy_temp_to_dir_resize_image($filename,$type,$crop=false) {
		$master_filename = $this->config->item('workoutinbox_client_data') . '/temp/image/original/' . $filename;

		// load the image model
		$this->load->model('file/file_image');
		// resize the master image to our max size and store it.  We only want to store file at a max of this size.
		$return = $this->file_image->load_master($master_filename);
		if ( $return['status'] > 299 ) {
			return $return;
		}
		// copy and resize the image in memory to type directory and resize
		return $this->copy_resize_image($filename,$type,$crop);
	}

	public function copy_dir_to_dir_resize_image($filename,$from_type,$to_type,$crop) {
		$master_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $from_type . '/image/original/' . $filename;

		// load the image model
		$this->load->model('file/file_image');
		// resize the master image to our max size and store it.  We only want to store file at a max of this size.
		$return = $this->file_image->load_master($master_filename);
		if ( $return['status'] > 299 ) {
			return $return;
		}
		// copy and resize the image in memory to type directory and resize
		return $this->copy_resize_image($filename,$to_type,$crop);
	}

	public function copy_resize_image($filename,$type,$crop) {
		// The image must already loaded into memory!
		//
		$original_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/original/' . $filename;
		$large_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/large/' . $filename;
		$medium_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/medium/' . $filename;
		$small_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/small/' . $filename;
		$icon_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/icon/' . $filename;

		// copy the file to the original folder
		$return = $this->file_image->copy_resize(null,$width=false,$height=false,$original_filename);
		if ( $return['status'] > 299 ) {
			return $return;
		}

		// Get the image information
		$image_info = array();
		$image_info = getimagesize($original_filename);
		// Get proportion
		$proportion = $image_info[1] / $image_info[0];

		// create large version of the file
		if ( $crop ) {
			$return = $this->file_image->copy_resize(null,$width=700,$height=700,$large_filename,true);
		} else {
			$return = $this->file_image->copy_resize(null,$width=700,$height=700 * $proportion,$large_filename,false);
		}
		if ( $return['status'] > 299 ) {
			return $return;
		}
		// create medium version of the file
		if ( $crop ) {
			$return = $this->file_image->copy_resize(null,$width=400,$height=400,$medium_filename,true);
		} else {
			$return = $this->file_image->copy_resize(null,$width=400,$height=400 * $proportion,$medium_filename,false);
		}
		if ( $return['status'] > 299 ) {
			return $return;
		}
		// create small version of the file
		if ( $crop ) {
			$return = $this->file_image->copy_resize(null,$width=100,$height=100,$small_filename,true);
		} else {
			$return = $this->file_image->copy_resize(null,$width=100,$height=100 * $proportion,$small_filename,false);
		}
		if ( $return['status'] > 299 ) {
			return $return;
		}
		// create icon version of the file
		if ( $crop ) {
			$return = $this->file_image->copy_resize(null,$width=50,$height=50,$icon_filename,true);
		} else {
			$return = $this->file_image->copy_resize(null,$width=50,$height=50 * $proportion,$icon_filename,false);
		}
		if ( $return['status'] > 299 ) {
			return $return;
		}
		// clear the master image from memory
		$return = $this->file_image->clear_memory();
		if ( $return['status'] > 299 ) {
			return $return;
		}

		return $this->return_handler->results(200,"File saved",new stdClass());
	}

	public function rotate($params) {
		//print_r($params);
		$type = array_shift($params);
		$filename = array_shift($params);
		$degrees = array_shift($params);
		// put the filenames of the file's many sizes into an array
		$files = array();
		if ( $type == "temp" ) {
			$files[] = $this->config->item('workoutinbox_client_data') . '/' . $type . '/image/original/' . $filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/' . $type . '/image/small/' . $filename;
		} else {
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/original/' . $filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/large/' . $filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/medium/' . $filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/small/' . $filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/icon/' . $filename;
		}
		// load the image model
		$this->load->model('file/file_image');
		foreach ( $files as $file ) {
			// Rotate the image
			$return = $this->file_image->rotate_image($file,$degrees);
			if ( $return['status'] > 299 ) {
				return $return;
			}
		}

		return $this->return_handler->results(200,"Image rotated",new stdClass());
	}

	public function delete($p_type,$p_filename) {
		// put the filenames of the file's many sizes into an array
		$files = array();
		if ( $p_type == "temp" ) {
			$files[] = $this->config->item('workoutinbox_client_data') . '/' . $p_type . '/image/original/' . $p_filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/' . $p_type . '/image/small/' . $p_filename;
		} else {
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $p_type . '/image/original/' . $p_filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $p_type . '/image/large/' . $p_filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $p_type . '/image/medium/' . $p_filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $p_type . '/image/small/' . $p_filename;
			$files[] = $this->config->item('workoutinbox_client_data') . '/media/' . $p_type . '/image/icon/' . $p_filename;
		}
		// delete the file
		foreach ( $files as $file ) {
			if ( file_exists($file) ) {
				unlink($file);
			}
		}

		return $this->return_handler->results(200,"Image Removed",new stdClass());
	}
}