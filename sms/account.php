<?php
/*
	David Bray
	BrayWorth Pty Ltd
	e. david@brayworth.com.au

	This work is licensed under a Creative Commons Attribution 4.0 International Public License.
		http://creativecommons.org/licenses/by/4.0/
	*/

NameSpace sms;

class account {
	var $enabled = FALSE;

	var $countrycode = '';

	// smsbroadcast requirements
	var $providor = '';
	var $accountid = '';
	var $accountpassword = '';
	var $fromnumber = '';

}
