<?php
//error_reporting(0);
if (!defined('KLICK_ATS_VERSION')) die('No direct access allowed');
/**
 * Access via Klick_Ats()->get_options().
 */
class Klick_Ats_Db_Operations {
	
	private $options;
	
	/**
	 * Constructor for Commands class
	 *
	 */
	public function __construct() {
		$this->options = Klick_Ats()->get_options();
	} 
	
	/**
	 * Create table in DB
	 *
	 * @param  string table_name without prefix
	 * @return void
	 */
	public function create_table($table_name) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . $table_name;

		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);
		
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (rowid mediumint(9) NOT NULL AUTO_INCREMENT,
			id tinytext NOT NULL,
			name tinytext NOT NULL,
			slug tinytext,
			version tinytext,
			preview_url varchar(65535),
			author tinytext,
			screenshot_url tinytext,
			rating tinytext,
			num_ratings tinytext,
			downloaded varchar(65535),
			active_installs varchar(191),
			homepage varchar(65535),
			sections varchar(65535),
			tags varchar(65535),
			versions  varchar(65535),
			description  varchar(65535),
			requires int DEFAULT 0,
			PRIMARY KEY  (rowid)) $charset_collate;";
			
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );
		Klick_Ats()->get_options()->update_option('data-loaded',current_time('mysql'));
	}
	
	/**
	 * Insert theme row
	 *
	 * @param  object $themevalue
	 * @return void
	 */
	public function insert_theme($themevalue) {
		
		global $wpdb;
		$table_name = $wpdb->prefix . "all_themes";
		
		return $wpdb->insert($table_name, array(
			'id' => $themevalue->slug,
			'name' => $themevalue->name,
			'slug' => $themevalue->slug,
			'version' => $themevalue->version,
			'preview_url' => $themevalue->preview_url,
			'author' =>  $themevalue->author,
			'screenshot_url' =>  $themevalue->screenshot_url,
			'rating' =>  $themevalue->rating,
			'downloaded' => $themevalue->downloaded,
			'num_ratings' =>  $themevalue->num_ratings,
			'active_installs' =>  $themevalue->active_installs,
			'homepage' => $themevalue->homepage,
			'sections' =>  serialize($themevalue->sections),
			'tags' => serialize( $themevalue->tags),
			'versions' =>   serialize($themevalue->versions),
			'description' => $themevalue->sections['description'],
		));
	}
	
	/**
	 * Fetch themes from API
	 *
	 * @param  string $page_count
	 * @param  string $remaining_themes
	 * @return mixed
	 */
	public function get_themes($page_count, $remaining_themes) {
		$args = (object)array(
		'page' => $page_count,
		'per_page' => min(240,$remaining_themes),
		'versions' => false,
		'fields' =>array(
			'icons' => true,
			'active_installs' => true,
			'sections' => true,
			'versions' => true,
			'rating'   => true,
			'description' => true,
			'tags' =>true,
			'parent' => true,
			'activate' => true,
			'hasUpdate' => true,
			'hasPackage' => true,
			'update' => true,
			'action'=>true,
			'downloaded' => true,
			)
		);
		
		$request = array('action' => 'query_themes', 'request' => serialize($args));
		
		$url = 'http://api.wordpress.org/themes/info/1.0/';
		
		$response = wp_remote_post($url, array('body' => $request, 'timeout' => 15) );

		if (is_wp_error($response)) {
			Klick_Ats()->get_logger()->log(__("Debug", "klick-ats"), __("Klick ATS received an error from WordPress.org API on page ".$page_count.". Skipping page.", "klick-ats"), array('php'));
		}
		
		return unserialize(wp_remote_retrieve_body($response));
	}

	/**
	 * Handler function for 'build_theme_data' ajax request
	 *
	 * @param  string $data
	 * @return array  $response
	 */
	public function build_theme_table($data) {
		global $wpdb;
		$table_name = $wpdb->prefix . "all_themes";
		$remaining_themes = $data['remaining_themes'];
		$page_count = $data['page_count'];
		
		if($page_count == 1){
			$this->create_table("all_themes");
		}
		
		$res = $this->get_themes($page_count,$remaining_themes);

		$success = false;
		
		if(is_object($res)) {
			
			$success = true;
			
			foreach ($res->themes as $themekey => $themevalue) {
				if ($this->insert_theme($themevalue)) {
					$remaining_themes--;
				}
			}
		}
		
		$query_for_num_rows = $this->get_query_total_rows($table_name);

		$response = array('remaining_themes'=>$remaining_themes,'success'=>$success,'inserted'=>$query_for_num_rows);
		
		return $response;
	}


	/**
	 * This create starts and limit on every page
	 *
	 * @param  string $page_number
	 * @param  string $per_page_limit
	 * @return array
	 */
	public function set_start_and_limit($page_number = 1, $per_page_limit){
		if (isset($page_number) && !empty($page_number)) {
			// For first page
			if ($page_number == 1) {
				$start = 0;
				$limit= $per_page_limit;
			} else {
			// For all other pages except 1	
				$start = ($page_number - 1) * $per_page_limit;
				$limit= $per_page_limit;
			}
		} else {
			$start = 0;
			$limit = $per_page_limit;
		}

		return $navigation = array('start'=>$start, 'limit' => $limit);
	}

	/**
	 * This function is used to sanitized sensitive data, removes style, script tags and space to requested form params
	 *
	 * @param  arry $form_data
	 * @return array
	 */
	public function create_sanitized_data($form_data){

		return $form_data = array('min_number_of_ratings'=> $form_data['min_number_of_ratings'],
						'avg_ratings' => $form_data['avg_ratings'],
						'active_installs' => wp_strip_all_tags($form_data['active_installs'], true),
						'downloaded' => $form_data['downloaded'],
						'search_by_tags' => wp_strip_all_tags($form_data['search_by_tags'], true),
						'search_by_name' => wp_strip_all_tags($form_data['search_by_name'], true),
						'search_by_author' => wp_strip_all_tags($form_data['search_by_author'], true),
						'search_by_description' => wp_strip_all_tags($form_data['search_by_description'], true),
						'search_by_keywords' => wp_strip_all_tags($form_data['search_by_keywords'], true),
						'allow_exact_name' => $form_data['allow_exact_name'],
						'allow_exact_author' => $form_data['allow_exact_author'],
					);
	}

	/**
	 * Drop table in database
	 *
	 * @param  string $table_name
	 * @return void
	 */
	public function drop_table($table_name) {
		global $wpdb;
		$table_name = $wpdb->prefix . $table_name;
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);
	}

	/**
	 * Check table existance
	 *
	 * @param  string $table_name
	 * @return void
	 */
	public function check_table_exist($table_name){
		global $wpdb;
		
		$table_name = $wpdb->prefix . $table_name;

		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name){
			return true;
		}

		return false;
	}

	/**
	 * Get number of response array from Theme API
	 *
	 * @return array
	 */
	public function get_number_of_themes() {
		$args = array();
		$args['fields']['icons'] = true;
		
		$args = (object)array(
		'page' => 1,
		'per_page' => 10,
		'versions' => false,
		'fields' =>array(
			'icons' => true,
			'active_installs' => true,
			'sections' => true,
			'versions' => true,
			'rating'   => true,
			'description' => true,
			'tags' =>true,
			'parent' => true,
			'activate' => true,
			'hasUpdate' => true,
			'hasPackage' => true,
			'update' => true,
			'action'=>true
			)
		);
		
		$request = array('action' => 'query_themes', 'request' => serialize($args));
		
		$url = 'http://api.wordpress.org/themes/info/1.0/';
		
		$response = wp_remote_post($url, array('body' => $request, 'timeout' => 15) );
		$res = unserialize(wp_remote_retrieve_body($response));
		return $res->info['results'];
	}

	/**
	 * Build query filter
	 *
	 * @param array   $sanitized_form_data
	 * @param string  $query_params
	 *
	 * @return string
	 */
	public function build_query_params($sanitized_form_data, $query_params){

		// By ratings
		$query_params .= !empty($sanitized_form_data['min_number_of_ratings']) ?  " AND num_ratings >= " . $sanitized_form_data['min_number_of_ratings']  :  "";

		// By php version
		$query_params .= !empty($sanitized_form_data['required_php_version']) ?  " AND requires_php >= " . $sanitized_form_data['required_php_version']  :  "";

		// By average ratings
		$query_params .= !empty($sanitized_form_data['avg_ratings']) ?  " AND rating >= " . $sanitized_form_data['avg_ratings']  :  "";

		// By active installs
		$query_params .= !empty($sanitized_form_data['active_installs']) ?  " AND active_installs >= " . $sanitized_form_data['active_installs']  :  "";

		// By downloaded
		$query_params .= !empty($sanitized_form_data['downloaded']) ?  " AND downloaded >= " . $sanitized_form_data['downloaded']  : "";

		// By tags
		$query_params .= !empty($sanitized_form_data['search_by_tags']) ?  " AND tags LIKE '%" . $sanitized_form_data['search_by_tags'] . "%'"  : "";

		// By name or exact
		if ($sanitized_form_data['allow_exact_name']== "yes") {
			$query_params .= !empty($sanitized_form_data['search_by_name']) ?  " AND name = '" . $sanitized_form_data['search_by_name'] . "'"  : "";
		} else {
			$query_params .= !empty($sanitized_form_data['search_by_name']) ?  " AND name LIKE '%"  . $sanitized_form_data['search_by_name'] . "%'"  : "";
		}

		// By author or exact
		if ($sanitized_form_data['allow_exact_author']== "yes") {
			$query_params .= !empty($sanitized_form_data['search_by_author']) ?  " AND author = '" . $sanitized_form_data['search_by_author'] . "'"  : "";
		} else {
				$query_params .= !empty($sanitized_form_data['search_by_author']) ?  " AND author LIKE '%" . $sanitized_form_data['search_by_author'] . "%'"  : "";
		}

		// By Description
		$query_params .= !empty($sanitized_form_data['search_by_description']) ?  " AND description LIKE '%" . $sanitized_form_data['search_by_description'] . "%'"  : "";

		// By Keywords
		$keywords = $sanitized_form_data['search_by_keywords'];
		$query_params .= !empty($keywords) ? " AND (description LIKE '%" . $keywords . "%'" . " OR slug LIKE '%" . $keywords . "%'" . " OR name LIKE '%" . $keywords . "%'" . " OR author LIKE '%" . $keywords . "%' )" : "";

		return $query_params;
	}

	/**
	 * To get object of including number of affected rows
	 *
	 * @param string  $table_name
	 * @param string   $query_params
	 *
	 * @return string
	 */
	public function get_query_affected_rows($table_name, $query_params){
		global $wpdb;

		// Get total number of rows and page number
		$query_for_num_rows = $wpdb->get_results(
		"SELECT  count(*) as total_num_rows
			FROM  $table_name WHERE $query_params 
		");

		return $query_for_num_rows;
	}

	/**
	 * Count and return total number of rows from table
	 *
	 * @param string $table_name
	 * @return string
	 */
	public function get_query_total_rows($table_name){
		global $wpdb;

		// Get total number of rows and page number
		$query_for_num_rows = $wpdb->get_results(
		"SELECT  count(*) as total_num_rows
			FROM  $table_name
		");

		return $query_for_num_rows[0]->total_num_rows;
	}

	/**
	 * Uses WP Core install method to install theme
	 *
	 * @param array $data
	 * @return boolean $result  success => true, fail => false
	 */
	public function install_this_theme($data){
		
		require_once(ABSPATH .'/wp-admin/includes/file.php');
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Theme_Upgrader( $skin );
		$result   = $upgrader->install($data['install_this_theme']);
		return $result;
	}
}
