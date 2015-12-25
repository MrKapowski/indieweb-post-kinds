<?php
/**
 * Post Kinds
 *
 * @link    http://indiewebcamp.com/Post_Kinds_Plugin
 * @package Post Kinds
 * Plugin Name: Post Kinds
 * Plugin URI: https://wordpress.org/plugins/indieweb-post-kinds/
 * Description: Ever want to reply to someone else's post with a post on your own site? Or to "like" someone else's post, but with your own site?
 * Version: 2.3.0
 * Author: David Shanske
 * Author URI: https://david.shanske.com
 * Text Domain: Post kinds
 */

define( 'POST_KINDS_VERSION', '2.3.0' );

load_plugin_textdomain( 'Post kind', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

if ( ! defined( 'MULTIKIND' ) ) {
	define( 'MULTIKIND', false );
}

// Add Kind Taxonomy.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-kind-taxonomy.php';

// Config Settings.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-kind-config.php';

// Add a Settings Link to the Plugins Page.
$plugin = plugin_basename( __FILE__ );
add_filter( 'plugin_action_links_$plugin', array( 'kind_config', 'settings_link' ) );


// Add Kind Post UI Configuration.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-kind-tabmeta.php';


// Add Kind Global Functions.
require_once plugin_dir_path( __FILE__ ) . '/includes/kind-functions.php';
// Add Kind Display Functions.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-kind-view.php';

// Add Kind Meta Storage and Retrieval Functions.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-kind-meta.php';

// Add an OpenGraph Parser.
if ( ! class_exists( 'ogp\Parser' ) ) {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-ogp-parser.php';
}

// Add an MF2 Parser
if ( ! class_exists( 'Mf2\Parser' ) ) {
  include_once plugin_dir_path( __FILE__ ) . 'includes/Mf2/Parser.php';
	include_once plugin_dir_path( __FILE__ ) . 'includes/Mf2/functions.php';
	include_once plugin_dir_path( __FILE__ ) . 'includes/Mf2/Twitter.php';
}

include_once plugin_dir_path( __FILE__ ) . 'includes/class-mf2-cleaner.php';


// Load stylesheets.
add_action( 'wp_enqueue_scripts', 'kindstyle_load' );
add_action( 'admin_enqueue_scripts', 'kindstyle_load' );


/**
 * Loads the Stylesheet for the Plugin.
 */
if ( ! function_exists( 'kindstyle_load' ) ) {
	/**
	 * Loads Plugin Style Sheet.
	 */
	function kindstyle_load() {
		wp_enqueue_style( 'kind', plugin_dir_url( __FILE__ ) . 'css/kind.min.css', array(), POST_KINDS_VERSION );
	}
} else {
	die( 'You have another version of Post Kinds installed!' );
}

// Add a notice to the Admin if the Webmentions Plugin isn't Activated.
add_action( 'admin_notices', 'postkind_plugin_notice' );


/**
 * Adds a notice if the Webmentions Plugin is Not Installed.
 */
function postkind_plugin_notice() {

	if ( ! class_exists( 'WebMentionPlugin' ) ) {
		echo '<div class="error"><p>';
		echo '<a href="https://wordpress.org/plugins/webmention/">';
		esc_html_e( 'This Plugin Requires the WordPress Webmention Plugin', 'post_kinds' );
		echo '</a></p></div>';
	}
}


if ( ! function_exists( 'extract_domain_name' ) ) {
	/**
	 * Returns the Domain Name out of a URL.
	 *
	 * @param string $url URL.
	 *
	 * @return string domain name
	 */
	function extract_domain_name( $url ) {

		$host = parse_url( $url, PHP_URL_HOST );
		$host = preg_replace( '/^www\./', '', $host );
		return $host;
	}
}

if ( ! function_exists( 'is_multi_array' ) ) {
	/**
	 * Returns True if Array is Multidimensional.
	 *
	 * @param array $arr array.
	 *
	 * @return boolean result
	 */
	function is_multi_array( $arr ) {

		if ( count( $arr ) === count( $arr, COUNT_RECURSIVE ) ) { return false;
		} else { return true;
		}
	}
}

if ( ! function_exists( 'array_filter_recursive' ) ) {
	/**
	 * Array_Filter for multi-dimensional arrays.
	 *
	 * @param  array    $array Untouched Array.
	 * @param  function $callback Function to Apply to Each Element.
	 * @return array Filtered Array.
	 */
	function array_filter_recursive( $array, $callback = null ) {

		foreach ( $array as $key => & $value ) {
			if ( is_array( $value ) ) {
				$value = array_filter_recursive( $value, $callback );
			} else {
				if ( ! is_null( $callback ) ) {
					if ( ! $callback( $value ) ) {
						unset( $array[ $key ] );
					}
				} else {
					if ( ! (bool) $value ) {
						unset( $array[ $key ] );
					}
				}
			}
		}
		unset( $value );
		return $array;
	}
}

if ( ! function_exists( 'is_url' ) ) {
	/**
	 * Is String a URL.
	 *
	 * @param  string $url A string.
	 * @return boolean Whether string is a URL.
	 */
	function is_url( $url ) {

		return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
	}
}


if ( ! function_exists( 'str_prefix' ) ) {
	/**
	 * Is prefix in string.
	 *
	 * @param  string $source The source string.
	 * @param  string $prefix The prefix you wish to check for in source.
	 * @return boolean The result.
	 */
	function str_prefix( $source, $prefix ) {

		return strncmp( $source, $prefix, strlen( $prefix ) ) === 0;
	}
}

if ( ! function_exists( 'ifset' ) ) {
	/**
	 * If set, return otherwise false.
	 *
	 * @param type $var Check if set.
	 * @return $var|false Return either $var or false.
	 */
	function ifset(&$var) {

		return isset( $var ) ? $var : false;
	}
}

function tz_seconds_to_offset($seconds) {
  return ($seconds < 0 ? '-' : '+') . sprintf('%02d:%02d', abs($seconds/60/60), abs($seconds/60)%60);
}
function tz_offset_to_seconds($offset) {
  if(preg_match('/([+-])(\d{2}):?(\d{2})/', $offset, $match)) {
    $sign = ($match[1] == '-' ? -1 : 1);
    return (($match[2] * 60 * 60) + ($match[3] * 60)) * $sign;
  } else {
    return 0;
  }
}

function kind_get_timezones()
{
    $o = array();
     
    $t_zones = timezone_identifiers_list();
     
    foreach($t_zones as $a)
    {
        $t = '';
         
        try
        {
            //this throws exception for 'US/Pacific-New'
            $zone = new DateTimeZone($a);
             
            $seconds = $zone->getOffset( new DateTime("now" , $zone) );
            $o[] = tz_seconds_to_offset($seconds);
        }
         
        //exceptions must be catched, else a blank page
        catch(Exception $e)
        {
            //die("Exception : " . $e->getMessage() . '<br />');
            //what to do in catch ? , nothing just relax
        }
    }
    $o = array_unique($o);
    asort($o);
     
    return $o;
} 

function kind_icon($slug) {
	if ( empty($slug) ) {
		return '';
	}
	return '<span class="kind-icon">' . file_get_contents( plugin_dir_url( __FILE__) . 'svg/' . $slug . '.svg' ) . '</span>';
}

?>
