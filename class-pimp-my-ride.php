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
		add_filter( 'wp_default_scripts', array( $this, 'dequeue_jquery_migrate' ) );

		// Dequeue WP Embed.
		remove_action( 'wp_head', 'wp_oembed_add_host_js', 10 );

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

		// Remove width and height attributes when inserting images into post.
		add_filter( 'post_thumbnail_html', array( $this, 'remove_width_and_height_attribute' ), 10 );
		add_filter( 'image_send_to_editor', array( $this, 'remove_width_and_height_attribute' ), 10 );

		// Modify Tiny_MCE toolbars.
		add_filter( 'tiny_mce_before_init', array( $this, 'customformatTinyMCE' ) );

		// Set jpeg compression.
		add_filter( 'jpeg_quality', array( $this, 'filter_jpeg_quality' ) );

		// Remove admin bar.
		// add_filter( 'show_admin_bar', '__return_false' );
		// add_action( 'admin_head-profile.php', array( $this, 'hide_user_profile_admin_bar_setting' ) );
	}

	/**
	 * Dequeue jQuery migrate.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param WP_Scripts $scripts Default scripts.
	 */
	public function dequeue_jquery_migrate( $scripts ) {
		if ( ! is_admin() ) {
			$scripts->remove( 'jquery' );
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
	 * @param array $plugins TinyMCE plugins.
	 * @return array
	 */
	public function disable_emojis_tinymce( $plugins ) {

		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		}

		return $plugins;
	}

	/**
	 * Remove width and height attributes when inserting image into post.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param string $html HTML for the post thumbnail or image inserted into the editor.
	 * @return string Modified html.
	 */
	public function remove_width_and_height_attribute( $html ) {
		$html = preg_replace( '/(width|height)="\d*"\s/', '', $html );
		return $html;
	}

	/**
	 * Remove items from the TinyMCE toolbar.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param array Options.
	 * @return array Modified options.
	 */
	public function customformatTinyMCE( $options ) {


		$items_to_remove = array( 'wp_more', 'underline', 'forecolor', 'outdent', 'indent' );
		$toolbars        = array( 'toolbar1', 'toolbar2' );

		foreach ( $toolbars as $toolbar ) {
			$items_array = explode( ',', $options[ $toolbar ] );
			foreach( $items_to_remove as $item ) {
				$item_key = array_keys( $items_array, $item );
				foreach( $item_key as $k => $v ) {
					unset( $items_array[ $v ] );
				}
			}
			$options[ $toolbar ] = implode( ',', $items_array );
		}

		// Remove h1, h5, h6.
		$options['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Preformatted=pre';

		return $options;
	}

	/**
	 * Set jpeg quality to 100.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function filter_jpeg_quality( $quality ) {
		return 100;
	}

	/**
	 * Hide admin bar setting on user profile page.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function hide_user_profile_admin_bar_setting() {
		echo '<style>.show-admin-bar{display:none;}</style>';
	}
}

add_action( 'init', array( 'Yo_WP_Pimp_My_Ride', 'get_instance' ) );
