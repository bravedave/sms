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

abstract class config extends \config {
	static $WEBNAME = 'SMS Demo Application for DVC';
	static $SMS_VIRTUAL = '';

	const sms_db_version = 0.01;

  static protected $_SMS_VERSION = 0;

	static protected function sms_version( $set = null) {
		$ret = self::$_SMS_VERSION;

		if ( (float)$set) {
			$config = self::sms_config();

			$j = file_exists( $config) ?
				json_decode( file_get_contents( $config)):
				(object)[];

			self::$_SMS_VERSION = $j->sms_version = $set;

			file_put_contents( $config, json_encode( $j, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

		}

		return $ret;

	}

	static function sms_checkdatabase() {
		if ( self::sms_version() < self::sms_db_version) {
      $dao = new dao\dbinfo;
			$dao->dump( $verbose = false);

			config::sms_version( self::sms_db_version);

		}

		// sys::logger( 'bro!');

	}

	static function sms_config() {
		return implode( DIRECTORY_SEPARATOR, [
      rtrim( self::dataPath(), '/ '),
      'sms.json'

		]);

	}

	static function sms_account_file() {
		return implode( DIRECTORY_SEPARATOR, [
			rtrim( self::dataPath(), '/ '),
			'sms-account.json',

		]);

	}

	static function sms_account() {
		if ( file_exists( $config = self::sms_account_file())) {
			return json_decode( file_get_contents( $config));

		}
		return false;

	}

	static function smshandler() {
		if ( $config = self::sms_account()) {
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

  static function sms_init() {
		if ( file_exists( $config = self::sms_config())) {
			$j = json_decode( file_get_contents( $config));

			if ( isset( $j->sms_version)) {
				self::$_SMS_VERSION = (float)$j->sms_version;

			};

		}

	}

}

config::sms_init();
