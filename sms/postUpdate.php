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

use application, config, green, dvc;

class postUpdate extends dvc\service {

  protected function _upgrade() {

    // config::route_register('people', 'green\\people\\controller');
    green\people\config::green_people_checkdatabase();
    echo (sprintf('%s : %s%s', 'green updated', __METHOD__, PHP_EOL));

    // config::route_register('sms', 'sms\\controller');
  }

  static function upgrade() {

    $app = new self(application::startDir());
    $app->_upgrade();
  }
}
