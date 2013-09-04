<?php

class csv_client_user extends csv_generic {

	// Used to translate the csv file's column name into database field names
	protected $column_trans;

	// information about whether a database field is mandatory or unique
	protected $field_info;

	// Column Definitions is a combination of the $columns_trans and $field_info entries.
	// $column_defs are stored in this array in the order in which they apear in the csv file.
	// They are indexed by thier csv file column name.
	protected $column_defs;

	// the $_GET parameters are stored here
	protected $temp_filename = null;
	protected $client_id = null;
	protected $location_id = null;
	protected $file_type = null;

	// The table of parsed, validated, and formatted csv records
	protected $entry_table;

	// row stats
	protected $add = 0;  // Add client_user ( added or modified or do nothing to user )
	protected $change = 0;  // Update client_user or if the client_user exists and the user is updated.
	protected $no_change = 0;  // both client_user and user exist and will not be changed
	protected $error = 0;

	// The csv file temp directory plus $_GET['filename'] minus its extension
	protected $base_filename = '/temp/csv/';

	// The file pointers
	protected $fp_csv_error = null;
	protected $fp_csv_add = null;
	protected $fp_csv_no_change = null;
	protected $fp_csv_change = null;
	protected $fp_trans = null;
	protected $fp_trans_error = null;

	// variables stored are stored here so they do not have to be passed as method parameters
	protected $table_name;
	protected $unique_list;
	protected $header = array();

	public function __construct() {
		parent::__construct();
		
		// add the client_data folder to the base_filename
		$this->base_filename = $this->config->item('workoutinbox_client_data') . $this->base_filename;

		// how to translate column names to field name in a workoutinbox client_user import file
		$this->column_trans['workoutinbox']['firstname']['field'] = "first_name";
		$this->column_trans['workoutinbox']['firstname']['format'] = "name";
		$this->column_trans['workoutinbox']['lastname']['field'] = "last_name";
		$this->column_trans['workoutinbox']['lastname']['format'] = "name";
		$this->column_trans['workoutinbox']['gender']['field'] = "gender";
		$this->column_trans['workoutinbox']['gender']['format'] = "gender";
		$this->column_trans['workoutinbox']['birthday']['field'] = "birthday";
		$this->column_trans['workoutinbox']['birthday']['format'] = "date";
		$this->column_trans['workoutinbox']['address']['field'] = "address";
		$this->column_trans['workoutinbox']['address']['format'] = "address";
		$this->column_trans['workoutinbox']['role']['field'] = "role_id";
		$this->column_trans['workoutinbox']['role']['format'] = "role";  // will convert workoutinbox client_user_role names to ids.
		$this->column_trans['workoutinbox']['phone']['field'] = "phone";
		$this->column_trans['workoutinbox']['phone']['format'] = "phone";
		$this->column_trans['workoutinbox']['email']['field'] = "email";
		$this->column_trans['workoutinbox']['email']['format'] = "email";
		$this->column_trans['workoutinbox']['note']['field'] = "note";
		$this->column_trans['workoutinbox']['note']['format'] = "note";

		// how to translate column names to field names in a zen planner membership export file
		$this->column_trans['zen']['First Name']['field'] = "first_name";
		$this->column_trans['zen']['First Name']['format'] = "name";
		$this->column_trans['zen']['Last Name']['field'] = "last_name";
		$this->column_trans['zen']['Last Name']['format'] = "name";
		$this->column_trans['zen']['Gender']['field'] = "gender";
		$this->column_trans['zen']['Gender']['format'] = "gender";
		$this->column_trans['zen']['Birth Date']['field'] = "birthday";
		$this->column_trans['zen']['Birth Date']['format'] = "zen_date";
		$this->column_trans['zen']['Address']['field'] = "address";
		$this->column_trans['zen']['Address']['format'] = "zen_address";
		$this->column_trans['zen']['Status']['field'] = "role_id";
		$this->column_trans['zen']['Status']['format'] = "zen_status"; // will convert zen membership status values to client_user_role ids
		$this->column_trans['zen']['Phone']['field'] = "phone";
		$this->column_trans['zen']['Phone']['format'] = "phone";
		$this->column_trans['zen']['Email']['field'] = "email";
		$this->column_trans['zen']['Email']['format'] = "email";

		// shows whether a field is mandatory or name and whether a field is unique or not
		$this->field_info['first_name']['mandatory'] = false;
		$this->field_info['first_name']['unique'] = false;
		$this->field_info['first_name']['init'] = '';
		$this->field_info['first_name']['lookup'] = null;
		$this->field_info['last_name']['mandatory'] = false;
		$this->field_info['last_name']['unique'] = false;
		$this->field_info['last_name']['init'] = '';
		$this->field_info['last_name']['lookup'] = null;
		$this->field_info['gender']['mandatory'] = false;
		$this->field_info['gender']['unique'] = false;
		$this->field_info['gender']['init'] = null;
		$this->field_info['gender']['lookup'] = null;
		$this->field_info['birthday']['mandatory'] = false;
		$this->field_info['birthday']['unique'] = false;
		$this->field_info['birthday']['init'] = null;
		$this->field_info['birthday']['lookup'] = null;
		$this->field_info['address']['mandatory'] = false;
		$this->field_info['address']['unique'] = false;
		$this->field_info['address']['init'] = null;
		$this->field_info['address']['lookup'] = null;
		$this->field_info['role_id']['mandatory'] = true;
		$this->field_info['role_id']['unique'] = false;
		$this->field_info['role_id']['init'] = null;
		$this->field_info['role_id']['lookup'] = 'client_user_role';
		$this->field_info['phone']['mandatory'] = false;
		$this->field_info['phone']['unique'] = false;
		$this->field_info['phone']['init'] = null;
		$this->field_info['phone']['lookup'] = null;
		$this->field_info['email']['mandatory'] = true;
		$this->field_info['email']['unique'] = true;
		$this->field_info['email']['init'] = null;
		$this->field_info['email']['lookup'] = null;
		$this->field_info['note']['mandatory'] = false;
		$this->field_info['note']['unique'] = false;
		$this->field_info['note']['init'] = null;
		$this->field_info['note']['lookup'] = null;
	}

	public function get( $params ) {
		$action = array_shift($params);

		if ( $action == "preview" ){
			if ( isset($_GET['filename']) && !empty($_GET['filename']) &&
			     isset($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) &&
			     isset($_GET['type']) && !empty($_GET['type']) && ( $_GET['type'] == "workoutinbox" || $_GET['type'] == "zen" ) ) {
			    return $this->perform('this->preview',$_GET['client_id'],$_GET['location_id'],$_GET['filename'],$_GET['type']);
			} else {
				return $this->return_handler->results(400,"valid filename, client_id, and type must be provided.",$response);
			}
		} else if ( $action == "download") {
			return $this->perform('this->download_file',$params);
		} else if ( $action == "import") {
			$data = new stdClass();
			if ( isset($_GET['filename']) && !empty($_GET['filename']) ) {
				$data->filename = $_GET['filename'];
			}
			return $this->perform('this->post',$data);
		} else {
			return $this->return_handler->results(400,"Invalid action",new stdClass());
		}
	}

	public function preview( $p_client_id,$p_location_id,$p_filename,$p_type ) {
		$this->temp_filename = $p_filename;
		$this->client_id = $p_client_id;
		$this->location_id = $p_location_id;
		$this->file_type = $p_type;
		$return = $this->perform('this->extract_file');
		if ( $return['status'] < 300 ) {
			$this->perform('this->create_output_files');
			$response->error = $this->error;
			$response->add = $this->add;
			$response->change = $this->change;
			$response->no_change = $this->no_change;
			return $this->return_handler->results(200,"",$response);
		} else {
			$response->error = $this->error;
			$response->add = $this->add;
			$response->change = $this->change;
			$response->no_change = $this->no_change;
			return $this->return_handler->results($return['status'],$return['message'],$response);
		}
	}

	public function create_output_files() {
		// open and create the header row for each csv file
		$this->perform('this->initialize_csv_files');

		foreach ( $this->entry_table as &$row ) {
			// print_r($row); echo "<br />";
			// initialize the uniqueness error in the row
			$row->uniqueness_errors = '';

			// initialize the user and client_user array for the row
			$user = new stdClass();
			$client_user = new stdClass();

			// get any user with this email address
			$key = array();
			$key['email'] = $row->entry['email']->db_value;
			$this->load->model('mysql/mysql_table');
			$return = $this->perform('table_workoutdb_user->getForAndKeys',$key);
			if ( $return['status'] == 200 ) {
				$user = $return['response'][0];
			}

			// create the data array to be used to add or update the user and client_user entries
			$data = $this->perform('this->get_data',$row->entry);

			// store off the user and client_user id
			if ( property_exists($user,'id') ) {
				// Try to get the client_user
				// get the client_user for the client_id and user_id if it exists
				$key = array();
				$key['client_id'] = $this->client_id;
				$key['user_id'] = $user->id;
				$return = $this->perform('table_workoutdb_client_user->getForAndKeys',$key);
				if ( $return['status'] == 200 ) {
					$client_user = $return['response'][0];
				}
			}

			// figure out what type of transaction entry you have
			if ( !property_exists($client_user,'id') ) {
				// echo "add<br />";
				// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
				// Add Entry
				// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
				$this->add++;

				// put the entry to the csv add file
				$this->perform('this->put_csv_add',$row);

				// add a post client_user to the transaction file
				$data['client_id'] = $this->client_id;
				$data['location_id'] = $this->location_id;
				$this->perform('this->put_trans','action_member','create',$data,$notify_user=false);
			} else {
				// echo "update<br />";
				// how many user fields will be changed
				$update_field_cnt = $this->perform('this->get_update_field_cnt',$row->entry, $user);
				// how many client_user fields will be changed
				$update_field_cnt += $this->perform('this->get_update_field_cnt',$row->entry,$client_user);

				if ( $update_field_cnt == 0 ) {
					// echo "no change<br />";
					// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
					// No Change Entry
					// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
					$this->no_change++;

					// put the entry to the csv no_change file
					$this->perform('this->put_csv_no_change',$row);
				} else {
					// echo "change<br />";
					// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
					// Change Entry
					// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
					$this->change++;

					// put the entry to the csv change file
					$this->perform('this->put_csv_change',$row->entry,$user,$client_user);

					// add a put client_user to the transaction file
					// put requires the client_user id in the data
					$data['id'] = $client_user_id;
					$this->perform('this->put_trans','action_member','update',$data,$notify=false);
				}
			}

			// release the user and client_user from memory
			unset($user);
			unset($client_user);
		}

		$this->perform('this->close_csv_files');
	}

	public function get_data($p_columns) {
		// create an array of the data supplied in the csv file.
		// the data array is to be used as the data field in the transaction file
		$data = array();
		foreach ( $p_columns as $column => $def ) {
			$data[$def->field] = $def->db_value;
		}
		return $data;
	}

	public function get_update_field_cnt($p_new,$p_current) {
		// count how many fields are different between the current and new entry
		$cnt = 0;
		foreach ( $p_new as $column => $def ) {
			if ( array_key_exists($def->field, $p_current) ) {
				if ( $def->db_value != $p_current[$def->field] ) {
					$cnt++;
				}
			}
		}
		return $cnt;
	}

	// ------------------------------------------------------------------------------------------------------------------------------------------
	// create csv preview files
	// ------------------------------------------------------------------------------------------------------------------------------------------

	public function initialize_csv_files() {
		// set the base filename
		$this->set_base_filename();
		// open the csv files used to preview
		$this->open_csv_error();
		$this->open_csv_add();
		$this->open_csv_no_change();
		$this->open_csv_change();

		// open the csv file used to store the POST and PUT client_user transactions
		$this->open_trans();

		// write the header entries to the csv files
		$this->put_csv_error_header();
		$this->put_csv_add_header();
		$this->put_csv_no_change_header();
		$this->put_csv_change_header();

		// write the header entry to the trans file
		$this->put_trans_header();
	}

	public function set_base_filename(){
		// append the file name minus the extension to the csv temp director
		$this->base_filename .= substr($this->temp_filename,0,strrpos($this->temp_filename, '.'));
	}

	public function open_csv_error() {
		$this->fp_csv_error = fopen($this->base_filename . "_error.csv", "w");
	}

	public function open_csv_add() {
		$this->fp_csv_add = fopen($this->base_filename . "_add.csv", "w");
	}

	public function open_csv_no_change() {
		$this->fp_csv_no_change = fopen($this->base_filename . "_no_change.csv", "w");
	}

	public function open_csv_change() {
		$this->fp_csv_change = fopen($this->base_filename . "_change.csv", "w");
	}

	public function put_csv_error_header() {
		$headers = array();
		foreach ( $this->entry_table[0]->entry as $column => $value ) {
			$headers[] = $column;
		}
		$headers[] = "Invalid Field Values";
		$headers[] = "Missing Manditor Values";
		$headers[] = "Uniqueness Errors";

		$put = fputcsv($this->fp_csv_error, $headers);

		unset($headers);
	}

	public function put_csv_add_header() {
		$headers = array();
		foreach ( $this->entry_table[0]->entry as $column => $value ) {
			$headers[] = $column;
		}

		$put = fputcsv($this->fp_csv_add, $headers);

		unset($headers);
	}

	public function put_csv_no_change_header() {
		$headers = array();
		foreach ( $this->entry_table[0]->entry as $column => $value ) {
			$headers[] = $column;
		}

		$put = fputcsv($this->fp_csv_no_change, $headers);

		unset($headers);
	}

	public function put_csv_change_header() {
		$headers = array();
		$headers[] = 'Entry';
		foreach ( $this->entry_table[0]->entry as $column => $value ) {
			$headers[] = $column;
		}

		$put = fputcsv($this->fp_csv_change, $headers);

		unset($headers);
	}

	public function put_csv_error($row) {
		$csv_entry = array();
		foreach ( $row->entry as $column => $entry ) {
			$csv_entry[$column] = $entry->csv_value;
		}
		$csv_entry["Invalid Field Values"] = implode(',',$row->invalid_field_values);
		$csv_entry["Missing Manditor Values"] = implode(',',$row->missing_mandatory_values);
		$csv_entry["Uniqueness Error"] = $row->uniqueness_errors;

		fputcsv($this->fp_csv_error, $csv_entry);

		unset($csv_entry);
	}

	public function put_csv_add($row) {
		$csv_entry = array();
		foreach ( $row->entry as $column => $entry ) {
			$csv_entry[$column] = $entry->csv_value;
		}

		fputcsv($this->fp_csv_add, $csv_entry);

		unset($csv_entry);
	}

	public function put_csv_no_change($row) {
		$csv_entry = array();
		foreach ( $row->entry as $column => $entry ) {
			$csv_entry[$column] = $entry->csv_value;
		}

		fputcsv($this->fp_csv_no_change, $csv_entry);

		unset($csv_entry);
	}

	public function put_csv_change($p_new, $p_user, $p_client_user) {

		$old = array('Entry'=>'OLD');
		$new = array('Entry'=>'NEW');
		foreach ( $p_new as $column => $entry ) {
			if ( array_key_exists($entry->field, $p_user) ) {
				$old[$entry->field] = $p_user[$entry->field];
				if ( $entry->db_value != $p_user[$entry->field] ) {
					$new[$entry->field] = $entry->db_value;
				} else {
					$new[$entry->field] = '';
				}
			} else if ( array_key_exists($entry->field, $p_client_user) ) {
				$old[$entry->field] = $p_client_user[$entry->field];
				if ( $entry->db_value != $p_client_user[$entry->field] ) {
					$new[$entry->field] = $entry->db_value;
				} else {
					$new[$entry->field] = '';
				}
			}
		}

		fputcsv($this->fp_csv_change, $old);
		fputcsv($this->fp_csv_change, $new);

		unset($old);
		unset($new);

	}

	public function close_csv_files() {
		// close the csv files
		$this->close_csv_error();
		$this->close_csv_add();
		$this->close_csv_no_change();
		$this->close_csv_change();
		// close the trans file
		$this->close_trans();
	}

	public function close_csv_error() {
		 fclose($this->fp_csv_error);
	}

	public function close_csv_add() {
		 fclose($this->fp_csv_add);
	}

	public function close_csv_no_change() {
		 fclose($this->fp_csv_no_change);
	}

	public function close_csv_change() {
		 fclose($this->fp_csv_change);
	}

	// ------------------------------------------------------------------------------------------------------------------------------------------
	// create transaction file
	// ------------------------------------------------------------------------------------------------------------------------------------------

	public function open_trans() {
		$this->fp_trans = fopen($this->base_filename . "_trans.csv", "w");
	}

	public function put_trans_header() {
		$headers = array();
		$headers[] = "api";
		$headers[] = "method";
		$headers[] = "data";
		$headers[] = "notify";

		$put = fputcsv($this->fp_trans, $headers);

		unset($headers);
	}

	public function put_trans($p_api,$p_method,$p_data,$p_notify) {
		$entry = array();
		$entry['api'] = $p_api;
		$entry['method'] = $p_method;
		$entry['data'] = json_encode($p_data);
		$entry['notify'] = (bool) $p_notify;

		fputcsv($this->fp_trans, $entry);

		unset($entry);
	}

	public function close_trans() {
		 fclose($this->fp_trans);
	}

	// ------------------------------------------------------------------------------------------------------------------------------------------
	// download a file
	// ------------------------------------------------------------------------------------------------------------------------------------------

	public function download_file( $params ) {
		// what file type do yuou want to download
		$type = array_shift($params);

		if ( isset($type) && !empty($type) ) {
			if ( in_array($type,array("error","add","change","no_change","trans","trans_error")) ) {
				if ( isset($_GET['filename']) && !empty($_GET['filename']) ) {
					$this->temp_filename = $_GET['filename'];
					// setup the download
					header('Content-Type: text/csv');
					header('Content-Disposition: attachment; filename="import_members_' . $type . '.csv"');
					header('Pragma: no-cache');
					header('Expires: 0');

					// set the base filename
					$this->set_base_filename();

					readfile($this->base_filename . "_" . $type . ".csv");
					exit();
				} else {
					return $this->return_handler->results(400,"Filename must be provided.",new stdClass());
				}
			} else {
				return $this->return_handler->results(400,"Invalid file type provided",new stdClass());
			}
		} else {
			return $this->return_handler->results(400,"file type must be provided in url",new stdClass());
		}
	}

	// ------------------------------------------------------------------------------------------------------------------------------------------
	// Process the transaction file
	// ------------------------------------------------------------------------------------------------------------------------------------------

	public function post($p_filename) {
		$response->processed = 0;
		$response->error = 0;
		if ( !is_null($p_filename) && !empty($p_filename) ) {
			$this->temp_filename = $p_filename;

			// set the base filename
			$this->perform('this->set_base_filename');

			// open the transaction file for read
			if ( ($this->fp_trans = fopen($this->base_filename . "_trans.csv", "r")) !== false ) {
				// open the trans_error file
				$this->perform('this->open_trans_errror');
				// Read the csv Header entry
				if ( ($columns = fgetcsv($this->fp_trans)) !== false ) {
					// Read the rest of the entries as Transactions
					while ( ($row = fgetcsv($this->fp_trans)) !== false ) {
						if ( count($row) > 0 ) {
							// convert the row into a transaction object
							$entry = $this->perform('this->create_transaction',$columns,$row);

							// if the header entry for the trans_error file is empty, create it
							if ( count($this->header) == 0 ) {
								$this->perform('this->put_trans_error_header',(array) $entry->data);
							}

							// apply the transaction
							$return = $this->perform( $entry->api . '->' . $entry->method, (array) $entry->data, (bool) $entry->notify );

							if ( $return['status'] >= 300 ) {
								$response->error++;

								// write the trans_error entry
								$this->perform('this->put_trans_error',$return['message'],(array) $entry->data);
							} else {
								$response->processed++;
							}
						}
					}
					// close the trans_error file
					$this->perform('this->close_trans_error');

					// close the transaction file
					$this->perform('this->close_trans');

					return $this->return_handler->results(200,"",$response);
				} else {
					return $this->return_handler->results(400,"The transaction file can not be read",$response);
				}
			} else {
				return $this->return_handler->results(400,"The transaction file can not be opened",$response);
			}
		} else {
			return $this->return_handler->results(400,"valid filename must be provided.",new stdClass());
		}
	}

	public function create_transaction($p_columns,$p_row) {
		$entry = new stdClass();
		// create the import object based on the column names found in the Header entry
		foreach ( $p_columns as $index => $name ) {
			if ( $name == "data" ) {
				$entry->{$name} = json_decode($p_row[$index]);
			} else {
				$entry->{$name} = $p_row[$index];
			}
		}
		// echo "import : "; print_r($import); echo "<br />";
		return $entry;
	}

	// ------------------------------------------------------------------------------------------------------------------------------------------
	// create transaction error file
	// ------------------------------------------------------------------------------------------------------------------------------------------

	public function open_trans_errror() {
		$this->fp_trans_error = fopen($this->base_filename . "_trans_error.csv", "w");
	}

	public function put_trans_error_header($entry) {
		$this->header[] = "Error";
		foreach ( $entry as $column => $value ) {
			$this->header[] = $column;
		}

		$put = fputcsv($this->fp_trans_error, $this->header);
	}

	public function put_trans_error($p_error,$p_entry) {
		$entry = array();
		$entry[] = $p_error;
		foreach ( $p_entry as $column => $value ) {
			$entry[] = $value;
		}

		fputcsv($this->fp_trans_error, $entry);

		unset($entry);
	}

	public function close_trans_error() {
		 fclose($this->fp_trans_error);
	}
}
?>