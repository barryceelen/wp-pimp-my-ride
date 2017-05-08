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
 * @version    1.2.0
 */

/**
 * Add, remove and/or modify default WordPress functionality.
 *
 * @package    WordPress
 * @subpackage Yo_WP_Pimp_My_ride
 */
class Pimp_My_Ride {

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

		// Modify Tiny_MCE toolbars.
		add_filter( 'tiny_mce_before_init', array( $this, 'custom_format_tinymce' ) );

		// Set jpeg compression.
		add_filter( 'jpeg_quality', array( $this, 'filter_jpeg_quality' ) );

		// Replace accented characters in file names.
		add_filter( 'sanitize_file_name', array( $this, 'remove_accents' ) );

		// Remove admin pointers.
		remove_action( 'admin_enqueue_scripts', array( 'WP_Internal_Pointers', 'enqueue_scripts' ) );

		// Remove customizer from admin bar.
		add_action( 'admin_bar_menu', array( $this, 'remove_customizer_from_admin_bar' ), 999 );
	}

	/**
	 * Dequeue jQuery migrate.
	 *
	 * @since 1.0.0
	 *
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
	 */
	public function javascript_detection() {

		if ( ! is_admin() ) {
			echo "<script>(function(html){html.className = html.className + ' js';})(document.documentElement);</script>\n";
		}
	}

	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 *
	 * @since 1.0.0
	 *
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
	 * Remove items from the TinyMCE toolbar.
	 *
	 * @since 1.0.0
	 *
	 * @param array $mce_init An array with TinyMCE config.
	 * @return array Modified array.
	 */
	public function custom_format_tinymce( $mce_init ) {

		$items_to_remove = array( 'wp_more', 'underline', 'forecolor', 'outdent', 'indent' );
		$toolbars        = array( 'toolbar1', 'toolbar2' );

		foreach ( $toolbars as $toolbar ) {
			$items_array = explode( ',', $mce_init[ $toolbar ] );
			foreach ( $items_to_remove as $item ) {
				$item_key = array_keys( $items_array, $item, true );
				foreach ( $item_key as $k => $v ) {
					unset( $items_array[ $v ] );
				}
			}
			$mce_init[ $toolbar ] = implode( ',', $items_array );
		}

		// Remove h1, h5, h6.
		$mce_init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Preformatted=pre';

		return $mce_init;
	}

	/**
	 * Set jpeg quality to 100.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quality Quality level between 0 (low) and 100 (high) of the JPEG.
	 */
	public function filter_jpeg_quality( $quality ) {
		return 100;
	}

	/**
	 * Enhanced 'remove_accents'. If the php Normalizer extension installed, use it.
	 *
	 * @since 1.0.0
	 *
	 * @see remove_accents()
	 *
	 * @param string $string Text that might have accent characters.
	 * @return string Filtered string with replaced "nice" characters.
	 */
	public function remove_accents( $string ) {
		if ( function_exists( 'normalizer_normalize' ) ) {
			if ( ! normalizer_is_normalized( $string, Normalizer::FORM_C ) ) {
				$string = normalizer_normalize( $string, Normalizer::FORM_C );
			}
		}
		return remove_accents( $string );
	}

	/**
	 * Remove customizer from admin bar.
	 *
	 * @since 1.2.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 */
	public function remove_customizer_from_admin_bar( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'customize' );
	}
}

global $yo_wp_pimp_my_ride;
$yo_wp_pimp_my_ride = Ocana_Admin::get_instance();
