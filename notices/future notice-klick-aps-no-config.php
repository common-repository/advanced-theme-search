<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Klick_Ats_No_Config')) return;

require_once(KLICK_ATS_PLUGIN_MAIN_PATH . '/includes/class-klick-ats-abstract-notice.php');

/**
 * Class Klick_Ats_No_Config
 */
class Klick_Ats_No_Config extends Klick_Ats_Abstract_Notice {
	
	/**
	 * Klick_Ats_No_Config constructor
	 */
	public function __construct() {
		$this->notice_id = 'advanced-theme-search';
		$this->title = __('Advanced theme serach plugin is installed but not configured', 'klick-ats');
		$this->klick_ats = "";
		$this->notice_text = __('Configure it Now', 'klick-ats');
		$this->image_url = '../images/our-more-plugins/cs.svg';
		$this->dismiss_time = 'dismiss-page-notice-until';
		$this->dismiss_interval = 30;
		$this->display_after_time = 0;
		$this->dismiss_type = 'dismiss';
		$this->dismiss_text = __('Hide Me!', 'klick-ats');
		$this->position = 'dashboard';
		$this->only_on_this_page = 'index.php';
		$this->button_link = KLICK_ATS_PLUGIN_SETTING_PAGE;
		$this->button_text = __('Click here', 'klick-ats');
		$this->notice_template_file = 'main-dashboard-notices.php';
		$this->validity_function_param = 'Advance-theme-search/advance-theme-search.php';
		$this->validity_function = 'is_plugin_configured';
	}
}
