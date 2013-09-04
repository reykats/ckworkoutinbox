<?php

class csv_generic extends common_perform {

	// Used to translate the csv file's column name into database field names
	protected $column_trans;

	// information about whether a database field is mandatory or unique
	protected $field_info;

	// Column Definitions is a combination of the $columns_trans and $field_info entries.
	// $column_defs are stored in this array in the order in which they apear in the csv file.
	// They are indexed by thier csv file column name.
	protected $column_defs;

	// database lookup tables used to validate data
	protected $lookup_tables;

	// the $_GET parameters are stored here
	protected $temp_filename;
	protected $client_id;
	protected $file_type;

	// The resulting table of parsed, validated, and formatted csv records
	protected $entry_table;

	// the database name
	protected $database_name = "workoutdb";

	public function __construct() {
		// echo "-csv_generic start-";
		parent::__construct();
		//
		// Load the validate data and format data helpers
		// $this->load->helper('validate_data');
		// $this->load->helper('format_data');
		// echo "-csv_generic end-";
	}

	public function extract_file() {
		$this->entry_table = array();

		$temp_filename = $this->config->item('workoutinbox_client_data') . "/temp/csv/" . $this->temp_filename;

		$this->load->library('parsecsv');
		if ( $results = $this->parsecsv->auto($temp_filename) ) {
			// load the file's csv column names to thier coordinating columns object entry
			$this->load_column_defs();
			// echo "header definitions : <br />"; print_r($this->column_defs); echo "<br />";
			// check to see if all mandatory columns exist
			$return = $this->validate_mandatory_headers();
			// echo "return : "; print_r($return); echo "<br />";
			if ( $return['status'] < 300 ) {
				// load any lookup tables that are needed
				$this->create_lookup_tables();
				// echo "lookup tables:<br />"; print_r($this->lookup_tables); echo "<br />";

				foreach ( $this->parsecsv->data as $row ) {
					// echo "row : <br />"; print_r($row); echo "<br />";
					// create and initialize the new entry
					$return = $this->create_entry($row);
					// echo "return : "; print_r($return); echo "<br />";
					// put entry into the array of entries
					array_push($this->entry_table,$return['response']);
					unset($entry);
				}
				return $this->return_handler->results(200,"",new stdClass());
			} else {
				return $return;
			}
		} else {
			return $this->return_handler->results(400,"Temp File could not be parsed.",new stdClass());
		}
	}

	public function load_column_defs() {
		// echo "parsecsv titles : <br />"; print_r($this->parsecsv->titles); echo "<br />";
		foreach ( $this->parsecsv->titles as $value ) {
			// echo "column : $value<br />";
			if ( $this->file_type == "workoutinbox" ) {
				// replace dashes, underscores, and spaces to get the header
				$header = str_replace(array("_","-"," "),"",strtolower($value));
			} else {
				$header = $value;
			}
			if ( array_key_exists($header, $this->column_trans[$this->file_type]) ) {
				// store the column translation off for the columm
				$field = $this->column_trans[$this->file_type][$header]['field'];
				$this->column_defs[$value]['format'] = $this->column_trans[$this->file_type][$header]['format'];
				$this->column_defs[$value]['field'] = $field;
				// delete the used $colums entry to prevent duplicate columns
				unset($this->column_trans[$this->file_type][$header]);
				//
				// store the field info off for the column
				$this->column_defs[$value]['mandatory'] = $this->field_info[$field]['mandatory'];
				$this->column_defs[$value]['unique'] = $this->field_info[$field]['unique'];
				$this->column_defs[$value]['init'] = $this->field_info[$field]['init'];
				$this->column_defs[$value]['lookup'] = $this->field_info[$field]['lookup'];
				// delete the used $field_info entry
				unset($this->field_info[$field]);
			}
		}
	}

	public function create_lookup_tables() {
		$this->lookup_tables = array();
		foreach ( $this->column_defs as $header => $column_def ) {
			// if there is a lookup table for the column's values and it has not been loaded already, load it
			if ( !is_null($column_def['lookup']) && !array_key_exists($column_def['lookup'],$this->lookup_tables) ) {
				$this->create_lookup_table($column_def['lookup']);
			}
		}

		//print_r($this->lookup_tables);
	}

	public function create_lookup_table($p_table_name) {
		// get the id and name for each entry in the table
		$this->db->select('id,name');
		$rows = $this->db->get($p_table_name)->result();
		if ( !empty($rows) ) {
			// initialize the lookup table
			$this->lookup_tables[$p_table_name] = array();
			foreach ( $rows as $row ) {
				// create the lookup table entry ( indexed by name )
				$this->lookup_tables[$p_table_name][$row->name] = (int) $row->id;
			}
		}
	}

	public function validate_mandatory_headers() {
		// if the column header translates to a mandatory field, add the column header to the missing coluns list
		$missing_columns = array();
		foreach ( $this->column_trans[$this->file_type] as $header => $value ) {
			if ( $this->field_info[$value['field']]['mandatory'] ) {
				array_push($missing_columns,$header);
			}
		}
		if ( count($missing_columns) == 0 ) {
			return $this->return_handler->results(200,"",$missing_columns);
		} else {
			return $this->return_handler->results(400,"You are missing mandatory columns [" . implode(", ",$missing_columns) . "]",$missing_columns);
		}
	}

	public function create_entry($p_row) {
		$missing_mandatory_values = array();
		$invalid_field_values = array();
		$entry = array();
		$valid_entry = true;
		foreach ( $this->column_defs as $header => $column_def ) {
			// initialize the field in the entry
			$entry[$header] = new stdClass();
			// store the csv value
			$entry[$header]->field = $column_def['field'];
			$entry[$header]->csv_value = $p_row[$header];
			$entry[$header]->db_value = $column_def['init'];
			$valid_field = true;
			// check for mandatory field not having a field value
			if ( $column_def['mandatory'] && empty($p_row[$header]) ) {
				$valid_field = false;
				$valid_entry = false;
				array_push($missing_mandatory_values,$header);
			}
			if ( $valid_field ) {
				// validate the field value if it is set
				if ( !empty($p_row[$header]) ) {
					if ( function_exists('valid_' . $column_def['format']) && !call_user_func('valid_' . $column_def['format'],$p_row[$header]) ) {
						$valid_field = false;
						$valid_entry = false;
						array_push($invalid_field_values,$header);
					}
				}
			}
			if ( $valid_field ) {
				// format the field value
				if ( function_exists('format_' . $column_def['format']) ) {
					$entry[$header]->db_value = call_user_func('format_' . $column_def['format'],$p_row[$header]);
					if ( !is_null($column_def['lookup']) ) {
						// get the value from the lookup table
						$entry[$header]->db_value = $this->lookup_tables[ $column_def['lookup'] ][ $entry[$header]->db_value ];
					}
				} else if ( !is_null( $column_def['lookup'] ) && !empty( $column_def['lookup'] ) ) {
					//print_r($this->lookup_tables);
					//echo " -- " . $p_row[$header] . " -- ";
					// get the value from the lookup table
					$entry[$header]->db_value = $this->lookup_tables[ $column_def['lookup'] ][ $p_row[$header] ];
				} else {
					$entry[$header]->db_value = $p_row[$header];
				}
			}
		}
		$response->entry = $entry;
		$response->valid = $valid_entry;
		$response->missing_mandatory_values = $missing_mandatory_values;
		$response->invalid_field_values = $invalid_field_values;
		if ( count($missing_mandatory_values) == 0 && count($invalid_field_values) == 0 ) {
			return $this->return_handler->results(200,"",$response);
		} else {

			return $this->return_handler->results(400,"Field validation error",$response);
		}
	}

}
?>