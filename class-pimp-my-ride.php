<?php
/**
 * General WordPress modifications.
 *
 * @package    WordPress
 * @subpackage Yo_WP_Pimp_My_ride
 * @author     Barry Ceelen
 * @license    GPL-2.0+
 * @link       https://github.com/barryceelen/wp-pimp-my-ride
 * @copyright  Barry Ceelen
 */

/**
 * Add, remove and/or modify default WordPress functionality.
 *
 * @package    WordPress
 * @subpackage Yo_WP_Pimp_My_ride
 */
class Yo_WP_Pimp_My_Ride {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {

		$this->add_actions_and_filters();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Add actions and filters.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function add_actions_and_filters() {

		// Dequeue jQuery Migrate.
		add_filter( 'wp_default_scripts', array( $this, 'dequeue_jquery_migrate' )  );

		// Dequeue WP Embed
		remove_action( 'wp_head', 'wp_oembed_add_host_js', 10  );

		// Add javascript detection.
		add_action( 'wp_head', array( $this, 'javascript_detection' ), 0 );

		// Remove emoji support.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );

		// Remove percentage symbol from filename (plus symbols etc. would be bad as well).
		add_filter ( 'sanitize_file_name_chars', array( $this, 'disallow_percentage_symbol_in_filename' ) );
	}

	/**
	 * Dequeue jQuery migrate.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function dequeue_jquery_migrate( $scripts ) {
		if( ! is_admin() ) {
			$scripts->remove( 'jquery');
			$scripts->add( 'jquery', false, array( 'jquery-core' ), false );
		}
	}

	/**
	 * Set js class on html element.
	 *
	 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function javascript_detection() {
		echo "<script>(function(html){html.className = html.className + ' js';})(document.documentElement);</script>\n";
	}

	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param array $plugins
	 * @return array
	 */
	public function disable_emojis_tinymce( $plugins ) {

		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		}

		return $plugins;
	}

	/**
	 * Add percentage symbol to filename filter.
	 *
	 * File names with percentage symbols in them can cause trouble.
	 *
	 * @see https://core.trac.wordpress.org/ticket/16226
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param array $special_chars
	 * @return array $special_chars
	 */
	public function disallow_percentage_symbol_in_filename( $special_chars ) {
		$special_chars[] = '%';
		return $special_chars;
	}
}

add_action( 'init', array( 'Yo_WP_Pimp_My_Ride', 'get_instance' ) );
