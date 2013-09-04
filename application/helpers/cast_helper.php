<?php
	
function cast_int($p_value) {
	if ( is_null($p_value) ) {
		$value = null;
	} else {
		$value = (int) $p_value;
	}
	return $value;
}

function cast_real($p_value) {
	if ( is_null($p_value) ) {
		$value = null;
	} else {
		$value = (real) $p_value;
	}
	return $value;
}

function cast_float($p_value) {
	if ( is_null($p_value) ) {
		$value = null;
	} else {
		$value = (float) $p_value;
	}
	return $value;
}

function cast_boolean($p_value) {
	if ( is_null($p_value) ) {
		$value = null;
	} else {
		$value = (boolean) $p_value;
	}
	return $value;
}

