<?php
/*
	David Bray
	BrayWorth Pty Ltd
	e. david@brayworth.com.au

	This work is licensed under a Creative Commons Attribution 4.0 International Public License.
		http://creativecommons.org/licenses/by/4.0/
	*/

NameSpace sms;
Use dvc;

class sms {
	protected $account, $smslog, $error;

	public $fake = FALSE;

	const smsbroadcastAPI = 'https://api.smsbroadcast.com.au/api.php';
	const smsbroadcastAPI_advanced = 'https://api.smsbroadcast.com.au/api-adv.php';
	const smsbroadcast_MAXLENGTH = '760';

	protected static function smsbroadcastSMS( $aContent ) {
		$http = new dvc\HttpPost( self::smsbroadcastAPI_advanced);
			$http->setPostData( $aContent);
			$http->send();
			return ( $http->getResponse());

	}

	public function __construct( account $smsAccount, $logger = NULL ) {
		$this->account = $smsAccount;
		$this->smslog = ( is_null( $logger) ? new smslog() : $logger);
		$this->error = (object)['description' => ''];

	}

	public function getError() {
		return ( $error );

	}

	public function creditURL() {
		if ( $this->account->enabled) {
			if ( $this->account->providor == 'smsbroadcast' )
				return ( 'https://smsbroadcast.com.au/');

		}

		return ('');

	}

	public function balance() {
		if ( $this->account->enabled) {
			if ( $this->account->providor == 'smsbroadcast' ) {
				$content = [
					'username' =>$this->account->accountid,
					'password' => $this->account->accountpassword,
					'action' => 'balance' ];

				$full_result = self::smsbroadcastSMS( $content );
				//~ error_log( $full_result );

				$a = explode(':', $full_result);
				if( $a[0] == "OK")
					return ( $a[1]);

				elseif( $a[0] == "ERROR" )
					return ( sprintf( 'err : %s', $a[1]));

			}

			return ( -1);

		}
		else {
			throw new Exceptions\SMSNotEnabled;

		}

	}

	public function max() {
		if ( $this->account->enabled) {
			if ( $this->account->providor == 'smsbroadcast' )
				return ( self::smsbroadcast_MAXLENGTH);

		}

		return ( '0' );

	}

	public function send( $to = '', $msg = '' ) {
		if ( $this->account->enabled) {
			if ( is_array( $to )) {
				$a = array();
				foreach ( $to as $t)
					$a[] = $this->_send( $t, $msg );

				return ( implode( ' : ', $a ));

			}
			elseif ( $to == "" )
				return ( $this->error->description = 'Empty Telephone');

			else
				return ( $this->_send( $to, $msg ));

		}
		else
			return ( $this->error->description = 'Not SMS Enabled');

	}

	protected function _send( $to = '', $msg = '' ) {
		if ( $to == '' )
			return 'nak (empty to)';

		if ( preg_match( '/smsbroadcast/', $this->account->providor )) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$number = $phoneUtil->parse( $to, $this->account->countrycode);
				if ( $phoneUtil->isValidNumber( $number)) {
					$to = $phoneUtil->format( $number, \libphonenumber\PhoneNumberFormat::E164);
					//~ die( $to);

					if ( $this->account->providor == "smsbroadcast" ) {

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
							'maxsplit' => 5 ];
							//~ '&ref='.rawurlencode($ref);

						if ( $this->fake ) {
							$full_result = "fake ($to)";
							$res = "fake ($to)";

						}
						else {
							$full_result = self::smsbroadcastSMS( $content );
							$response_lines = explode("\n", $full_result);

							$res = array();
							foreach( $response_lines as $data_line ) {
								$data = explode( ':', $data_line );
								if( $data[0] == "OK" )
									$res[] = "OK : " . $data[1] . " : ref => " . $data[2];

								elseif( $data[0] == "BAD" )
									$res[] = "BAD: " . $data[1] . " NOT successful. Reason: " . $data[2];

								elseif( $data[0] == "ERROR" )
									$res[] = "ERR: Reason: " . $data[1];

							}

							$res = implode( ",", $res );

						}
						$this->smslog->log( $to, $msg, $res, $full_result );

					}

					return ( $res );

				}
				else {
					return ( 'nak : not valid number');

				}

			}
			catch ( \libphonenumber\NumberParseException $e) {
				return ( $e->description());

			}

		}
		else {
			throw new Exceptions\MissingOrInvalidProvidor;

		}

	}

}