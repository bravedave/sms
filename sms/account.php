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

	// smsbroadcast requirements
	public $providor = '';
	public $accountid = '';
	public $accountpassword = '';
	public $fromnumber = '';
}
