<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

class config extends \bravedave\dvc\config {

  static $CONTENT_ENABLE_CROSS_ORIGIN_HEADER = true;

  static $WEBNAME = 'SMS Sample Application';

  static public function route(string $path): string {

    $map = (object)[
      'sms' => 'sms\controller',
      'people' => 'green\people\controller',
    ];

    // logger::info(sprintf('<%s> %s', isset($map->{$path}) ? $map->{$path} : 'not set .. looking', __METHOD__));
    return (isset($map->{$path}) ? $map->{$path} : parent::route($path));
  }
}
