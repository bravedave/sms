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

use config as root;

abstract class config extends root {
	static $WEBNAME = 'SMS Demo Application for DVC';
	static $SMS_VIRTUAL = '';

	const sms_db_version = 0.01;

	static protected $_SMS_VERSION = 0;

	static protected function sms_version($set = null) {
		$ret = self::$_SMS_VERSION;

		if ((float)$set) {
			$config = self::sms_config();

			$j = file_exists($config) ?
				json_decode(file_get_contents($config)) :
				(object)[];

			self::$_SMS_VERSION = $j->sms_version = $set;

			file_put_contents($config, json_encode($j, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		}

		return $ret;
	}

	static function sms_checkdatabase() {
		if (self::sms_version() < self::sms_db_version) {
			$dao = new dao\dbinfo;
			$dao->dump($verbose = false);

			config::sms_version(self::sms_db_version);
		}

		// sys::logger( 'bro!');

	}

	static function sms_config() {
		return implode(DIRECTORY_SEPARATOR, [
			rtrim(self::dataPath(), '/ '),
			'sms.json'

		]);
	}

	static function sms_account_file() {
		return implode(DIRECTORY_SEPARATOR, [
			rtrim(self::dataPath(), '/ '),
			'sms-account.json',

		]);
	}

	static function sms_account(): account {

		$account = new account;

		if (file_exists($config = self::sms_account_file())) {
			// public $enabled = false;


			$a = array_merge(
				[
					'countrycode' => '',
					'appkey' => '',
					'providor' => '',
					'account' => '',
					'password' => '',
					'from' => '',
				],
				(array)json_decode(file_get_contents($config))
			);

			$account->countrycode = $a['countrycode'];
			$account->appkey = $a['appkey'];
			$account->providor = $a['providor'];
			$account->accountid = $a['account'];
			$account->accountpassword = $a['password'];
			$account->fromnumber = $a['from'];
			if ('' != trim($account->fromnumber)) {
				$account->enabled = true;
			}
		}
		return $account;
	}

	static function smshandler() {

		if ($account = self::sms_account()) return new sms($account);
		return false;
	}

	static function sms_init() {
		if (file_exists($config = self::sms_config())) {
			$j = json_decode(file_get_contents($config));

			if (isset($j->sms_version)) {
				self::$_SMS_VERSION = (float)$j->sms_version;
			};
		}
	}
}

config::sms_init();
