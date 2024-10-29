<!-- First Tab content -->
<div id="klick_ats_tab_first">
	<div class="klick-notice-message"></div>

	<?php 
		require_once( ABSPATH . 'wp-admin/includes/class-wp-themes-list-table.php' );
		require_once( ABSPATH . 'wp-admin/includes/theme-install.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-theme-install-list-table.php' );  
		
		$number_of_themes = Klick_Ats()->db_operations()->get_number_of_themes();

		$table_name = "all_themes";

		$ats_create_db = isset( $_REQUEST['ats_create_db']) ? wp_strip_all_tags($_REQUEST['ats_create_db']) : "";

		$update_db = isset( $_REQUEST['update_db']) ? wp_strip_all_tags($_REQUEST['update_db']) : "No";

		$data_loaded_time = Klick_Ats()->get_options()->get_option('data-loaded'); 

		if (isset($_REQUEST['update_db']) &&  ($_REQUEST['update_db']== "Yes")) {
			Klick_Ats()->db_operations()->drop_table($table_name);
		}
	?>

	<form id='theme-filter' method='get' action='<?php echo admin_url( 'admin.php' ); ?>'>
		<?php $page_number = isset( $_REQUEST['page_number']) ? wp_strip_all_tags($_REQUEST['page_number']) : "1"; ?>

		<input id="page_number" name="page_number" type="hidden" value = "<?php echo $page_number ?>">	
		<input id="page" name="page" type="hidden" value = "klick_ats">	
		<input id="total_pages" name="total_pages" type="hidden" value = "<?php echo Klick_Ats()->get_options()->get_option('total-pages'); ?>">

		<?php if (Klick_Ats()->db_operations()->check_table_exist($table_name) == false) {?>

			<h3><span class="theme-status-lebel"></span></h3>
			<div class="downloaded-theme-status"><span></span></div>
			<br /><br /><?php _e('To use Advanced Theme Search you need to download the theme data','klick-ats'); ?> <br /><br />
			<?php _e('Downloading all theme data can take along time...','klick-ats'); ?>  <br /><br />

				<select name="klick_ats_theme_data" id="klick_ats_theme_data">
					<option selected disabled value=""><?php _e('Select the amount of theme data to download','klick-ats'); ?> </option>
					<option value="1200"><?php _e('Download 1,200 most popular themes (60 seconds)','klick-ats'); ?></option>
					<option value="<?php echo $number_of_themes ?>"><?php _e('Download ' .$number_of_themes. ' themes (2 minutes)','klick-ats'); ?> </option>
				</select>

	 			<script type="text/javascript">
	 			   var klick_ats_ajax_nonce='<?php echo wp_create_nonce('klick_ats_ajax_nonce'); ?>';
	 			</script>

				<input disabled id = "ats_create_db" name = "ats_create_db" type="button" class="button" value="<?php esc_attr_e( 'Download Theme Data' ); ?>" /><br /><br />

			<?php _e(' The time estimates depend on many factors including your internet connection speed','klick-ats'); ?> <br /><br />

		<?php } else { ?>

				<div class="klick-logo-and-title">
					<h2><?php echo  __('Advanced Search Parameters (you last updated the theme data','klick-ats') . " " . Klick_Ats()->klick_ats_time_elapsed_string($data_loaded_time) . __(' to re-download','klick-ats'); ?> <a href="<?php echo admin_url() . 'plugin-install.php?page=klick_ats&update_db=Yes' ?>" ><?php _e('click here','klick-ats'); ?></a>)</h2>
				</div>

			<?php $obj = new WP_Theme_Install_List_Table(); ?>
				
				<div class="valid-message-area"></div>
				<div id="msg_area"></div>
				<input type="hidden" name="action" value="wpklickats10500" />

				<!-- Advanced search form starts-->
				<ul class="klick-ats-advanceform">
					<li>
						<!-- Search by theme name -->
				 	      <label for="search_by_name"><?php _e('Name','klick-ats'); ?> :
				 	      	<?php $checked = (isset($_REQUEST['allow_exact_name']) && wp_strip_all_tags($_REQUEST['allow_exact_name'], true) == "yes" ? 'checked' : ''); ?> &nbsp;&nbsp;
				 	      	<?php $search_by_name = isset( $_REQUEST['search_by_name']) ? wp_strip_all_tags($_REQUEST['search_by_name']) : "" ?>
				 	      	<span><?php echo "<input type='checkbox' id='allow_exact_name' name='allow_exact_name' value='yes'  $checked /> EXACT"; ?></span>
				 	      </label>
					 	   	<?php echo "<input type='text' id='search_by_name' name='search_by_name' value='" . wp_strip_all_tags($search_by_name, true) . "'>"; ?>
					 	   	
					</li>
					<li>
						<!-- Search by author -->
				  	    <label for="search_by_author"><?php _e('Author','klick-ats'); ?> :
				  	    	<?php $checked = (isset($_REQUEST['allow_exact_author']) && wp_strip_all_tags($_REQUEST['allow_exact_author'], true) == "yes" ? 'checked' : ''); ?> &nbsp;&nbsp;
				  	    	<?php $search_by_author = isset( $_REQUEST['search_by_author']) ? wp_strip_all_tags($_REQUEST['search_by_author']) : "" ?>
				  	    	<span><?php echo "<input type='checkbox' id='allow_exact_author' name='allow_exact_author' value='yes'  $checked /> EXACT"; ?> </span>
				  	    </label>
				 	 	   	<?php echo "<input type='text' id='search_by_author' name='search_by_author' value='" . $search_by_author. "'>"; ?>
					</li>
					<li>
						<!-- Search by tags -->
		 	 	 	      <label for="search_by_tags"><?php _e('Tags','klick-ats'); ?> :</label>
		 	 	 	      <?php $search_by_tags = isset( $_REQUEST['search_by_tags']) ? wp_strip_all_tags($_REQUEST['search_by_tags']) : "" ?>
		 	 		 	   	<?php echo "<input type='text' id='search_by_tags' name='search_by_tags' value='" . $search_by_tags. "'>"; ?>
					</li>
					<li>
						<!-- Search by keywords -->
			 	 	      <label for="search_by_keywords"><?php _e('Keywords','klick-ats'); ?> :</label>
			 	 	      <?php $search_by_keywords = isset( $_REQUEST['search_by_keywords']) ? wp_strip_all_tags($_REQUEST['search_by_keywords']) : "" ?>
			 		 	   	<?php echo "<input type='text' id='search_by_keywords' name='search_by_keywords' value='" . $search_by_keywords. "'>"; ?>
					</li>
					<li>
						<!-- Search by descriptions -->
	 		 	 	      <label for="search_by_description"><?php _e('Description','klick-ats'); ?> :</label>
	 		 	 	       <?php $search_by_description = isset( $_REQUEST['search_by_description']) ? wp_strip_all_tags($_REQUEST['search_by_description']) : "" ?>
	 		 		 	   	<?php echo "<input type='text' id='search_by_description' name='search_by_description' value='" . $search_by_description. "'>"; ?>
					</li>
			
					<li>
						<!-- Minimum number of ratings  -->
						 <label for="minimum nunber of ratings"><?php _e('Minimum number of ratings','klick-ats'); ?> :</label>
						 <select name="min_number_of_ratings">
							<?php 
								$min_number_of_ratings = isset( $_REQUEST['min_number_of_ratings']) ? wp_strip_all_tags($_REQUEST['min_number_of_ratings']) : "";
								for ($i=0; $i<=1000; $i=$i+100) {
									 $selected = ($min_number_of_ratings == $i ) ? "selected" : "";
									echo "<option value='$i' $selected >" . $i . "</option>";
								}
							?>
						 </select>
					</li>
					<li>
						<!-- Minimum Avg rating % -->
						 <label for="avg_ratings"><?php _e('Minimum Avg rating %','klick-ats'); ?> :</label>
						 <?php $avg_ratings = isset($_REQUEST["avg_ratings"]) ? wp_strip_all_tags($_REQUEST["avg_ratings"], true) : "0"; ?>
					 	 <?php echo "<input type='text' id='avg_ratings' class='ats-form' name='avg_ratings' value='" . $avg_ratings . "'>"; ?>
					</li>
	
					<li>
						<!-- Active installs -->
				 	  <label for="active_installs"><?php _e('Minimum active installs','klick-ats'); ?> :</label>
				 	  	<?php $active_installs = isset($_REQUEST["active_installs"]) ? wp_strip_all_tags($_REQUEST["active_installs"], true) : "0"; ?>
				 	 	<input type="text" name="active_installs" id="active_installs" class="ats-form" value="<?php echo $active_installs; ?> ">
					</li>
					<li>
						<!-- Downloaded -->
				 	    <label for="downloaded"><?php _e('Minimum number of downloads','klick-ats'); ?> :</label>
				 	    <?php $downloaded = isset($_REQUEST["downloaded"]) ? wp_strip_all_tags($_REQUEST["downloaded"], true) : "0"; ?>
					 	   	<input type="text" name="downloaded" id="downloaded" class="ats-form" value="<?php echo $downloaded;  ?>">
					</li>
					

					<li>
						<!-- Advanced search form ends -->
						<input id = "ats_find_my_themes" type="submit" class="button" value="<?php esc_attr_e( 'Find My Themes' ); ?>" />
					</li>
				</ul>
				<!-- Advanced search form ends-->

			<?php
				global $wpdb;
				$table_name = $wpdb->prefix . "all_themes";
				$query_params  = "";
				$query_params .= !empty($_REQUEST['required_wp_version']) ? "requires >= " . wp_strip_all_tags($_REQUEST['required_wp_version'], true) : "requires >= 0.0"; 
				
				$per_page_limit = 24;
				$navigation = array();

				$page_number = isset( $_REQUEST['page_number']) ? $_REQUEST['page_number'] : "1";

				$navigation = Klick_Ats()->db_operations()->set_start_and_limit($page_number, $per_page_limit);

				$start = $navigation['start'];
				$limit = $navigation['limit'];

				$form_data = array('min_number_of_ratings'=> isset( $_REQUEST['min_number_of_ratings']) ? $_REQUEST['min_number_of_ratings'] : "", // num_ratings
					'avg_ratings' => isset( $_REQUEST['avg_ratings']) ? $_REQUEST['avg_ratings'] : "" , //rating
					'active_installs' => isset( $_REQUEST['active_installs']) ? $_REQUEST['active_installs'] : "", //active_installs
					'downloaded' => isset( $_REQUEST['downloaded']) ? $_REQUEST['downloaded'] : "", //downloaded
					'search_by_tags' => isset( $_REQUEST['search_by_tags']) ? $_REQUEST['search_by_tags'] : "",
					'search_by_name' => isset( $_REQUEST['search_by_name']) ? $_REQUEST['search_by_name'] : "",
					'search_by_author' => isset( $_REQUEST['search_by_author']) ? $_REQUEST['search_by_author'] : "" ,
					'search_by_description' => isset( $_REQUEST['search_by_description']) ? $_REQUEST['search_by_description'] : "" ,
					'search_by_keywords' => isset( $_REQUEST['search_by_keywords']) ? $_REQUEST['search_by_keywords'] : "",
					'allow_exact_name' => isset( $_REQUEST['allow_exact_name']) ? $_REQUEST['allow_exact_name'] : "" ,
					'allow_exact_author' => isset( $_REQUEST['allow_exact_author']) ? $_REQUEST['allow_exact_author'] : "",
				);

				$sanitized_form_data = Klick_Ats()->db_operations()->create_sanitized_data($form_data);

				$query_params = Klick_Ats()->db_operations()->build_query_params($sanitized_form_data, $query_params);
				
				$query_for_num_rows = Klick_Ats()->db_operations()->get_query_affected_rows($table_name, $query_params);

				Klick_Ats()->get_options()->update_option('affected-total-rows',$query_for_num_rows[0]->total_num_rows);
				Klick_Ats()->get_options()->update_option('total-pages',ceil($query_for_num_rows[0]->total_num_rows/$per_page_limit)); 

				$results = $wpdb->get_results("SELECT * FROM  $table_name WHERE $query_params LIMIT $start, $limit");

				$total_rows =  Klick_Ats()->get_options()->get_option('affected-total-rows');
				$advanced_search_toggle = Klick_Ats()->get_options()->get_option('advanced-search-toggle');

				// If advanced search enable
				if(!empty($total_rows) && isset($advanced_search_toggle) && $advanced_search_toggle == 1){
					Klick_Ats()->klick_ats_pagination($page_number); 
				} ?>
				
				<?php 
				if(count($results) > 0){ ?>
					<!-- Themebox starts -->
					<div class="theme-browser">
						<div class="themes wp-clearfix">
							<?php
								foreach ( $results as $theme ) :
									$install_url = add_query_arg( array(
										'action' => 'install-theme',
										'theme'  => $theme->slug,
									), self_admin_url( 'update.php' ) );

									foreach (unserialize($theme->versions) as $key => $value) {
										$latest_version_zip = $value;
									}
								?>

									<div class="theme" tabindex="0" id="theme_box" data-id="<?php echo $theme->slug; ?>">

										<?php if ( ! empty( $theme ->screenshot_url ) ) { ?>
											<div class="theme-screenshot">
												<a  href="<?php echo "#".$theme->slug; ?>"><img src="<?php echo $theme ->screenshot_url; ?>" alt="" /></a>
											</div>
										<?php } else { ?>
											<div class="theme-screenshot blank" ></div>
										<?php } ?>

										<h3 class="theme-name"><?php echo $theme->name; ?></h3>

										<div class="theme-actions">
												 <?php 
												$themes = wp_get_themes( array( 'allowed' => true ) );
													if ( ! isset( $themes[ $theme->slug] ) ) { ?>
															<a class="details-link button button-primary klick-ats-install" href="#" data-id="<?php echo $latest_version_zip; ?>" >
																<?php echo __('Install','klick-ats')?>
															</a>

													<?php } else { ?>

															<a class="details-link button button-primary klick-ats-install" href="#" disabled data-id="<?php echo $latest_version_zip; ?>" >
																<?php echo __('Installed','klick-ats')?>
															</a>

													<?php } ?>
												
											
												<a class="details-link theme-install button button-primary" href="<?php echo "#".$theme->slug; ?>">Details</a>
										</div>

										<!-- theme detail popup starts -->
										<div id="<?php echo $theme->slug; ?>" class="overlay" >
											<div class="popup">
												<h2><?php echo $theme->name; ?> Details</h2>
												<a class="close" href="#">&times;</a>
												<div class="content">
													<ul class="klick-theme-detail">
														<li>
															<label>Theme</label>
															<div><?php echo $theme->name; ?></div>
														</li>
														<li>
															<label>Version</label>
															<div><?php echo $theme->version; ?></div>
														</li>
														<li>
															<label>Downloaded</label>
															<div><?php echo $theme->downloaded; ?></div>
														</li>
														<li>
															<label>Description</label>
															<div class="ats-description"><?php echo $theme->description; ?></div>
														</li>
														<li>
															<label>Image</label>
															<div><?php echo "<img src='$theme->screenshot_url' height='200px' width='200px'>"; ?></div>
														</li>
														<li>
															<div>To see live preview  <a href="<?php echo $theme->preview_url; ?>" target="_blank">Click here</a></div>
														</li>
														<?php 
										
														echo "<a href='$latest_version_zip' class='button button-primary'>Download Latest Version</a>";
														?>

													</ul>
												</div>
											</div>
										</div>
										<!-- theme detail popup ends -->	
									</div>
								<?php endforeach; 
							 ?>

						</div>
					</div>
					<!-- Themebox ends -->
				<?php } else {
					echo "<h3><center>". __('No themes found','klick-ats') . "</center></h3>";
				}
				?>
		

		<?php } ?>
	</form>

</div>
<script type="text/javascript">
    var klick_ats_ajax_nonce='<?php echo wp_create_nonce('klick_ats_ajax_nonce'); ?>';
</script>
