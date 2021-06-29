<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Hidden_Posts
 */

$hidden_posts_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $hidden_posts_tests_dir ) {
	$hidden_posts_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $hidden_posts_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $hidden_posts_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

require_once $hidden_posts_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin file.
 */
function hidden_posts_manually_load_plugin() {
	require dirname( __FILE__ ) . '/../hidden-posts.php';
}
tests_add_filter( 'muplugins_loaded', 'hidden_posts_manually_load_plugin' );

require $hidden_posts_tests_dir . '/includes/bootstrap.php';
