<?php
/*
	David Bray
	BrayWorth Pty Ltd
	e. david@brayworth.com.au

	This work is licensed under a Creative Commons Attribution 4.0 International Public License.
		http://creativecommons.org/licenses/by/4.0/
	*/

class home extends Controller {
	protected $_handler = null;

	protected function before() {
		$this->_handler = \config::smshandler();

	}

	protected function posthandler() {
		$debug = false;
		//~ $debug = true;

		$action = $this->getPost('action');

		if ( 'save-settings' == $action) {
			$a = [
				'countrycode' => $this->getPost('countrycode'),
				'providor' => $this->getPost('providor'),
				'account' => $this->getPost('account'),
				'password' => $this->getPost('password'),
				'from' => $this->getPost('from'),

			];

			$config = sprintf( '%s%ssms-account.json', \config::dataPath(), DIRECTORY_SEPARATOR);
			if ( file_exists( $config)) unlink( $config);

			file_put_contents( $config, json_encode( $a, JSON_UNESCAPED_SLASHES));

			//~ sys::dump( $a);

			Response::redirect( url::tostring());

		}
		elseif ( 'send-sms' == $action) {
			$evt = $this->getPost('event');
			$msg = $this->getPost('message');
			$virtual = 'yes' == $this->getPost('virtual');
			if ( $debug) \sys::logger( sprintf( 'sms::msg( %s)', $msg ));

			if ( $msg == "" ) {
				\Json::nak( '/sms: message is missing');
				if ( $debug) \sys::logger( 'sms: message is missing');

			}
			else {
				$_to = $this->getPost('to');
				if ( $_to) {
					$_to = (array)$_to;

					$to = [];
					foreach ( $_to as $t ) {
						if ( sms\sms::IsMobilePhone( $t)) {
							$to[] = $t;

						}

					}

					if ( count( $to)) {
						if ( $virtual && sms\sms::IsMobilePhone( \config::$SMS_VIRTUAL)) {
							$this->_handler->setFrom( \config::$SMS_VIRTUAL);

						}

						\Json::ack( '/sms : ' . $this->_handler->send( $to, $msg, $evt));

					}
					else {
						\Json::nak( '/sms: to is missing' );

					}

				}

			}

		}
		else { \Json::nak( $action); }

	}

	protected function _index() {
		$this->render([
			'title' => $this->title = 'SMS Sample Application',
			'primary' => 'blank',
			'secondary' =>'index'
		]);

	}

	function index() {
		$this->isPost() ?
			$this->postHandler() :
			$this->_index();

	}

	public function dialog() {
		$this->modal([
			'title' => 'SMS',
			'load' => 'sms-modal'

		]);

	}

	function settings() {
		$this->render([
			'title' => $this->title = 'SMS Settings',
			'primary' => 'settings',
			'secondary' =>'index'
		]);

	}

}
