<?php
/*
	David Bray
	BrayWorth Pty Ltd
	e. david@brayworth.com.au

	This work is licensed under a Creative Commons Attribution 4.0 International Public License.
		http://creativecommons.org/licenses/by/4.0/
	*/

namespace sms;

class account {
	public $enabled = false;

	public $countrycode = '';

	// smsbroadcast requirements
	public $providor = '';
	public $accountid = '';
	public $accountpassword = '';
	public $fromnumber = '';

}
