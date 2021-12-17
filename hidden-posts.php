<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Plugin Name: Hidden Posts
 * Description: Hide posts on the home page.
 * Version:     0.1
 * Author:      Automattic
 * Author URI:  http://automattic.com
 * License:     GPLv2 or later
 *
 * @package Hidden_Posts
 */

/**
 * Hidden Posts
 *
 * Hide a limited number of specified posts from the hompage.
 *
 * We keep a list of post ID's in an option and use
 * a NOT IN query with those post ID's. We limit
 * the number of posts so that the query doesn't
 * get too slow.
 */
class Hidden_Posts {

	const OPTION_KEY = 'hidden-posts';
	const NONCE_KEY  = 'hidden-posts-nonce';
	const LIMIT      = 100; // Maximum number of posts to store in the hidden array.

	/**
	 * Get hooked in!
	 */
	public function run() {
		add_action( 'save_post', array( $this, 'save_meta' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_filter( 'manage_posts_columns', array( $this, 'custom_column_title' ), 10, 2 );
		add_action( 'manage_posts_custom_column', array( $this, 'custom_column_data' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'custom_column_style' ) );
		add_filter( 'views_edit-post', array( $this, 'custom_column_filter' ) );
		add_filter( 'get_previous_post_where', array( $this, 'render_pagination' ) );
		add_filter( 'get_next_post_where', array( $this, 'render_pagination' ) );
	}

	/**
	 * Hide the posts in the hidden array on the homepage.
	 *
	 * @param WP_Query $query The WP_Query instance.
	 */
	public function pre_get_posts( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( apply_filters( 'hidden_posts_show_posts', is_single() ) ) {
			return;
		}

		$hidden_posts = self::get_posts();
		$post_not_in  = $query->get( 'post__not_in' );
		if ( is_array( $post_not_in ) ) {
			$post_not_in = array_unique( array_merge( $post_not_in, $hidden_posts ) );
		} else {
			$post_not_in = $hidden_posts;
		}
		$query->set( 'post__not_in', $post_not_in );
	}

	/**
	 * Update the post array.
	 *
	 * @param int $post Post ID.
	 */
	public function save_meta( $post ) {
		// Verify the nonce.
		if ( ! isset( $_POST[ self::NONCE_KEY ] ) || ! wp_verify_nonce( sanitize_text_field( $_POST[ self::NONCE_KEY ] ), self::NONCE_KEY ) ) {
			return;
		}

		// Update the post array if necessary.
		if ( isset( $_POST[ self::OPTION_KEY ] ) ) {
			self::add_post( $post );
		} else {
			self::remove_post( $post );
		}
	}

	/**
	 * Get the array of posts.
	 *
	 * @return array Array of Post IDs.
	 */
	public static function get_posts() {
		return array_filter( array_map( 'absint', get_option( self::OPTION_KEY, array() ) ) );
	}

	/**
	 * Add the post to the hidden array.
	 *
	 * If the post is already in the hidden array,
	 * just bail. Otherwise, add it. Also,
	 * make sure we don't go over the specified limit.
	 *
	 * @param int $id Post ID.
	 */
	public function add_post( $id ) {
		$posts = self::get_posts();

		if ( in_array( $id, $posts ) ) {
			return;
		}

		// Add the post to the array.
		$posts[] = $id;

		$count_posts = count( $posts );

		// Make sure there are only LIMIT posts in the array.
		while ( $count_posts > self::LIMIT ) {
			array_shift( $posts );
			$count_posts = count( $posts );
		}

		update_option( self::OPTION_KEY, array_map( 'intval', $posts ) );
	}

	/**
	 * Remove the post from the hidden array.
	 *
	 * If the post doesn't exist in the hidden array,
	 * just bail. Otherwise, splice it out.
	 *
	 * @param int $id Post ID.
	 */
	public static function remove_post( $id ) {
		$posts = self::get_posts();

		if ( ! in_array( $id, $posts ) ) {
			return;
		}

		array_splice( $posts, array_search( $id, $posts ), 1 );

		update_option( self::OPTION_KEY, array_map( 'intval', $posts ) );
	}

	/**
	 * Add custom title to the admin columns.
	 *
	 * @param array $columns The original admin column titles.
	 * @param string $post_type The post type being displayed.
	 * @return array The updated admin column titles.
	 */
	public function custom_column_title( array $columns, $post_type ) {
		// Only add the column if it's a supported post type.
		if ( in_array( $post_type, self::supported_post_types() ) ) {
			unset( $columns['date'] );
			$columns['visibility'] = esc_html__( 'Visibility', 'hidden-posts' );
			$columns['date']       = esc_html__( 'Date', 'hidden-posts' );
		}
		return $columns;
	}

	/**
	 * Add custom data to the admin columns.
	 *
	 * @param string $column The column to which the data should be added.
	 * @param int    $post_id The id of the post to which the data should be added.
	 * @return void The added data.
	 */
	public function custom_column_data( $column, $post_id ) {
		if ( 'visibility' === $column ) {
			$post_ids = self::get_posts();
			echo in_array( $post_id, $post_ids, true ) ?
				'<span class="dashicons dashicons-hidden"></span>' :
				'<span class="dashicons dashicons-visibility"></span>';
		}
	}

	/**
	 * Add custom styles to the admin columns.
	 *
	 * @return void The custom styles for the admin columns.
	 */
	public function custom_column_style() {
		print( '<style> .fixed .column-visibility { width: 5.5em; } </style>' );
	}

	/**
	 * Add custom filter to the admin columns.
	 *
	 * @param array $views The original array with view links.
	 *
	 * @return array The updated array with view links.
	 */
	public function custom_column_filter( $views ) {
		if ( ! is_admin() ) {
			return $views;
		}

		if ( isset( $_GET['post_type'] ) && ! in_array( $_GET['post_type'], self::supported_post_types() ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $views;
		}

		if ( ! count( get_option( self::OPTION_KEY, array() ) ) ) {
			return $views;
		}

		global $wp_query;

		$query           = array(
			'post__in' => get_option( self::OPTION_KEY, array() ),
		);
		$result          = new WP_Query( $query );
		$class           = ( isset( $_GET['show_hidden'] ) && '1' === $_GET['show_hidden'] ) ? 'class="current"' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$views['hidden'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			admin_url( 'edit.php?post_type=post&show_hidden=1' ),
			$class,
			esc_html( apply_filters( 'hidden_posts_filter_title', 'Hidden' ) ),
			$result->found_posts
		);

		if ( isset( $_GET['show_hidden'] ) && '1' === $_GET['show_hidden'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$wp_query = $result; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		return $views;
	}

	/**
	 * Render the meta box for hiding a post.
	 */
	public function add_metabox() {
		add_meta_box(
			'hidden-posts',
			esc_html( apply_filters( 'hidden_posts_checkbox_title', 'Visibility' ) ),
			array( $this, 'render_metabox' ),
			self::supported_post_types(),
			'side',
			'high'
		);
	}

	/**
	 * Render the meta box for hiding a post.
	 *
	 * @param WP_Post $post The post object for which the metabox should be added.
	 */
	public function render_metabox( WP_Post $post ) {
		$checked = in_array( (int) $post->ID, self::get_posts(), true );
		wp_nonce_field( self::NONCE_KEY, self::NONCE_KEY );
		printf(
			'<div id="superawesome-box" class="misc-pub-section"><label><input type="checkbox" name="%s" %s> %s</label></div>',
			esc_attr( self::OPTION_KEY ),
			checked( $checked, true, false ),
			esc_html( apply_filters( 'hidden_posts_checkbox_text', 'Hide post' ) )
		);

		do_action( 'hidden_posts_after_render_metabox', $post );
	}

	/**
	 * Get an array of supported post types for post visibility.
	 *
	 * @return array
	 */
	public static function supported_post_types() {
		return apply_filters( 'hidden_posts_post_types', array( 'post' ) );
	}

	/**
	 * Render the pagination without all hidden posts.
	 * 
	 * @param string $where The original SQL query for the pagination.
	 * @return string The updated SQL query for the pagination.
	 */
	public function render_pagination( $where ) {
		$posts = implode( ',', self::get_posts() );

		// Return the original SQL query, if no hidden posts are available.
		if ( '' === $posts ) {
			return $where;
		}

		// Return the original SQL query, if filter show_hidden_posts_in_pagination is set to true.
		if ( apply_filters( 'show_hidden_posts_in_pagination', false ) ) {
			return $where;
		}

		// Return the updated SQL query.
		return $where . " AND p.ID NOT IN ( $posts )";
	}

}

$hidden_posts = new Hidden_Posts();
$hidden_posts->run();

/**
 * Helper function to get hidden posts.
 *
 * @return array Array of Post IDs.
 */
function vip_get_hidden_posts() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	return Hidden_Posts::get_posts();
}
