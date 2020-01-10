<?php
/*
	David Bray
	BrayWorth Pty Ltd
	e. david@brayworth.com.au

	This work is licensed under a Creative Commons Attribution 4.0 International Public License.
		http://creativecommons.org/licenses/by/4.0/

	*/
namespace sms;

abstract class config extends \config {
	static $WEBNAME = 'SMS Demo Application for DVC';
	static $SMS_VIRTUAL = '';

	static function smsconfig() {
		$config = sprintf( '%s%ssms-account.json', self::dataPath(), DIRECTORY_SEPARATOR);
		if ( file_exists( $config)) return json_decode( file_get_contents( $config));

		return false;

	}

	static function smshandler() {
		if ( $config = self::smsconfig()) {
			$account = new \sms\account;

			$account->countrycode = $config->countrycode;
			$account->providor = $config->providor;
			$account->accountid = $config->account;
			$account->accountpassword = $config->password;
			$account->fromnumber = $config->from;
			if ( '' != trim( $account->fromnumber )) {
				$account->enabled = true;

			}

			return new sms( $account);

		}

		return false;

	}

}
