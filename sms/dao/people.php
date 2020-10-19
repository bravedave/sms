<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace sms\dao;

use green;
use strings;

class people extends green\people\dao\people {
	public function getByMobile( $a, $mobile2 = false) {
		//~ $debug = true;
		$debug = false;

		$w = [];
		foreach( (array)$a as $p ) {
			if ( $p) {
				$w[] = sprintf( 'mobile = "%s"', $this->escape( $p));
				if ( $mobile2) $w[] = sprintf( 'mobile2 = "%s"', $this->escape( $p));

				if ( strings::isMobilePhone( $p)) {
					$x = strings::AsMobilePhone( $p);
					if ( $x != $p) {
						$w[] = sprintf( 'mobile = "%s"', $this->escape( $x));
						if ( $mobile2) $w[] = sprintf( 'mobile2 = "%s"', $this->escape( $x ));

					}

				}

			}

		}

		$_sql = sprintf( 'SELECT * FROM people WHERE %s', implode( ' OR ', $w ));
		if ( $debug) \sys::logSQL( $_sql);

		if ( $res = $this->Result( $_sql)) {
			return $res->dto( $this->template);

		}

		return null;

  }

}
