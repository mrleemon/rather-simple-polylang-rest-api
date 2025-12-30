<?php
/**
 * Plugin Name: Rather Simple Polylang REST API
 * Plugin URI:
 * Update URI: false
 * Version: 3.0
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Requires Plugins: polylang
 * Author: Oscar Ciutat
 * Author URI: http://oscarciutat.com/code/
 * Text Domain: rather-simple-polylang-rest-api
 * Domain Path: /languages
 * Description: Adds basic REST API support to the free version of Polylang
 * License: GPLv2 or later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package rather_simple_polylang_rest_api
 */

/**
 * Core class used to implement the plugin.
 */
class Rather_Simple_Polylang_REST_API {

	/**
	 * Plugin instance.
	 *
	 * @var object $instance
	 */
	protected static $instance = null;

	/**
	 * Access this plugin’s working instance.
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Used for regular plugin work.
	 */
	public function plugin_setup() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	/**
	 * Constructor. Intentionally left empty and public.
	 */
	public function __construct() {}

	/**
	 * Initialize REST API filters for Polylang language support.
	 */
	public function rest_api_init() {

		foreach ( get_post_types( array( 'show_in_rest' => true ) ) as $post_type ) {
			add_filter(
				"rest_{$post_type}_query",
				array( $this, 'rest_polylang_apply_lang' ),
				10,
				2
			);
		}

		foreach ( get_taxonomies( array( 'show_in_rest' => true ) ) as $taxonomy ) {
			add_filter(
				"rest_{$taxonomy}_query",
				array( $this, 'rest_polylang_apply_lang' ),
				10,
				2
			);
		}
	}

	/**
	 * Apply Polylang language filter to REST API queries.
	 *
	 * @param array           $args    The REST API query arguments.
	 * @param WP_REST_Request $request The current REST API request.
	 * @return array Modified query arguments with language applied.
	 */
	public function rest_polylang_apply_lang( $args, $request ) {
		if ( ! function_exists( 'pll_languages_list' ) ) {
			return $args;
		}

		$lang = $request->get_param( 'lang' );

		if ( ! $lang ) {
			$lang = pll_default_language();
		}

		if ( ! in_array( $lang, pll_languages_list(), true ) ) {
			return $args;
		}

		$args['lang'] = $lang;
		return $args;
	}
}

add_action( 'plugins_loaded', array( Rather_Simple_Polylang_REST_API::get_instance(), 'plugin_setup' ) );
