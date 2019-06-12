<?php
/*
	David Bray
	BrayWorth Pty Ltd
	e. david@brayworth.com.au

	This work is licensed under a Creative Commons Attribution 4.0 International Public License.
		http://creativecommons.org/licenses/by/4.0/
	*/

namespace sms;

class smslog {
	function log( $to, $msg, $res, $full_result, $evt ) {
		error_log( sprintf( 'SMS: %s => %s : %s (%s)', $to, $msg, $res, $full_result));

	}

}
