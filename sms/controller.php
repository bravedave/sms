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

use bravedave\dvc\{json, logger, Response};
use green\search;

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

		if ('get-people-by-id' == $action) {

			if ($id = $this->getPost('id')) {

				$dao = new dao\people;
				if ($dto = $dao->getByID($id)) {

					json::ack('person')->add('data', [
						'id' => $dto->id,
						'name' => $dto->name,
						'mobile' => $dto->mobile,
						'mobile2' => $dto->mobile2,
						'email' => $dto->email,
					]);
				} else {

					json::nak($action);
				}
			} else {

				json::nak($action);
			}
		} elseif ('get-people-by-phone' == $action) {
			if ($tel = $this->getPost('tel')) {
				$dao = new dao\people;
				if ($dto = $dao->getByMobile($tel)) {
					json::ack('person')->add('data', [
						'id' => $dto->id,
						'name' => $dto->name,
						'mobile' => $dto->mobile,
						'mobile2' => $dto->mobile2,
						'email' => $dto->email,

					]);
				} elseif ($dto = $dao->getByMobile($tel, $mobile2 = true)) {
					json::ack('person')->add('data', [
						'id' => $dto->id,
						'name' => $dto->name,
						'mobile' => $dto->mobile,
						'mobile2' => $dto->mobile2,
						'email' => $dto->email,

					]);
				} else {
					json::nak($action);
				}
			} else {
				json::nak($action);
			}
		} elseif ('save-settings' == $action) {
			$a = [
				'countrycode' => $this->getPost('countrycode'),
				'providor' => $this->getPost('providor'),
				'account' => $this->getPost('account'),
				'password' => $this->getPost('password'),
				'from' => $this->getPost('from'),
				'appkey' => $this->getPost('appkey'),
			];

			$config = config::sms_account_file();
			if (file_exists($config)) unlink($config);
			file_put_contents(
				$config,
				json_encode($a, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
			);

			//~ sys::dump( $a);

			Response::redirect();
		} elseif ('search-person' == $action) {
			if ($term = $this->getPost('term')) {
				// \sys::logger( sprintf('<%s> %s', $term, __METHOD__));

				json::ack($action)
					->add('term', $term)
					->add('data', search::people($term));
			} else {
				json::nak($action);
			}
		} elseif ('send-sms' == $action) {

			$evt = $this->getPost('event');
			$msg = $this->getPost('message');
			$virtual = 'yes' == $this->getPost('virtual');
			if ($debug) logger::debug(sprintf('<msg( %s)> %s', $msg, __METHOD__));

			if ($msg == "") {

				json::nak(sprintf('<message is missing> %s', __METHOD__));
				if ($debug) logger::debug(sprintf('<message is missing> %s', __METHOD__));
			} else {

				$_to = $this->getPost('to');
				if ($_to) {

					$_to = (array)$_to;

					$to = [];
					foreach ($_to as $t) {

						if (strings::IsMobilePhone($t)) $to[] = $t;
					}

					if (count($to)) {
						if ($virtual && strings::IsMobilePhone(config::$SMS_VIRTUAL)) {

							$this->_handler->setFrom(config::$SMS_VIRTUAL);
						}

						json::ack('/sms : ' . $this->_handler->send($to, $msg, $evt));
					} else {

						json::nak(sprintf('<to is missing> %s', __METHOD__));
					}
				}
			}
		} elseif ('sms-enabled' == $action) {

			/*
			( _ => {
				_.post({
					url : _.url('sms'),
					data : { action : 'sms-enabled'},
				}).then( d => _.growl( d));
			})( _brayworth_);
			*/
			if ($this->_handler->enabled()) {

				json::ack($action);
			} else {

				json::nak($action);
			}
		} else {

			parent::postHandler();
		}
	}

	protected function _index() {

		$this->data = (object)[
			'title' => $this->title = config::$WEBNAME,
			'pageUrl' => strings::url($this->route),
			'searchFocus' => true,
			'aside' => [
				'index-title',
				'index-sms',
				'index-people'
			]
		];

		$this->renderBS5([
			'main' => fn () => $this->load('blank')
		]);
	}

	public function dialog() {

		if ($this->_handler) {

			$this->data = (object)[
				'title' => $this->title = 'SMS',
			];
			$this->load('sms-modal');
		} else {

			$this->modal([
				'title' => 'SMS',
				'headerClass' => 'bg-warning',
				'load' => 'not-enabled'
			]);
		}
	}

	public function settings() {

		$this->data = (object)[
			'aside' => [
				'index-title',
				'index-sms',
			],
			'pageUrl' => strings::url($this->route . '/settings'),
			'settings' => config::sms_account(),
			'searchFocus' => true,
			'title' => $this->title = 'SMS Settings',
		];

		// \sys::dump($this->data->settings);

		$this->renderBS5([
			'main' => fn () => $this->load('settings')
		]);
	}
}
