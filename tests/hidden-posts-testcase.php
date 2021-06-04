<?php

/**
 * Base unit test class for Hidden Posts
 */
class HiddenPosts_TestCase extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		global $hidden_posts;
		$this->_hp = $hidden_posts;
	}
}
