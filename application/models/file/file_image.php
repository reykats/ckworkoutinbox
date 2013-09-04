<?php
class file_image extends common_perform {

	public function __construct() {
		parent::__construct();
		$this->load->library('image_moo');
	}

	public function load_master($master_filename) {
		$this->image_moo->load($master_filename);
		if( $this->image_moo->errors ) {
			return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
		}
		return $this->return_handler->results(200,"",new stdClass());
	}

	public function copy_resize($master_filename,$max_width=false,$max_height=false,$save_filename=false,$crop=false) {
		// ----------------------------------------------------------------
		// copy_resize
		//
		// Resize an image and store it to a file
		// ----------------------------------------------------------------
		// master_filename   - The fully qualified file name of the file to be copied and resized.
		//                     If empty, the image currently in memory will be used.
		// max_width         - The maximum width the image will be resized to.
		// max_height        - The maximum height the image will be resized to.
		// save_filename     - The fully qualified file name to save the resized file as.
		// crop              - true/false - crop the image when resizing it.
		// ----------------------------------------------------------------
		// if a file name was provied, load it into memory as the master image
		if ( !empty($master_filename) ) {
			$this->image_moo->load($master_filename);
			if( $this->image_moo->errors ) {
				return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
			}
		}
		// if height and width provided, Resize and save the image to a file.
		if ( $max_width && $max_height ) {
			if ( $crop ) {
				$this->image_moo->resize_crop($max_width,$max_height);
			} else {
				$this->image_moo->resize($max_width,$max_height);
			}
			if( $this->image_moo->errors ) {
				return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
			}
		} else if ( $max_width and !$crop ) {
			$this->image_moo->resize($max_width,$max_height);
			if( $this->image_moo->errors ) {
				return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
			}
		} else if ( $max_height and !$crop ) {
			$this->image_moo->resize($max_width,$max_height);
			if( $this->image_moo->errors ) {
				return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
			}
		}
		// Save the image to a file.  Then revert the image in memory to the master image.
		if ( $save_filename ) {
			$this->image_moo->save($save_filename,true)->clear_temp();
			if( $this->image_moo->errors ) {
				return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
			}
		} else {
			$this->image_moo->save_pa('','',true)->clear_temp();
			if( $this->image_moo->errors ) {
				return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
			}
		}
		return $this->return_handler->results(200,"",new stdClass());
	}

	public function rotate($degrees) {
		// ----------------------------------------------------------------
		// rotate
		//
		// rotate the master image the desired number of degrees
		// ----------------------------------------------------------------
		$this->image_moo->set_background_colour("#111")->rotate($degrees);
		if( $this->image_moo->errors ) {
			return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
		}
		return $this->return_handler->results(200,"",new stdClass());
	}

	public function rotate_image($filename,$degrees) {
		// resize the master image to our max size and store it.  We only want to store file at a max of this size.
		// echo "load master : $filename<br />";
		$return = $this->load_master($filename);
		if ( $return['status'] > 299 ) {
			return $return;
		}
		// rotate the master image
		// echo "rotate : $degrees<br />";
		$return = $this->rotate($degrees);
		if ( $return['status'] > 299 ) {
			return $return;
		}
		// echo "save changes<br />";
		$this->image_moo->save_pa('','',true)->clear_temp();
		if( $this->image_moo->errors ) {
			return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
		}
		// clear the master image from memory
		$return = $this->clear_memory();
		if ( $return['status'] > 299 ) {
			return $return;
		}
	}

	public function clear_memory() {
		// ----------------------------------------------------------------
		// clear
		//
		// Clear all images from memory
		// ----------------------------------------------------------------
		// clear all images from memory
		$this->image_moo->clear();
		if( $this->image_moo->errors ) {
			return $this->return_handler->results(400,$this->image_moo->display_errors(),new stdClass());
		}
		return $this->return_handler->results(200,"",new stdClass());
	}

	public function view_type($size=null,$type=null,$filename=null) {
		// ----------------------------------------------------------------
		// view
		//
		// view an image file
		// ----------------------------------------------------------------
		// Validate mandatory parameters
		if ( empty($filename) || empty($type) || empty($size) ) {
			return $this->return_handler->results(400,'filename, type, and size must be passed',new stdClass());
		}
		
		// get the name for the size id
		$return = $this->perform("table_workoutdb_image_size->getForId",$size);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$size_name = $return["response"]->name;
		
		if ( $type == 'temp' ) {
			$master_filename = $this->config->item('workoutinbox_client_data') . '/temp/image/' . $size_name . '/' . $filename;
		} else {
			$master_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/' . $size_name . '/' . $filename;
		}
		 
		if ( !file_exists($master_filename) ) {
			return $this->return_handler->results(400,$master_filename . " does not exists",new stdClass());
		}
		
		$ext = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
		switch ($ext)
		{
			case "GIF"  :
				$this->output->set_content_type("image/gif")->set_output(file_get_contents($master_filename));
				break;
			case "JPG" :
			case "JPEG" :
				$this->output->set_content_type("image/jpeg");
				$this->output->set_output(file_get_contents($master_filename));
				
				break;
			case "PNG" :
				$this->output->set_content_type("image/png")->set_output(file_get_contents($master_filename));
				break;
			default :
				return $this->return_handler->results(400,$filename . " invalid file extension.",new stdClass());
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}

	public function view($params = array()) {
		// ----------------------------------------------------------------
		// view
		//
		// view an image file
		// ----------------------------------------------------------------
		// print_r($params);
		$type = array_shift($params);
		$filename = array_shift($params);
		$size = array_shift($params);
		//echo "$type $filename $size<br />";
		if ( isset($_GET['filename']) && !empty($_GET['filename']) &&
		     isset($_GET['type']) && !empty($_GET['type']) &&
			 isset($_GET['size']) && !empty($_GET['size']) ) {
			$filename = $_GET['filename'];
			$type = $_GET['type'];
			$size = $_GET['size'];
		}
			 
		if ( empty($filename) || empty($type) || empty($size) ) {
			return $this->return_handler->results(400,'filename, type, and size must be passed',new stdClass());
		}
		
		if ( $type == 'temp' ) {
			$master_filename = $this->config->item('workoutinbox_client_data') . '/temp/image/' . $size . '/' . $filename;
		} else {
			$master_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/' . $size . '/' . $filename;
		}
		
		// echo "master_filename:$master_filename<br />";
		 
		if ( !file_exists($master_filename) ) {
			return $this->return_handler->results(400,$master_filename . " does not exists",new stdClass());
		}
		
		$ext = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
		switch ($ext)
		{
			case "GIF"  :
				$this->output->set_content_type("image/gif")->set_output(file_get_contents($master_filename));
				break;
			case "JPG" :
			case "JPEG" :
				$this->output->set_content_type("image/jpeg");
				$this->output->set_output(file_get_contents($master_filename));
				
				break;
			case "PNG" :
				$this->output->set_content_type("image/png")->set_output(file_get_contents($master_filename));
				break;
			default :
				return $this->return_handler->results(400,$filename . " invalid file extension.",new stdClass());
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}

	public function imageInfo($params = array()) {
		// ----------------------------------------------------------------
		// view
		//
		// view an image file
		// ----------------------------------------------------------------
		//print_r($params);
		$type = array_shift($params);
		$filename = array_shift($params);
		$size = array_shift($params);
		//echo "$type $filename $size<br />";

		if ( $type == 'temp' ) {
			$master_filename = $this->config->item('workoutinbox_client_data') . '/temp/image/' . $size . '/' . $filename;
		} else {
			$master_filename = $this->config->item('workoutinbox_client_data') . '/media/' . $type . '/image/' . $size . '/' . $filename;
		}

		if ( file_exists($master_filename) ) {
			//echo $master_filename;

			// Get the image information
			$image_info = array();
			$image_info = getimagesize($master_filename);
			// print_r($image_info);

			return $this->return_handler->results(200,"",$image_info);
		} else {
			return $this->return_handler->results(400,'Could not find the file you requested',array());
		}

	}
}