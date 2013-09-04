<?php
	
function calendar_event_compare($a,$b) {
	// This is the compare function to be used by the usort command used to sort the events
	if ( $a->start == $b->start ) {
		return 0;
	} else {
		return ($a->start < $b->start ? -1 : 1);
	}
}
	
function client_location_template_start_compare($a,$b) {
	// This is the compare function to be used by the usort command used to sort the events
	if ( $a->client->name == $b->client->name && $a->client->id == $b->client->id &&
	     $a->location->name == $b->location->name && $a->location->id == $b->location->id &&
	     $a->calendar_entry_template->name == $b->calendar_entry_template->name && $a->calendar_entry_template->id == $b->calendar_entry_template->id &&
	     $a->start == $b->start ) {
		return 0;
	} else {
		if ( $a->client->name != $b->client->name ) {
			return ( $a->client->name < $b->client->name ? -1 : 1 );
		} else if ( $a->client->id != $b->client->id ) {
			return ( $a->client->id < $b->client->id ? -1 : 1 );
		} else if ( $a->location->name != $b->location->name ) {
			return ( $a->location->name < $b->location->name ? -1 : 1 );
		} else if ( $a->location->id != $b->location->id ) {
			return ( $a->location->id < $b->location->id ? -1 : 1 );
		} else if ( $a->calendar_entry_template->name != $b->calendar_entry_template->name ) {
			return ( $a->calendar_entry_template->name < $b->calendar_entry_template->name ? -1 : 1 );
		} else if ( $a->calendar_entry_template->id != $b->calendar_entry_template->id ) {
			return ( $a->calendar_entry_template->id < $b->calendar_entry_template->id ? -1 : 1 );
		} else {
			return ( $a->start < $b->start ? -1 : 1 );
		}
	}
}
	
function client_event_compare($a,$b) {
	// This is the compare function to be used by the usort command used to sort the events
	if ( $a->template->name == $b->template->name && $a->template->id == $b->template->id &&
	     $a->location->name == $b->location->name && $a->location->id == $b->location->id &&
	     $a->event->start == $b->event->start ) {
		return 0;
	} else {
		if ( $a->template->name != $b->template->name ) {
			return ( $a->template->name < $b->template->name ? -1 : 1 );
		} else if ( $a->template->id != $b->template->id ) {
			return ( $a->template->id < $b->template->id ? -1 : 1 );
		} else if ( $a->location->name != $b->location->name ) {
			return ( $a->location->name < $b->location->name ? -1 : 1 );
		} else if ( $a->location->id != $b->location->id ) {
			return ( $a->location->id < $b->location->id ? -1 : 1 );
		} else {
			return ( $a->event->start < $b->event->start ? -1 : 1 );
		}
	}
}
	
function client_event_compare2($a,$b) {
	// This is the compare function to be used by the usort command used to sort the events
	if ( $a->location->name == $b->location->name && $a->location->id == $b->location->id &&
	     $a->template->name == $b->template->name && $a->template->id == $b->template->id &&
	     $a->event->start == $b->event->start ) {
		return 0;
	} else {
		if ( $a->location->name != $b->location->name ) {
			return ( $a->location->name < $b->location->name ? -1 : 1 );
		} else if ( $a->location->id != $b->location->id ) {
			return ( $a->location->id < $b->location->id ? -1 : 1 );
		} else if ( $a->template->name != $b->template->name ) {
			return ( $a->template->name < $b->template->name ? -1 : 1 );
		} else if ( $a->template->id != $b->template->id ) {
			return ( $a->template->id < $b->template->id ? -1 : 1 );
		} else {
			return ( $a->event->start < $b->event->start ? -1 : 1 );
		}
	}
}
	
function group_ranking_compare($a,$b) {
	// This is the compare function to be used by the usort command used to sort the events
	if ( $a->result->value == $b->result->value &&
	     $a->name == $b->name && $a->id == $b->id ) {
		return 0;
	} else {
		if ( $a->result->value != $b->result->value ) {
			return ( $a->result->value > $b->result->value ? -1 : 1 );
		} else if ( $a->name != $b->name ) {
			return ( $a->name > $b->name ? -1 : 1 );
		} else if ( $a->id != $b->id ) {
			return ( $a->id < $b->id ? -1 : 1 );
		}
	}
}