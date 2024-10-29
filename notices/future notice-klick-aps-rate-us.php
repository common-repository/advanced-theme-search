<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Klick_Ats_Rate_Us')) return;

require_once(KLICK_ATS_PLUGIN_MAIN_PATH . '/includes/class-klick-ats-abstract-notice.php');

/**
 * Class Klick_Ats_Rate_Us
 */
class Klick_Ats_Rate_Us extends Klick_Ats_Abstract_Notice {

	/**
	 * Klick_Ats_Rate_Us constructor
	 */
	public function __construct() {
		$this->notice_id = 'givemerate';
		$this->title = __('Please Rate WP Configuration and Status', 'klick-ats');
		$this->klick_ats = "";
		$this->notice_text = __('If you could spare just a few minutes it would help us alot - thanks', 'klick-ats');
		$this->image_url = '../images/our-more-plugins/cs.svg';
		$this->dismiss_time = 'dismiss-page-notice-until';
		$this->dismiss_interval = 30;
		$this->display_after_time = 0;
		$this->dismiss_type = 'dismiss forever';
		$this->dismiss_text= __('I have already rated', 'klick-ats');
		$this->position = 'top';
		$this->only_on_this_page = '';
		$this->button_link = 'https://wordpress.org/support/plugin/klick-ats/reviews/?rate=5#new-post';
		$this->button_text = __('Click Here', 'klick-ats');
		$this->notice_template_file = 'horizontal-notice.php';
		$this->validity_function_param = '';
		$this->validity_function = '';
	}
}

