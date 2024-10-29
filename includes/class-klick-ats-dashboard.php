<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Klick_Ats_Dashboard')) return;

/**
 * Class Klick_Ats_Dashboard
 */
class Klick_Ats_Dashboard {

	/**
	 * Klick_Ats_Dashboard constructor
	 */
	public function __construct() {
	}

	/**
	 * Initalize menu and submenu
	 */
	public function init_menu(){

		$capability_required = Klick_Ats()->capability_required();

		if (!current_user_can($capability_required)) return;

		$enqueue_version = (defined('WP_DEBUG') && WP_DEBUG) ? KLICK_ATS_VERSION . '.' . time() : KLICK_ATS_VERSION;
		$min_or_not = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

		// Register and enqueue script
		wp_enqueue_script( 'jquery' );
		wp_register_script( "klick_ats_script", KLICK_ATS_PLUGIN_URL . 'js/klick-ats' . $min_or_not . '.js', array('jquery'), $enqueue_version);
		wp_enqueue_script( 'klick_ats_script' );

		// Register and enqueue style
		wp_enqueue_style('klick_ats_css', KLICK_ATS_PLUGIN_URL . 'css/klick-ats' . $min_or_not . '.css', array(), $enqueue_version);   
		wp_enqueue_style('klick_ats_notices_css', KLICK_ATS_PLUGIN_URL . 'css/klick-ats-notices' . $min_or_not . '.css', array(), $enqueue_version);

		$icon = KLICK_ATS_PLUGIN_URL . "/images/small_icon.png";
		add_options_page('Advanced Theme Search', 'Advanced Theme Search', $capability_required, 'klick_ats', array($this, 'klick_ats_tab_view'),$icon);

		// Define hook and function to render admin notice
		add_action('all_admin_notices', array($this, 'show_admin_dashboard_notice'));

		// Define localize script to get localize string
		wp_localize_script('klick_ats_script', 'klick_ats_admin', array(
			'notice_for_active_installs' => __('Active installs, Only numbers(positive) are allowed','klick-ats'),
			'notice_for_avg_ratings' => __('Enter a percentage value (min 0 max 100)','klick-ats'),
			'notice_for_downloaded' => __('Download, Only numbers(positive) are allowed','klick-ats'),
			'notice_for_screenshots' => __('Screenshots, Only numbers(positive) are allowed','klick-ats'),
			'ats_page_url' => admin_url() . 'admin.php?page=klick_ats&ats_search=yes',
			'notice_for_success_install' => __('Theme is installed, Please activate it','klick-ats'),
			'notice_for_fail_install' => __('There is something wrong while theme installing, please try again','klick-ats'),
			'advanced_search_toggle' => Klick_Ats()->get_options()->get_option('advanced-search-toggle'),			
		));

	}
	
	/**
	 * Initlize script and localize script
	 *
	 * @return void
	 */
	public function init_user_end(){

		$enqueue_version = (defined('WP_DEBUG') && WP_DEBUG) ? KLICK_ATS_VERSION . '.' . time() : KLICK_ATS_VERSION;
		$min_or_not = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

		// Register and enqueue script
		wp_enqueue_script( 'jquery' );
		wp_register_script("klick_ats_ui_script", KLICK_ATS_PLUGIN_URL . 'js/klick-ats-ui' . $min_or_not . '.js', array('jquery'), $enqueue_version);
		wp_enqueue_script( 'klick_ats_ui_script' );

		// Define localize script to get localize string
		wp_localize_script('klick_ats_ui_script', 'klick_ats_ui', array(
			'ajaxurl' => admin_url('admin-ajax.php', 'relative'),
			'klick_ats_ajax_nonce' => wp_create_nonce('klick_ats_ajax_nonce'),
			'klick_ats_plugin_url' => KLICK_ATS_PLUGIN_URL,
		));
	}

	/**
	 * Renders css at user side
	 *
	 * @return void
	 */
	public function init_user_css(){

		$enqueue_version = (defined('WP_DEBUG') && WP_DEBUG) ? KLICK_ATS_VERSION . '.' . time() : KLICK_ATS_VERSION;
		$min_or_not = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

		// Register and enqueue style
		wp_enqueue_style('klick_ats_ui_css', KLICK_ATS_PLUGIN_URL . 'css/klick-ats-ui' . $min_or_not . '.css', array(), $enqueue_version);   
	}

	/**
	 * Renders Notice at main WP dashboard
	 *
	 * @return void
	 */
	public function show_admin_dashboard_notice(){
		Klick_Ats()->get_notifier()->do_notice('dashboard');
	}
	
	/**
	 * Renders tabs page with template
	 *
	 * @return void
	 */
	public function klick_ats_tab_view() { 

		$capability_required = Klick_Ats()->capability_required();

		if (!current_user_can($capability_required)) {
			echo "Permission denied.";
			return;
		}

		?>
		<br>
		<?php
		
		// Define tabs, set default/active tab
		$tabs = $this->get_tabs();
		
		$search_enable = Klick_Ats()->get_options()->get_option('advanced-search-toggle');

		if($search_enable  == true){
			$active_tab = apply_filters('klick_ats_admin_default_tab', 'ats-manage');
		} else {
			$active_tab = apply_filters('klick_ats_admin_default_tab', 'ats-manage-settings');
		}
		
		
		echo '<div class="wrap" role="main"><div id="klick_ats_tab_wrap" class="klick-ats-tab-wrap">';

		$this->include_template('klick-ats-tabs-header.php', false, array('active_tab' => $active_tab, 'tabs' => $tabs));

		$tab_data = array();
			
		foreach ($tabs as $tab_id => $tab_description) {

			echo '<div class="klick-ats-nav-tab-contents" id="klick_ats_nav_tab_contents_' . $tab_id . '" ' . (($tab_id == $active_tab) ? '' : 'style="display:none;"') . '>';
			
			do_action('klick_ats_admin_tab_render_begin', $active_tab);
			
			$tab_data[$tab_id] = isset($tab_data[$tab_description])? $tab_data[$tab_description]:array();
			
			$this->include_template('klick-ats-tab-' . $tab_id . '.php',false, array('data' => $tab_data[$tab_id]));

			echo '</div>';
		}
		
		do_action('klick_ats_admin_tab_render_end', $active_tab);
		
		echo '</div></div>';
	}
	
	/**
	 * Set tab names
	 *
	 * @return array
	 */
	public function get_tabs() {

		$search_toggle = Klick_Ats()->get_options()->get_option('advanced-search-toggle');
		if($search_toggle == true){
			return apply_filters('klick_ats_admin_page_tabs', 
				array('ats-manage' => '<span class="dashicons dashicons-wordpress-alt"></span> ' . __('Search Themes', 'klick-ats'), 
					'ats-manage-settings' => '<span class="dashicons dashicons-wordpress-alt"></span> ' . __('Advanced Theme Search Settings', 'klick-ats'), 
						'our-other-plugins' => __('Our other Plugins', 'klick-ats'), 
						'change-log' => __('Change Log', 'klick-ats')
					)
			);
		}

		return apply_filters('klick_ats_admin_page_tabs', 
		array('ats-manage-settings' => '<span class="dashicons dashicons-wordpress-alt"></span> ' . __('Advanced Theme Search Settings', 'klick-ats'), 
				'our-other-plugins' => __('Our other Plugins', 'klick-ats'), 
				'change-log' => __('Change Log', 'klick-ats')
			)
		);
	
	}
	
	/**
	 * Brings in templates
	 *
	 * @return void
	 */
	public function include_template($path, $return_instead_of_echo, $extract_these = array()) {
		if ($return_instead_of_echo) ob_start();

		if (preg_match('#^([^/]+)/(.*)$#', $path, $matches)) {
			$prefix = $matches[1];
			$suffix = $matches[2];
			if (isset(Klick_Ats()->template_directories[$prefix])) {
				$template_file = Klick_Ats()->template_directories[$prefix] . '/' . $suffix;
			}
		}
		
		if (!isset($template_file)) {
			$template_file = KLICK_ATS_PLUGIN_MAIN_PATH . '/templates/' . $path;
		}

		$template_file = apply_filters('klick_ats_template', $template_file, $path);

		do_action('klick_ats_before_template', $path, $template_file, $return_instead_of_echo, $extract_these);

		if (!file_exists($template_file)) {
			error_log("Klick: template not found: " . $template_file);
		} else {
			extract($extract_these);

			// Defines the vars used in included template file
			$klick_ats = Klick_Ats();
			$options = Klick_Ats()->get_options();
			$dashboard = $this;
			include $template_file;
		}

		do_action('klick_ats_after_template', $path, $template_file, $return_instead_of_echo, $extract_these);

		if ($return_instead_of_echo) return ob_get_clean();
	}

	/**
	 * 
	 * This function can be update to suit any URL as longs as the URL is passed
	 *
	 * @param string $url   URL to be check to see if it an klickonit match.
	 * @param string $text  Text to be entered within the href a tags.
	 * @param string $html  Any specific HTMl to be added.
	 * @param string $class Specify a class for the href.
	 */
	public function klick_ats_url($url, $text, $html = null, $class = null) {
		// Check if the URL is klickonit.
		if (false !== strpos($url, '//klick-on-it.com')) {
			// Apply filters.
			$url = apply_filters('klick_ats_klick_on_it_com', $url);
		}
		// Return URL - check if there is HTMl such as Images.
		if (!empty($html)) {
			echo '<a ' . $class . ' href="' . esc_attr($url) . '">' . $html . '</a>';
		} else {
			echo '<a ' . $class . ' href="' . esc_attr($url) . '">' . htmlspecialchars($text) . '</a>';
		}
	}
}
