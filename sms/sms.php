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

use bravedave\dvc\logger;

class sms {
	protected $account, $smslog, $error;

	public $fake = false;

	const smsbroadcastAPI = 'https://api.smsbroadcast.com.au/api.php';
	const smsbroadcastAPI_advanced = 'https://api.smsbroadcast.com.au/api-adv.php';
	const smsbroadcast_MAXLENGTH = '760';

	const cellcastAPI = 'https://cellcast.com.au/api/v3/';
	const cellcast_MAXLENGTH = '918';

	protected static function cellcastSMS(string $apiKey, string $text, array $phone_numbers = []): string|object {
		try {

			// 'APPKEY: <<APPKEY>>',
			$headers = array(
				sprintf('APPKEY: %s', $apiKey),
				'Accept: application/json',
				'Content-Type: application/json',
			);

			if ($text == 'balance') {

				$ch = curl_init(); //open connection
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_URL, static::cellcastAPI . 'account');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				if (!$result = curl_exec($ch)) {

					$response_error = json_decode(curl_error($ch));
					return (object)[
						"status" => 'ERROR',
						"msg" => "Something went to wrong, please try again",
						"result" => $response_error
					];
				}
				curl_close($ch);
				$o = (object)[
					"status" => 'OK',
					"msg" => "Got Account successfully",
					"balance" => 0,
					"result" => json_decode($result)
				];

				$o->balance = $o->result->data->sms_balance;

				return $o;
			} else {

				$fields = array(
					'sms_text' => $text, //here goes your SMS text
					'numbers' => $phone_numbers // Your numbers array goes here
				);
				$ch = curl_init(); //open connection
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_URL, static::cellcastAPI . 'send-sms');
				curl_setopt($ch, CURLOPT_POST, count($fields));
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				if (!$result = curl_exec($ch)) {

					$response_error = json_decode(curl_error($ch));
					return (object)[
						"status" => 'ERROR',
						"msg" => "Something went to wrong, please try again",
						"result" => $response_error
					];
				}
				curl_close($ch);

				$response = (object)[
					"status" => 'OK',
					"msg" => "SMS sent successfully",
					"result" => json_decode($result)
				];

				// return $response;

				if (200 == $response->result->meta->code) {

					return sprintf(
						'OK : %s : success: <%s>, credits_used: <%s>',
						$response->result->msg,
						$response->result->data->success_number,
						$response->result->data->credits_used,
					);
				} else {

					return sprintf(
						'NAK : %s',
						$response->result->msg
					);
				}
			}
		} catch (\Exception $e) {

			return (object)[
				"status" => 'ERROR',
				"msg" => "Something went to wrong, please try again.",
				"result" => []
			];
		}
	}

	protected static function smsbroadcastSMS($content) {
		$ch = curl_init(self::smsbroadcastAPI_advanced);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content, '', '&', PHP_QUERY_RFC3986));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($ch);
		curl_close($ch);
		return ($output);
	}

	protected function _send(array $to, $msg, $evt) {
		if ($to == '') return 'nak (empty to)';


		if (preg_match('/(smsbroadcast|cellcast)/', $this->account->providor)) {

			if ($this->account->providor == "cellcast") {

				/*
						$fields = [
							'sms_text' => $text, //here goes your SMS text
							'numbers' => $phone_number // Your numbers array goes here
						];
						 */
				return static::cellcastSMS($this->account->appkey, $msg, $to);
			} elseif ($this->account->providor == "smsbroadcast") {

				/* smsbroadcast Format
							username=myuser
							password=mypass
							to=0400111222,0400222333
							from=MyCompany
							message=Hello%20World
							ref=112233
							maxsplit=5 */

				$content = [
					'username' => $this->account->accountid,
					'password' => $this->account->accountpassword,
					'to' => $to,
					'from' => $this->account->fromnumber,
					'message' => $msg,
					'maxsplit' => 5
				];
				//~ '&ref='.rawurlencode($ref);

				if ($this->fake) {
					//~ if ( $this->fake || preg_match( '@^\+?(61|0)499.*@', $to)) {
					$full_result = "fake ($to)";
					$res = "fake ($to)";
				} else {
					$full_result = self::smsbroadcastSMS($content);
					$response_lines = explode("\n", $full_result);

					$res = [];
					foreach ($response_lines as $data_line) {
						$data = explode(':', $data_line);
						if ($data[0] == "OK")
							$res[] = "OK : " . $data[1] . " : ref => " . $data[2];

						elseif ($data[0] == "BAD")
							$res[] = "BAD: " . $data[1] . " NOT successful. Reason: " . $data[2];

						elseif ($data[0] == "ERROR")
							$res[] = "ERR: Reason: " . $data[1];
					}

					$res = implode(",", $res);
				}

				$this->smslog->log($to, $msg, $res, $full_result, $evt);
				return ($res);
			}
		} else {

			throw new Exceptions\MissingOrInvalidProvidor;
		}
	}

	public function __construct(account $smsAccount, $logger = null) {

		$this->account = $smsAccount;
		$this->smslog = (is_null($logger) ? new smslog : $logger);
		$this->error = (object)['description' => ''];
	}

	public function getError() {

		return ($this->error);
	}

	public function creditURL(): string {

		if ($this->account->enabled) {

			if ($this->account->providor == 'cellcast') {

				return ('https://cellcast.com.au/');
			} elseif ($this->account->providor == 'smsbroadcast') {

				return ('https://smsbroadcast.com.au/');
			}
		}

		return '';
	}

	public function balance() {

		if ($this->account->enabled) {

			if ($this->account->providor == 'cellcast') {

				$result = self::cellcastSMS($this->account->appkey, 'balance');

				if ($result->status == 'OK') {

					// logger::dump($result->result, __METHOD__);
					return (int)$result->balance == $result->balance ?
						(int)$result->balance :
						$result->balance;
				} elseif ($result->status == "ERROR") {

					return sprintf('err : %s', $result->msg);
				}
			} elseif ($this->account->providor == 'smsbroadcast') {

				$content = [
					'username' => $this->account->accountid,
					'password' => $this->account->accountpassword,
					'action' => 'balance'
				];

				$full_result = self::smsbroadcastSMS($content);
				//~ error_log( $full_result );

				$a = explode(':', $full_result);
				if ($a[0] == "OK") {

					return ($a[1]);
				} elseif ($a[0] == "ERROR") {

					return (sprintf('err : %s', $a[1]));
				}
			}

			return -1;
		} else {

			throw new Exceptions\SMSNotEnabled;
		}
	}

	public function enabled() {

		return $this->account->enabled;
	}

	public function max() {

		if ($this->account->enabled) {

			if ($this->account->providor == 'cellcast') {

				return self::cellcast_MAXLENGTH;
			} elseif ($this->account->providor == 'smsbroadcast') {

				return self::smsbroadcast_MAXLENGTH;
			}
		}

		return '0';
	}

	public function send($to = '', $msg = '', $evt = 'sms') {

		if ($this->account->enabled) {

			$_to = [];
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {

				if (is_array($to)) {

					foreach ($to as $t) {

						$number = $phoneUtil->parse($t, $this->account->countrycode);
						if ($phoneUtil->isValidNumber($number)) {

							$_to[] = $phoneUtil->format($number, \libphonenumber\PhoneNumberFormat::E164);
						}
					}
				} elseif ($to) {

					$number = $phoneUtil->parse($to, $this->account->countrycode);
					if ($phoneUtil->isValidNumber($number)) {

						$_to[] = $phoneUtil->format($number, \libphonenumber\PhoneNumberFormat::E164);
					}
				}

				if ($_to) {

					return $this->_send($_to, $msg, $evt);
				} else {

					return $this->error->description = 'Empty Telephone';
				}
			} catch (\libphonenumber\NumberParseException $e) {

				return $e->getMessage();
			}
		} else {

			return $this->error->description = 'Not SMS Enabled';
		}
	}
}
