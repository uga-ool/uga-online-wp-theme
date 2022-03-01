<?php

namespace Leadin\admin;

use Leadin\wp\User;


/**
 * Handles metadata for users in the admin area.
 */
class AdminUserMetaData {

	const SKIP_REVIEW = 'leadin_skip_review';

	/**
	 * Set SKIP_REVIEW meta data for a user.
	 *
	 * @param int $skip_epoch Epoch time of when the review was skipped.
	 */
	public static function set_skip_review( $skip_epoch ) {
		return User::set_metadata( self::SKIP_REVIEW, $skip_epoch );
	}

	/**
	 * Get SKIP_REVIEW meta data for a user.
	 */
	public static function get_skip_review() {
		return User::get_metadata( self::SKIP_REVIEW );
	}
}
