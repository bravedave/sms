<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace sms;

class account {
	public $enabled = false;

	public $countrycode = '';

	// cellcast requirements
	public $appkey = '';

	public $id = 0;

	// smsbroadcast requirements
	public $providor = '';
	public $accountid = '';
	public $accountpassword = '';
	public $fromnumber = '';

	// just160 - deprecated
	public $accountkey = '';
}
