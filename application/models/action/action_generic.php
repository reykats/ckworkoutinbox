<?php

class action_generic extends common_perform {

	protected $database_name = "workoutdb";
	protected $table_name;
	protected $id;
	protected $order_field;
	protected $xref_table_name;
	protected $xrefed_table_name;
	protected $linked_table_name;

	public function __construct() {
		// echo "-action_generic start-";
		
		parent::__construct();
		
		// echo "-action_generic end-";
	}
	
	public function post_linked_entry_list($entries) {
		foreach ( $entries as $entry ) {
			// add the new id to entry
			$entry->{$this->table_name . '_id'} = $this->id;
			// post the linked entry
			$return = $this->perform('table_' . $this->database_name . '_' . $this->linked_table_name . '->insert', $entry);
			// echo "post_linked_entry_list:"; print_r($return); echo "<br />";
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function post_xref_list($entries) {
		// ---------------------------------------------------------------------------------------------------------
		// $entries is an array of indexes to the xrefed table's entries to be xrefed
		//
		// This function is to be used to cross reference to entries in a STATIC table.
		// ---------------------------------------------------------------------------------------------------------
		foreach ( $entries as $entry_id ) {
			// create the xref table entry
			$fields->{$this->table_name . '_id'} = $this->id;
			$fields->{$this->xrefed_table_name . '_id'} = $entry_id;
			// post the xref table entry
			$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->insert', $fields);
			// echo "post_xref_list:"; print_r($return); echo "<br />";
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function post_xrefed_entries($entries) {
		// echo "entries:"; print_r($entries); echo "<br />";
		// ---------------------------------------------------------------------------------------------------------
		// $entries is an array of objects that are to be entered in the xrefed table,
		// then cross referenced in the xref table.
		//
		// This function is NOT to be used to cross reference to entries in a STATIC table.
		// ---------------------------------------------------------------------------------------------------------
		foreach ( $entries as $entry ) {
			// echo "entry:"; print_r($entry); echo "<br />";
			// do not pass the id field to post
			if ( isset($entry->id) ) {
				unset($entry->id);
			}
			// post the xrefed table's new entry
			$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->insert', $entry);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			// create the xref table entry
			$fields->{$this->table_name . '_id'} = $this->id;
			$fields->{$this->xrefed_table_name . '_id'} = $return['response']->id;
			// post the xref table entry
			$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->insert', $fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function post_pass_thru_xref_list($entries) {
		// echo "entries:"; print_r($entries); echo "<br />";
		// ---------------------------------------------------------------------------------------------------------
		// $entries is an array of objects that are to be entered in the xref table.
		//
		// This function is to be used to create cross reference table unordered entries that are made up of more than
		// the ids of the cross referenced tables.  For instance notes.
		// This function is to be used to create the cross reference to entries in a STATIC table.
		// ---------------------------------------------------------------------------------------------------------
		foreach ( $entries as $entry ) {
			// echo "entry:"; print_r($entry); echo "<br />";
			$entry->{$this->table_name . '_id'} = $this->id;
			if ( isset($entry->id) ) {
				// rename id to the xrefed table's linking id
				$entry->{$this->xrefed_table_name . '_id'} = $entry->id;
				unset($entry->id);
			}
			// post the xref table entry
			$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->insert', $entry);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function post_ordered_pass_thru_xref_entries($entries) {
		// echo "post ordered pass thru xref entries<br />";
		// echo "entries:"; print_r($entries); echo "<br />";
		// ---------------------------------------------------------------------------------------------------------
		// entries is an array of objects that contain both fields an object to pass thru too
		// ---------------------------------------------------------------------------------------------------------
		$order = array();
		// ---------------------------------------------------------------------------------------------------------
		// loop through the pass thru entries passed in
		// ---------------------------------------------------------------------------------------------------------
		foreach ( $entries as $pass_thru ) {
			// do not pass the id field to post
			if ( isset($pass_thru->id) ) {
				unset($pass_thru->id);
			}
			// create and initialize the new pass thru object
			$fields = array();
			$fields[$this->table_name . '_id'] = $this->id;
			//initialize the xrefed table id to null (it may stay null if the xrefed object was not passed)
			$fields[$this->xrefed_table_name . '_id'] = null;
			// ---------------------------------------------------------------------------------------------------------
			// loop through the pass thru entry's fields
			// ---------------------------------------------------------------------------------------------------------
			foreach ( $pass_thru as $key => $value ) {
				if ( is_object($value) ) {
					// ---------------------------------------------------------------------------------------------------------
					// if the field has an object that is not empty, post the object to the xrefed table
					// ---------------------------------------------------------------------------------------------------------
					if ( count((array) $value) > 0 ) {
						// do not pass the id field to post
						if ( property_exists($value,'id') ) {
							unset($value->id);
						}
						$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->insert', $value);
						if ( $return['status'] >= 300 ) {
							return $return;
						}
						// set the xrefed table id for the pass thru entry
						$fields[$this->xrefed_table_name . '_id'] = $return['response']->id;
					}
				} else {
					// if the field is not an object, add it to the xref table's field list
					$fields[$key] = $value;
				}
			}
			// ---------------------------------------------------------------------------------------------------------
			// post the fild list to the xref (pass thru) table
			// ---------------------------------------------------------------------------------------------------------
			// echo "POST " . $this->xref_table_name . " fields: "; print_r($fields); echo "<br />";
			$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->insert', (object) $fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			// store the id in the posted order array
			array_push($order,$return['response']->id);
		}
		// ---------------------------------------------------------------------------------------------------------
		// update the order field in the table
		// ---------------------------------------------------------------------------------------------------------
		$entry = new stdClass();
		$entry->id = $this->id;
		$entry->{$this->order_field} = json_encode($order);
		//
		$return = $this->perform('table_' . $this->database_name . '_' . $this->table_name . '->update', $entry);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		return $this->return_handler->results(200,"",null);
	}

	public function put_linked_entry_list($entries) {
		// -----------------------------------------------------------------------------------------------------------
		// The new list of linked entries is pass in
		// -----------------------------------------------------------------------------------------------------------
		$new_list = $entries;
		// -----------------------------------------------------------------------------------------------------------
		// Get the old list of linked entries
		// -----------------------------------------------------------------------------------------------------------
		$key = array();
		$key[$this->table_name . '_id'] = $this->id;
		$return = $this->perform('table_' . $this->database_name . '_' . $this->linked_table_name . '->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$old_list = $return['response'];
		// echo "old:"; print_r($old_list); echo "new:"; print_r($new_list);
		// -----------------------------------------------------------------------------------------------------------
		// delete entries that are in the old list and not in the new list
		// -----------------------------------------------------------------------------------------------------------
		foreach ( $old_list as $old ) {
			$delete = true;
			foreach ( $new_list as $new ) {
				if ( isset($new->id) && $old->id == $new->id ) {
					$delete = false;
					break;
				}
			}
			if ( $delete ) {
				$return = $this->perform('table_' . $this->database_name . '_' . $this->linked_table_name . '->delete',$old->id);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		// -----------------------------------------------------------------------------------------------------------
		// update entries that are in both lists
		// -----------------------------------------------------------------------------------------------------------
		foreach ( $new_list as $new ) {
			if ( isset($new->id) && !is_null($new->id) && !empty($new->id) ) {
				foreach ( $old_list as $old ) {
					if (  $new->id == $old->id ) {
						$return = $this->perform('table_' . $this->database_name . '_' . $this->linked_table_name . '->update',$new);
						if ( $return['status'] >= 300 ) {
							return $return;
						}
						break;
					}
				}
			}
		}
		// -----------------------------------------------------------------------------------------------------------
		// insert new entries that have a id that is null
		// -----------------------------------------------------------------------------------------------------------
		foreach ( $new_list as $new ) {
			if ( !isset($new->id) || is_null($new->id) || empty($new->id) ) {
				$new->{$this->table_name . '_id'} = $this->id;
				$return = $this->perform('table_' . $this->database_name . '_' . $this->linked_table_name . '->insert',$new);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function put_xref_list($entries) {
		// -----------------------------------------------------------------------------------------------------------
		// The new list of entries is an array of ids passed in
		// -----------------------------------------------------------------------------------------------------------
		$new_list = $entries;
		// -----------------------------------------------------------------------------------------------------------
		// Get the old list of current entries
		// -----------------------------------------------------------------------------------------------------------
		$key = array();
		$key[$this->table_name . '_id'] = $this->id;
		$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$old_list = $return['response'];
		// echo "old:"; print_r($old_list); echo "new:"; print_r($new_list);
		// -----------------------------------------------------------------------------------------------------------
		// delete entries that are in the old list and not in the new list
		// -----------------------------------------------------------------------------------------------------------
		foreach ( $old_list as $old ) {
			$delete = true;
			foreach ( $new_list as $new_id ) {
				if ( $old->{$this->xrefed_table_name . '_id'} == $new_id ) {
					$delete = false;
					break;
				}
			}
			if ( $delete ) {
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->delete',$old->id);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		// -----------------------------------------------------------------------------------------------------------
		// insert entries that are in the old list and not in the new list
		// -----------------------------------------------------------------------------------------------------------
		foreach ( $new_list as $new_id ) {
			$insert = true;
			foreach ( $old_list as $old ) {
				if ( $new_id == $old->{$this->xrefed_table_name . '_id'} ) {
					$insert = false;
					break;
				}
			}
			if ( $insert ) {
				$entry->{$this->table_name . '_id'} = $this->id;
				$entry->{$this->xrefed_table_name . '_id'} = $new_id;
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->insert',$entry);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function put_xref_entries($entries) {
		// entries is an array of objects
		//
		// if the object's id is null, it is to be added.
		// if the object's id is not null, it is to be updated.
		// if an existing object is not in the list, it is to be deleted.
		// -------------------------------------------------------------------------------------------------------------
		// The new list of new objects is passed in
		// -------------------------------------------------------------------------------------------------------------
		$new_list = $entries;
		// -----------------------------------------------------------------------------------------------------------
		// Get the old existing xref list
		// -----------------------------------------------------------------------------------------------------------
		$key = array();
		$key[$this->table_name . '_id'] = $this->id;
		$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$old_xref_list = $return['response'];
		// -------------------------------------------------------------------------------------------------------------
		// delete xrefs and entries that are in the old list and not in the new list
		// -------------------------------------------------------------------------------------------------------------
		foreach ( $old_xref_list as $old_xref ) {
			$delete = true;
			foreach ( $new_list as $new_entry ) {
				$new_entry_array = (array) $new_entry;
				if ( !empty($new_entry_array) && $old_xref->{$this->xrefed_table_name . '_id'} == $new_entry->id ) {
					$delete = false;
					break;
				}
			}
			if ( $delete ) {
				// delete the old xref entry
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->delete',$old_xref->id);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				// delete the old xrefed entry
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->delete',$old_xref->{$this->xrefed_table_name . '_id'});
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		// -------------------------------------------------------------------------------------------------------------
		// insert new entries that have ids that are null
		// update new entries that have ids that are not null
		// -------------------------------------------------------------------------------------------------------------
		foreach ( $new_list as $new_entry ) {
			if ( is_null($new_entry->id) ) {
				// insert the new xrefed entry
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->insert',$new_entry);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				// insert the new xref entry
				$entry->{$this->table_name . '_id'} = $this->id;
				$entry->{$this->xrefed_table_name . '_id'} = $return['response']->id;
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->insert',$entry);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			} else {
				// update the xrefed entry
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->update',$new_entry);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function put_pass_thru_xref_list($entries) {
		// -----------------------------------------------------------------------------------------------------------
		// The entries is an array of object.
		//
		// The objects are the values of the fields in the xref table.
		// If the object contains an id, it is the id of the xrefed table NOT the id of the xerf table.
		// -----------------------------------------------------------------------------------------------------------
		$new_list = array();
		foreach ( $entries as $entry ) {
			// echo "entry:"; print_r($entry); echo "<br />";
			$new = clone $entry;
			if ( !isset($new->{$this->table_name . '_id'}) ) {
				// add the table's link id
				$new->{$this->table_name . '_id'} = $this->id;
			}
			if ( isset($new->id) ) {
				// rename id to the xrefed table's linking id
				$new->{$this->xrefed_table_name . '_id'} = $new->id;
				unset($new->id);
			}
			$new_list[] = clone $new;
			unset($new);
		}
		// -----------------------------------------------------------------------------------------------------------
		// Get the old list of entries
		// -----------------------------------------------------------------------------------------------------------
		$key = array();
		$key[$this->table_name . '_id'] = $this->id;
		$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$old_list = $return['response'];

		//  print_r($old_list); echo "<br/>";
		//  print_r($new_list); echo "<br/>";

		// -----------------------------------------------------------------------------------------------------------
		// delete entries that are in the old list and not in the new list
		// -----------------------------------------------------------------------------------------------------------
		foreach ( $old_list as $old ) {
			$delete = true;
			foreach ( $new_list as $new ) {
				if ( $old->{$this->xrefed_table_name . '_id'} == $new->{$this->xrefed_table_name . '_id'} ) {
					$delete = false;
					break;
				}
			}
			if ( $delete ) {
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->delete',$old->id);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		// -----------------------------------------------------------------------------------------------------------
		// insert entries that are in the old list and not in the new list
		// update entries that are in both the old list and the new list
		// -----------------------------------------------------------------------------------------------------------
		foreach ( $new_list as $new ) {
			$insert = true;
			foreach ( $old_list as $old ) {
				if ( $new->{$this->xrefed_table_name . '_id'} == $old->{$this->xrefed_table_name . '_id'} ) {
					$insert = false;
					break;
				}
			}
			if ( $insert ) {
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->insert',$new);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			} else {
				$new->id = $old->id;
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->update',$new);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function put_ordered_pass_thru_xref_entries($entries) {
		// entries is an array of objects
		//
		// if the object's id is null, it is to be added.
		// if the object's id is not null, it is to be updated.
		// if an existing object is not in the list, it is to be deleted.
		// -------------------------------------------------------------------------------------------------------------
		// The new list of new objects is passed in
		// -------------------------------------------------------------------------------------------------------------
		$new_pass_thru_list = $entries;
		// -------------------------------------------------------------------------------------------------------------
		// Get the old existing xref list
		// -------------------------------------------------------------------------------------------------------------
		$key = array();
		$key[$this->table_name . '_id'] = $this->id;
		$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$old_pass_thru_list = $return['response'];

		// echo "database:" . $this->database_name . " table:" . $this->table_name . "<br />";
		// echo "new:"; print_r($new_pass_thru_list); echo "<br />";
		// echo "old:"; print_r($old_pass_thru_list); echo "<br />";
		// -------------------------------------------------------------------------------------------------------------
		// delete xrefs and their entries if they are in the old list and not in the new list
		// -------------------------------------------------------------------------------------------------------------
		foreach ( $old_pass_thru_list as $old_pass_thru ) {
			$delete = true;
			foreach ( $new_pass_thru_list as $new_pass_thru ) {
				if ( $old_pass_thru->id == $new_pass_thru->id ) {
					$delete = false;
					break;
				}
			}
			if ( $delete ) {
				// delete the old xref entry
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->delete',$old_pass_thru->id);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				// delete the old xrefed entry
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->delete',$old_pass_thru->{$this->xrefed_table_name . '_id'});
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		// -------------------------------------------------------------------------------------------------------------
		// update entries that are in the old list and in the new list
		// -------------------------------------------------------------------------------------------------------------
		foreach ( $new_pass_thru_list as $new_pass_thru ) {
			$update = false;
			$new_id = null;
			foreach ( $old_pass_thru_list as $old_pass_thru ) {
				if ( $new_pass_thru->id == $old_pass_thru->id ) {
					$update = true;
					// find the field that is an object in the new pass thru entry
					foreach ( $new_pass_thru as $key => $value ) {
						if ( is_object($value) ) {
							if ( count((array) $value) == 0 && is_null($old_pass_thru->{$this->xrefed_table_name . '_id'}) ) {
								// echo "A<br />";
								// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
								// The xref_table_name entry doesn't exist and hasn't been added
								// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
								// update the pass through entry with any changes
								$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->update',clone $new_pass_thru);
								if ( $return['status'] >= 300 ) {
									return $return;
								}
								
							} else if ( count((array) $value) == 0 && !is_null($old_pass_thru->{$this->xrefed_table_name . '_id'}) ) {
								// echo "B<br />";
								// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
								// The xref_table_name entry has been removed
								// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
								// remove the link in the pass through
								$pass_thru = clone $new_pass_thru;
								$pass_thru->{$this->xrefed_table_name . '_id'} = null;
								// update the pass through entry with any changes
								$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->update',clone $pass_thru);
								if ( $return['status'] >= 300 ) {
									return $return;
								}
								// delete the linked entry
								$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->delete',$old_pass_thru->{$this->xrefed_table_name . '_id'});
								if ( $return['status'] >= 300 ) {
									return $return;
								}
								
							} else if ( count((array) $value) != 0 && is_null($old_pass_thru->{$this->xrefed_table_name . '_id'}) ) {
								// echo "C<br />";
								// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
								// The xref_table_name entry is has been added
								// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
								// create the new linked entry
								$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->insert',$value);
								if ( $return['status'] >= 300 ) {
									return $return;
								}
								// change the link in the pass thru entry
								$pass_thru = clone $new_pass_thru;
								$pass_thru->{$this->xrefed_table_name . '_id'} = $return['response']->id;
								// update the pass through entry with any changes
								$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->update',clone $pass_thru);
								if ( $return['status'] >= 300 ) {
									return $return;
								}
								
							} else if ( count((array) $value) != 0 && !is_null($old_pass_thru->{$this->xrefed_table_name . '_id'}) ) {
								// echo "D<br />";
								if ( is_null($value->id) ) {
									// echo "E<br />";
									// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									// The xref_table_name entry removed and a new xref_table_name entry has been added
									// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									// ceate new xrefed entry
									$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->insert',$value);
									if ( $return['status'] >= 300 ) {
										return $return;
									}
									// change the link in the pass thru entry
									$pass_thru = clone $new_pass_thru;
									$pass_thru->{$this->xrefed_table_name . '_id'} = $return['response']->id;
									// update the pass through entry with any changes
									$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->update',clone $pass_thru);
									if ( $return['status'] >= 300 ) {
										return $return;
									}
									// delete old xrefed entry
									$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->delete',$old_pass_thru->{$this->xrefed_table_name . '_id'});
									if ( $return['status'] >= 300 ) {
										return $return;
									}
								} else {
									// echo "F database: ".$this->database_name." table:".$this->xref_table_name."<br />";
									// update the pass through entry with any changes
									$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->update',clone $new_pass_thru);
									if ( $return['status'] >= 300 ) {
										return $return;
									}
									// echo "xx<br />";
								}
							}
							break;
						}
					}
					break;
				}
			}
		}
		// -------------------------------------------------------------------------------------------------------------
		// insert xrefs and their entries if the xref's id is null
		// -------------------------------------------------------------------------------------------------------------
		$order = array();
		foreach ( $new_pass_thru_list as $new_pass_thru ) {
			if ( is_null($new_pass_thru->id) ) {
				// insert the new xrefed entry
				$new_id = null;
				foreach ( $new_pass_thru as $key => $value ) {
					if ( is_object($value) ){
						if ( !empty($value) ) {
							$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->insert',$value);
							if ( $return['status'] >= 300 ) {
								return $return;
							}
							$new_id = $return['response']->id;
							break;
						}
					}
				}
				// insert the new xref entry
				$new_pass_thru->{$this->table_name . '_id'} = $this->id;
				$new_pass_thru->{$this->xrefed_table_name . '_id'} = $new_id;
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->insert',$new_pass_thru);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				// store the new id for ordering
				array_push($order,$return['response']->id);
			} else {
				// store the id for ordering
				array_push($order,$new_pass_thru->id);
			}
		}
		// -------------------------------------------------------------------------------------------------------------
		// update the order field in the table
		// -------------------------------------------------------------------------------------------------------------
		$entry->id = $this->id;
		$entry->{$this->order_field} = json_encode($order);
		$return = $this->perform('table_' . $this->database_name . '_' . $this->table_name . '->update',$entry);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		return $this->return_handler->results(200,"",null);
	}

	public function delete_linked_entries() {
		// -----------------------------------------------------------------------------------------------------------
		// Get the old list of linked entries
		// -----------------------------------------------------------------------------------------------------------
		$key = array();
		$key[$this->table_name . '_id'] = $this->id;
		$return = $this->perform('table_' . $this->database_name . '_' . $this->linked_table_name . '->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$old_list = $return['response'];
		// echo "old:"; print_r($old_list);
		// -------------------------------------------------------------------------------------------------------------
		// delete the existing entries in the list
		// -------------------------------------------------------------------------------------------------------------
		foreach ( $old_list as $entry ) {
			$return = $this->perform('table_' . $this->database_name . '_' . $this->linked_table_name . '->delete',$entry->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function delete_xref_list() {
		// -----------------------------------------------------------------------------------------------------------
		// Get the old existing xref list
		// -----------------------------------------------------------------------------------------------------------
		$key = array();
		$key[$this->table_name . '_id'] = $this->id;
		$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$old_xref_list = $return['response'];
		// echo "old:"; print_r($old_xref_list);
		// -------------------------------------------------------------------------------------------------------------
		// delete the existing xref list
		// -------------------------------------------------------------------------------------------------------------
		foreach ( $old_xref_list as $entry ) {
			$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->delete',$entry->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		return $this->return_handler->results(200,"",null);
	}

	public function delete_xrefed_entries() {
		// -----------------------------------------------------------------------------------------------------------
		// Get the old existing xref list
		// -----------------------------------------------------------------------------------------------------------
		$key = array();
		$key[$this->table_name . '_id'] = $this->id;
		$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$old_xref_list = $return['response'];
		// echo "old:"; print_r($old_xref_list);
		// -------------------------------------------------------------------------------------------------------------
		// delete the xrefed entries and the xref entries in the list
		// -------------------------------------------------------------------------------------------------------------
		foreach ( $old_xref_list as $entry ) {
			$return = $this->perform('table_' . $this->database_name . '_' . $this->xref_table_name . '->delete',$entry->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			//echo "DELETE xref:";print_r($entry);
			//echo "xrefed_table_name: " . $this->xrefed_table_name . "<br />";
			if ( !is_null($entry->{$this->xrefed_table_name . '_id'}) ) {
				$return = $this->perform('table_' . $this->database_name . '_' . $this->xrefed_table_name . '->delete',$entry->{$this->xrefed_table_name . '_id'});
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		return $this->return_handler->results(200,"",null);
	}
}