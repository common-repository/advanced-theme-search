<?php
/**
Plugin Name: Advanced Theme Search
Description: Free yourself from the limitations of the standard theme search delivered by WordPress core. List themes that have been updated within the last X months or with Y number of downloads.
Version: 0.0.2
Author: klick on it
Author URI: http://klick-on-it.com
License: GPLv2 or later
Text Domain: klick-ats
 */

/*
This plugin developed by klick-on-it.com
*/

/*
Copyright 2017 klick on it (http://klick-on-it.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 3 - GPLv3)
as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined('ABSPATH')) die('No direct access allowed');
if (!class_exists('Klick_Ats')) :
define('KLICK_ATS_VERSION', '0.0.1');
define('KLICK_ATS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KLICK_ATS_PLUGIN_MAIN_PATH', plugin_dir_path(__FILE__));
define('KLICK_ATS_PLUGIN_SETTING_PAGE', admin_url() . 'admin.php?page=klick_ats');

class Klick_Ats {

	protected static $_instance = null;

	protected static $_options_instance = null;

	protected static $_notifier_instance = null;

	protected static $_logger_instance = null;

	protected static $_dashboard_instance = null;

	protected static $_db_operations_instance = null;
	
	/**
	 * Constructor for main plugin class
	 */
	public function __construct() {
		
		register_activation_hook(__FILE__, array($this, 'klick_ats_activation_actions'));

		register_deactivation_hook(__FILE__, array($this, 'klick_ats_deactivation_actions'));

		add_action('wp_ajax_klick_ats_ajax', array($this, 'klick_ats_ajax_handler'));
		
		add_action('admin_menu', array($this, 'init_dashboard'));
		
		add_action('plugins_loaded', array($this, 'setup_translation'));
		
		add_action('plugins_loaded', array($this, 'setup_loggers'));

		add_action( 'wp_footer', array($this, 'klick_ats_ui_scripts'));

		add_action( 'wp_head', array($this, 'klick_ats_ui_css'));

		// Custom hook generated when form submit as hidden field custom nonce
		add_action( 'admin_action_wpklickats10500',  array($this, 'wpklickats10500_admin_action'));
		
	}

	/**
	 * This function fire when hook form submit is executes, and get all forms params and redirect on proper plugin page
	 *
	 * @return void
	 */
	public function wpklickats10500_admin_action(){
		$query_params = 'search_by_name='.urlencode($_REQUEST['search_by_name'] ). 
		'&allow_exact_name='.urlencode($_REQUEST['allow_exact_name'] ). 
		'&search_by_author='.urlencode($_REQUEST['search_by_author'] ). 
		'&allow_exact_author='.urlencode($_REQUEST['allow_exact_author'] ). 
		'&search_by_tags='.urlencode($_REQUEST['search_by_tags'] ). 
		'&search_by_description='.urlencode($_REQUEST['search_by_description']). 
		'&min_number_of_ratings='.urlencode($_REQUEST['min_number_of_ratings']) .
		'&avg_ratings='.urlencode($_REQUEST['avg_ratings']) .
		'&active_installs='.urlencode($_REQUEST['active_installs']) .
		'&downloaded='.urlencode($_REQUEST['downloaded']) .
		'&page_number='.urlencode($_REQUEST['page_number']) .
		'&current-page-selector='.urlencode($_REQUEST['current-page-selector']) .
		'&total_pages='.urlencode($_REQUEST['total_pages']) .
		'&action='.urlencode($_REQUEST['action']) ;
		wp_redirect( $_SERVER['HTTP_REFERER'] .'?page=klick_ats&'. $query_params );
		exit();
	}

	/**
	 * Create string with 'ago' keywords
	 *
	 * @param  string 	$datetime
	 * @param  boolean 	$full, Default false
	 * @return string
	 */
	public function klick_ats_time_elapsed_string($datetime, $full = false) {
  	   $now = new DateTime;
       $ago = new DateTime($datetime);
       $diff = $now->diff($ago);

       $diff->w = floor($diff->d / 7);
       $diff->d -= $diff->w * 7;

       $string = array(
           'y' => 'year',
           'm' => 'month',
           'w' => 'week',
           'd' => 'day',
           'h' => 'hour',
           'i' => 'minute',
           's' => 'second',
       );

       foreach ($string as $k => &$v) {
           if ($diff->$k) {
               $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
           } else {
               unset($string[$k]);
           }
       }

       if (!$full) $string = array_slice($string, 0, 1);

       return $string ? implode(', ', $string) . ' ago' : 'just now';
	}
	
	/**
	 * Instantiate Klick_Ats if needed
	 *
	 * @return object Klick_Ats
	 */
	public static function instance() {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Instantiate Klick_Ats_Db_Operations if needed
	 *
	 * @return object Klick_Ats
	 */
	public static function db_operations() {
		if (empty(self::$_db_operations_instance)) {
			if (!class_exists('Klick_Ats_Db_Operations')) include_once(KLICK_ATS_PLUGIN_MAIN_PATH . '/includes/class-klick-ats-db-operations.php');
			self::$_db_operations_instance = new Klick_Ats_Db_Operations();
		}
		return self::$_db_operations_instance;
	}

	/**
	 * Instantiate Klick_Ats_Options if needed
	 *
	 * @return object Klick_Ats_Options
	 */
	public static function get_options() {
		if (empty(self::$_options_instance)) {
			if (!class_exists('Klick_Ats_Options')) include_once(KLICK_ATS_PLUGIN_MAIN_PATH . '/includes/class-klick-ats-options.php');
			self::$_options_instance = new Klick_Ats_Options();
		}
		return self::$_options_instance;
	}
	
	/**
	 * Instantiate Klick_Ats_Dashboard if needed
	 *
	 * @return object Klick_Ats_Dashboard
	 */
	public static function get_dashboard() {
		if (empty(self::$_dashboard_instance)) {
			if (!class_exists('Klick_Ats_Dashboard')) include_once(KLICK_ATS_PLUGIN_MAIN_PATH . '/includes/class-klick-ats-dashboard.php');
			self::$_dashboard_instance = new Klick_Ats_Dashboard();
		}
		return self::$_dashboard_instance;
	}
	
	/**
	 * Instantiate Klick_Ats_Logger if needed
	 *
	 * @return object Klick_Ats_Logger
	 */
	public static function get_logger() {
		if (empty(self::$_logger_instance)) {
			if (!class_exists('Klick_Ats_Logger')) include_once(KLICK_ATS_PLUGIN_MAIN_PATH . '/includes/class-klick-ats-logger.php');
			self::$_logger_instance = new Klick_Ats_Logger();
		}
		return self::$_logger_instance;
	}
	
	/**
	 * Instantiate Klick_Ats_Notifier if needed
	 *
	 * @return object Klick_Ats_Notifier
	 */
	public static function get_notifier() {
		if (empty(self::$_notifier_instance)) {
			include_once(KLICK_ATS_PLUGIN_MAIN_PATH . '/includes/class-klick-ats-notifier.php');
			self::$_notifier_instance = new Klick_Ats_Notifier();
		}
		return self::$_notifier_instance;
	}
	
	/**
	 * Establish Capability
	 *
	 * @return string
	 */
	public function capability_required() {
		return apply_filters('klick_ats_capability_required', 'manage_options');
	}
	
	/**
	 * Init dashboard with menu and layout
	 *
	 * @return void
	 */
	public function init_dashboard() {
		$dashboard = $this->get_dashboard();
		$dashboard->init_menu();
		load_plugin_textdomain('klick-ats', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}

	/**
	 * To enqueue js at user side
	 *
	 * @return void
	 */
	public function klick_ats_ui_scripts() {
		$dashboard = $this->get_dashboard();
		$dashboard->init_user_end();
	}

	/**
	 * To enqueue css at user side
	 *
	 * @return void
	 */
	public function klick_ats_ui_css(){
		$dashboard = $this->get_dashboard();
		$dashboard->init_user_css();
	}

	/**
	 * Perform post plugin loaded setup
	 *
	 * @return void
	 */
	public function setup_translation() {
		load_plugin_textdomain('klick-ats', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}

	/**
	 * Creates an array of loggers, Activate and Adds
	 *
	 * @return void
	 */
	public function setup_loggers() {
		
		$logger = $this->get_logger();

		$loggers = $logger->klick_ats_get_loggers();
		
		$logger->activate_logs($loggers);
		
		$logger->add_loggers($loggers);
	}
	
	/**
	 * Ajax Handler
	 */
	public function klick_ats_ajax_handler() {

		$nonce = empty($_POST['nonce']) ? '' : $_POST['nonce'];
		if (!wp_verify_nonce($nonce, 'klick_ats_ajax_nonce') || empty($_POST['subaction'])) die('Security check');
		
		$parsed_data = array();
		$data = array();
		
		$subaction = sanitize_key($_POST['subaction']);
		
		$post_data = isset($_POST['data']) ? $_POST['data'] : null;
		
		parse_str($post_data, $parsed_data); // convert string to array

		switch ($subaction) {
			case 'klick_ats_build_theme_table':
				$data['remaining_themes'] = sanitize_text_field($parsed_data['remaining_themes']);
				$data['page_count'] = sanitize_text_field($parsed_data['page_count']);
				break;
			case 'klick_ats_install_theme':
				$data['install_this_theme'] = $parsed_data['install_this_theme'];
				break;
			case 'klick_ats_save_settings':
				$data['ats_advance_search_toggle'] = sanitize_text_field($parsed_data['ats_advance_search_toggle']);
				break;	
			default:
				error_log("Klick_Ats_Commands: ajax_handler: no such sub-action (" . esc_html($subaction) . ")");
				die('No such sub-action/command');
		}
		
		$results = array();
		
		// Get sub-action class
		if (!class_exists('Klick_Ats_Commands')) include_once(KLICK_ATS_PLUGIN_MAIN_PATH . 'includes/class-klick-ats-commands.php');

		$commands = new Klick_Ats_Commands();

		if (!method_exists($commands, $subaction)) {
			error_log("Klick_Ats_Commands: ajax_handler: no such sub-action (" . esc_html($subaction) . ")");
			die('No such sub-action/command');
		} else {
			$results = call_user_func(array($commands, $subaction), $data);

			if (is_wp_error($results)) {
				$results = array(
					'result' => false,
					'error_code' => $results->get_error_code(),
					'error_message' => $results->get_error_message(),
					'error_data' => $results->get_error_data(),
					);
			}
		}
		
		echo json_encode($results);
		die;
	}

	/**
	 * Plugin activation actions.
	 *
	 * @return void
	 */
	public function klick_ats_activation_actions(){
		$this->get_options()->set_default_options();
	}

	/**
	 * Plugin deactivation actions.
	 *
	 * @return void
	 */
	public function klick_ats_deactivation_actions(){
		$this->get_options()->delete_all_options();
		$this->db_operations()->drop_table("all_themes");

	}

	/**
	 * Define Pagination same as WP default
	 *
	 * @return void
	 */
	public function klick_ats_pagination($page_number) {
		?>
		<div class="tablenav-pages custom-nav"><span class="displaying-num"><?php echo $this->get_options()->get_option('affected-total-rows'); ?> items</span>
			<?php if ($page_number != 1) { ?>
			<input class="tablenav-pages-navspan" id = "klick_ats_go_to_first" name = "klick_ats_go_to_first" type="submit"  value="&laquo;" />
			<?php } else { ?>
			<input class="tablenav-pages-navspan disabled" id = "klick_ats_go_to_first" name = "klick_ats_go_to_first" type="submit"  value="&laquo;" disabled="disabled" />
			<?php } ?>

			<?php if ($page_number != 1) { ?>
			<input class="tablenav-pages-navspan" id = "ats_prev_page" name = "ats_prev_page" type="submit" value="&lsaquo;" />
			<?php } else { ?>
			<input class="tablenav-pages-navspan disabled" id = "ats_prev_page" name = "ats_prev_page" type="submit" value="&lsaquo;" disabled="disabled" />
			<?php } ?>

			<span class="paging-input">
				<label for="current-page-selector" class="screen-reader-text">Current Page</label>
				<input class="current-page" id="current-page-selector" name="current-page-selector" type="text" name="paged" value="<?php echo $page_number; ?>" size="3" aria-describedby="table-paging" readonly>
				<span class="tablenav-paging-text"> of <span class="total-pages"><?php echo $this->get_options()->get_option('total-pages'); ?></span></span>
			</span>

			<?php if ($page_number != $this->get_options()->get_option('total-pages')) { ?>
			<input class="tablenav-pages-navspan" id = "ats_next_page" name = "ats_next_page" type="submit"  value="&rsaquo;" />
			<?php } else { ?>
			<input class="tablenav-pages-navspan disabled" id = "ats_next_page" name = "ats_next_page" type="submit"  value="&rsaquo;" disabled="disabled" />
			<?php } ?>
					
			<?php if ($page_number != $this->get_options()->get_option('total-pages')) { ?>
			<input class="tablenav-pages-navspan" id = "klick_ats_go_to_last" name = "klick_ats_go_to_last" type="submit" value="&raquo;" />
			<?php } else { ?>
			<input class="tablenav-pages-navspan disabled" id = "klick_ats_go_to_last" name = "klick_ats_go_to_last" type="submit" value="&raquo;" disabled="disabled" />
			<?php } ?>
		</div> <?php
	}
}

register_uninstall_hook(__FILE__,'klick_ats_uninstall_option');

/**
 * Delete data when uninstall
 *
 * @return void
 */
function klick_ats_uninstall_option(){
	Klick_Ats()->get_options()->delete_all_options();
}

/**
 * Instantiates the main plugin class
 *
 * @return instance
 */
function Klick_Ats(){
	 return Klick_Ats::instance();
}

endif;

$GLOBALS['Klick_Ats'] = Klick_Ats();
