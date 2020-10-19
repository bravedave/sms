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

use green\search;
use Json;
use Response;
use strings;

class controller extends \Controller {
	protected $_handler = null;
	protected $viewPath = __DIR__ . '/views/';

	protected function before() {
		config::sms_checkdatabase();
		$this->_handler = config::smshandler();
		parent::before();

	}

	protected function posthandler() {
		$debug = false;
		//~ $debug = true;

		$action = $this->getPost('action');

		if ( 'get-people-by-id' == $action) {
			if ( $id = $this->getPost( 'id')) {
				$dao = new dao\people;
				if ( $dto = $dao->getByID( $id)) {
					Json::ack( 'person')->add('data', [
						'id' => $dto->id,
						'name' => $dto->name,
						'mobile' => $dto->mobile,
						'mobile2' => $dto->mobile2,
						'email' => $dto->email,

					]);

				} else { Json::nak( $action); }

			} else { Json::nak( $action); }

		}
		elseif ( 'get-people-by-phone' == $action) {
			if ( $tel = $this->getPost( 'tel')) {
				$dao = new dao\people;
				if ( $dto = $dao->getByMobile( $tel)) {
					Json::ack( 'person')->add('data', [
						'id' => $dto->id,
						'name' => $dto->name,
						'mobile' => $dto->mobile,
						'mobile2' => $dto->mobile2,
						'email' => $dto->email,

					]);

				}
				elseif ( $dto = $dao->getByMobile( $tel, $mobile2 = true)) {
					Json::ack( 'person')->add('data', [
						'id' => $dto->id,
						'name' => $dto->name,
						'mobile' => $dto->mobile,
						'mobile2' => $dto->mobile2,
						'email' => $dto->email,

					]);

				} else { Json::nak( $action); }

			} else { Json::nak( $action); }

		}
		elseif ( 'save-settings' == $action) {
			$a = [
				'countrycode' => $this->getPost('countrycode'),
				'providor' => $this->getPost('providor'),
				'account' => $this->getPost('account'),
				'password' => $this->getPost('password'),
				'from' => $this->getPost('from'),

			];

			$config = config::sms_account_file();
			if ( file_exists( $config)) unlink( $config);
			file_put_contents(
				$config,
				json_encode( $a, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)

			);

			//~ sys::dump( $a);

			Response::redirect();

		}
		elseif ( 'search-person' == $action) {
			if ( $term = $this->getPost('term')) {
        // \sys::logger( sprintf('<%s> %s', $term, __METHOD__));

				Json::ack( $action)
					->add( 'term', $term)
					->add( 'data', search::people( $term));

			}
			else {
				Json::nak( $action);

			}

    }
		elseif ( 'send-sms' == $action) {
			$evt = $this->getPost('event');
			$msg = $this->getPost('message');
			$virtual = 'yes' == $this->getPost('virtual');
			if ( $debug) \sys::logger( sprintf( 'sms::msg( %s)', $msg ));

			if ( $msg == "" ) {
				Json::nak( '/sms: message is missing');
				if ( $debug) \sys::logger( 'sms: message is missing');

			}
			else {
				$_to = $this->getPost('to');
				if ( $_to) {
					$_to = (array)$_to;

					$to = [];
					foreach ( $_to as $t ) {
						if ( strings::IsMobilePhone( $t)) {
							$to[] = $t;

						}

					}

					if ( count( $to)) {
						if ( $virtual && strings::IsMobilePhone( config::$SMS_VIRTUAL)) {
							$this->_handler->setFrom( config::$SMS_VIRTUAL);

						}

						Json::ack( '/sms : ' . $this->_handler->send( $to, $msg, $evt));

					}
					else {
						Json::nak( '/sms: to is missing' );

					}

				}

			}

		}
		else { Json::nak( $action); }

	}

	protected function _index() {
		$this->render([
			'title' => $this->title = 'SMS Sample Application',
			'primary' => 'blank',
			'secondary' => [
				'index-title',
				'index-sms',
				'index-people'

			]

		]);

	}

	public function dialog() {
		if ( $this->_handler) {
			$this->title = 'SMS';
			$this->load('sms-modal');

		}
		else {
			$this->modal([
				'title' => 'SMS',
				'headerClass' => 'bg-warning',
				'load' => 'not-enabled'

			]);

		}

	}

	public function settings() {
		$this->data = (object)[
			'settings' => config::sms_account()

		];

		$this->render([
			'title' => $this->title = 'SMS Settings',
			'primary' => 'settings',
			'secondary' => [
				'index-title',
				'index-sms',

			]

		]);

	}

}
