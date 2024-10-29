/**
 * Send an action via admin-ajax.php
 * 
 * @param {string} action - the action to send
 * @param * data - data to send
 * @param Callback [callback] - will be called with the results
 * @param {boolean} [json_parse=true] - JSON parse the results
 */
var klick_ats_send_command = function (action, data, callback, json_parse) {
	json_parse = ('undefined' === typeof json_parse) ? true : json_parse;
	var ajax_data = {
		action: 'klick_ats_ajax',
		subaction: action,
		nonce: klick_ats_ajax_nonce,
		data: data
	};
	jQuery.post(ajaxurl, ajax_data, function (response) {
		
		if (json_parse) {
			try {
				var resp = JSON.parse(response);
			} catch (e) {
				return;
			}
		} else {
			var resp = response;
		}
		
		if ('undefined' !== typeof callback) callback(resp);
	});
}

/**
 * When DOM ready
 * 
 */
jQuery(document).ready(function ($) {
	klick_ats = klick_ats(klick_ats_send_command);
	ats_clicked = false;

	$("#klick_ats_theme_data").change(function(e) {
		$("#ats_create_db").prop( "disabled", false );
	});
	
	$("#ats_create_db").click(function(e) {
		if (!ats_clicked) {
			ats_clicked = true;
		}
	});
	
	$("#ats_find_my_themes").click(function(e) {
		if (!ats_clicked) {
			ats_clicked = true;
			$("#page_number").val(1); // To start from first page
		}
	});
	
	$("#ats_next_page").click(function(e) {
		if (!ats_clicked) {
			ats_clicked = true;
			$("#page_number").val(parseInt($("#page_number").val()) + 1);
		}
	});
	
	$("#klick_ats_go_to_last").click(function(e) {
		if (!ats_clicked) {
			ats_clicked = true;
			$("#page_number").val($("#total_pages").val());
		}
	});

	$("#ats_prev_page").click(function(e) {
		if (!ats_clicked) {
			ats_clicked = true;
			$("#page_number").val(parseInt($("#page_number").val()) - 1);
		}
	});

	$("#klick_ats_go_to_first").click(function(e) {
		if (!ats_clicked) {
			ats_clicked = true;
			$("#page_number").val(1);
		}
	});

	if(klick_ats_admin.advanced_search_toggle != true){
		$(".klick-ats-advanceform").addClass('disabled');
	}

});

/**
 * Function for sending communications
 * 
 * @callable sendcommandCallable
 * @param {string} action - the action to send
 * @param * data - data to send
 * @param Callback [callback] - will be called with the results
 * @param {boolean} [json_parse=true] - JSON parse the results
 */
/**
 * Main klick_ats
 * 
 * @param {sendcommandCallable} send_command
 */
var klick_ats = function (klick_ats_send_command) {
	var $ = jQuery;
	$("#msg_area").hide();
	$("#klick_ats_advanced_Save").attr('disabled','disabled');

	/**
	 * When toggle radio change, Make enable save button
	 *
	 * @return void
	 */
	$(".klick-ats-advance-search-toggle").change(function(){
		$("#klick_ats_advanced_Save").prop('disabled',false);
	});

	/**
	 * Reflects default pagination to page_number (For bug issue)
	 *
	 * @return void
	 */
	$("#current-page-selector").keyup(function(){
		$("#page_number").val($(this).val());
	});

	/**
	 * Process the tab click handler
	 *
	 * @return void
	 */
	$('#klick_ats_nav_tab_wrapper .nav-tab').click(function (e) {
		e.preventDefault();
		
		var clicked_tab_id = $(this).attr('id');
	
		if (!clicked_tab_id) { return; }
		if ('klick_ats_nav_tab_' != clicked_tab_id.substring(0, 18)) { return; }
		
		var clicked_tab_id = clicked_tab_id.substring(18);

		$('#klick_ats_nav_tab_wrapper .nav-tab:not(#klick_ats_nav_tab_' + clicked_tab_id + ')').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');

		$('.klick-ats-nav-tab-contents:not(#klick_ats_nav_tab_contents_' + clicked_tab_id + ')').hide();
		$('#klick_ats_nav_tab_contents_' + clicked_tab_id).show();
	});

	/**
	 * Gathers the details from form
	 * 
	 * @returns (string) - serialized row data
	 */
	function gather_row(){
		var form_data = $(".klick-ats-form-wrapper form").serialize();
		return form_data;	
	}

	/**
	 * Process the advance search toggle save click handler
	 *
	 * @return void
	 */
	$("#klick_ats_advanced_Save").click(function() {
		var form_data = gather_row();
		klick_ats_send_command('klick_ats_save_settings', form_data, function (resp) {
			$('.klick-save-notice-message').html(resp.status['messages']);
			if(resp.status['search_toggle'] == false){
				$("#klick_ats_nav_tab_ats-manage").css("display","none");
			} else {
				$("#klick_ats_nav_tab_ats-manage").css("display","block");
			}

			$('.fade').delay(2000).slideUp(200, function(){
				$("#klick_ats_advanced_Save").prop('disabled','disabled');
			});
		});
	});

	/**
	 * Download theme data from WordPress.org
	 *
	 * @return void
	 */
	$("#ats_create_db").click(function(e) {
		e.preventDefault();
		$(this).prop( "disabled", 'disabled');
		var page_size = 240;
		var required_themes = parseInt($("#klick_ats_theme_data").val());
		var page_count = 1;
		
		init_status();
		update_status(page_size / required_themes * 100);
		
		build_theme_table(required_themes, required_themes, page_count, page_size);
		
		return;
	});
	
	/**
	 * Click handler to communicate and store themes in DB, Ajax send request and update status bar
	 *
	 * @return void
	 */
	function build_theme_table(required_themes, remaining_themes, page_count, page_size) {
		var data = 'remaining_themes=' + remaining_themes + '&page_count=' + page_count;
		
		klick_ats_send_command('klick_ats_build_theme_table', data, function (resp) {
			if (parseInt(resp.status.remaining_themes) > 0) {
				remaining_themes = parseInt(resp.status.remaining_themes);
				success = resp.status.success;
				
				if (success) {
					page_count++;
				} else {
					page_count++;//if error skip the page
				}
				
				status = parseInt((page_size + required_themes - remaining_themes) / required_themes * 100);
				status = Math.min(100,status);
		
				build_theme_table(required_themes, remaining_themes, page_count, page_size);
				update_status(status);
			} else {
				window.location.replace(klick_ats_admin.ats_page_url);
			}
		});
	};
	
	/**
	 * Validate before form submit
	 *
	 * @return boolean
	 */
	$("#theme-filter").submit(function(){
		// avg_ratings
		var avg_ratings_element_val = $.trim($("#avg_ratings").val());
		if(check_for_empty(avg_ratings_element_val) === true){
 				$("#avg_ratings").val("0");
		}	
		else if(check_for_alpha(avg_ratings_element_val) === true){
			set_notice_message_generate('#msg_area',klick_ats_admin.notice_for_avg_ratings);
			return false;
		}
		else if(check_number_gt_100(avg_ratings_element_val) === true){
			set_notice_message_generate('#msg_area',klick_ats_admin.notice_for_avg_ratings);
			return false;
		}	

		// // active_installs
		var active_install_element_val = $.trim($("#active_installs").val());
		if(check_for_empty(active_install_element_val) === true){
 				$("#active_installs").val("0");
		}	
		else if(check_for_alpha(active_install_element_val) === true){
			set_notice_message_generate('#msg_area',klick_ats_admin.notice_for_active_installs);
			return false;
		}

		// // downloaded
		 var downloaded = $.trim($("#downloaded").val());
		if(check_for_empty(downloaded) === true){
 				$("#downloaded").val("0");

		}	
		else if(check_for_alpha(downloaded) === true){
		 	set_notice_message_generate('#msg_area',klick_ats_admin.notice_for_downloaded);
		 	return false;
		 }

		return true;
	});

	/**
	 * Initialize status bar
	 *
	 * @return void
	 */	
	function init_status() {
		$(".downloaded-theme-status").css("display","block");
		$(".downloaded-theme-status").find("span").css("display","block");
		$(".theme-status-lebel").html('0%');
		$(".downloaded-theme-status").find("span").css('width','0%');
		$(".downloaded-theme-status").find("span").css('background', '#F5821F');
	}

	/**
	 * update status bar
	 *
	 * @param string status
	 * @return void
	 */
	function update_status(status) {
		var status = status;
		$(".theme-status-lebel").html(Math.ceil(status) + '%');
		$(".downloaded-theme-status").css("height",27+'px');
		$(".downloaded-theme-status").find("span").css('width',status+'%');
		$(".downloaded-theme-status").find("span").css('background', '#F5821F');
	}

	/**
	 * Change handler on some form control which raised validation
	 *
	 * @return void
	 */
	$('.ats-form').change(function(event) {
		set_notice_message_hide("#msg_area");
		$("#ats_find_my_themes").attr('disabled',false);
	});

	/**
	 * Create and render notice admin side
	 *
	 * @string string selecoter, e.g. #msg_area
	 * @msg string msg
	 * @return void
	 */
	function set_notice_message_generate(selector, msg){
		$(""+selector+"").addClass('klick-notice-message notice notice-error is-dismissible');
		$(""+selector+"").html("<p>" + msg +  "</p>");
		$(""+selector+"").slideDown();
		//$("#ats_find_my_themes").attr('disabled','disabled');
	}

	/**
	 * Hide admin notice
	 *
	 * @return void
	 */
	function set_notice_message_hide(selector){
		$(""+selector+"").slideUp();
	}

	/**
	 * Test expression if any non numeric is entered
	 *
	 * @return boolean
	 */
	 function check_for_alpha( str ) {
	 	return !/^[0-9]+$/.test(str);
	}

	/**
	 * Checking for empty form control specially texbox
	 *
	 * @return boolean(true)
	 */
	 function check_for_empty(str){
	 	if(str.length < 1) {
	 		return true;
	 	}
	 	return false;
	}

	/**
	 * Checking for if entered number is greater then 100
	 *
	 * @return boolean(true)
	 */
	function check_number_gt_100(str){
		if(str > 100){
			return true;
		}
		return false;
	}

	/**
	 * Send request to install particular theme 
	 *
	 * @return boolean
	 */
	$('.klick-ats-install').click(function(e){
		e.preventDefault();
		var data_id = $(this).attr("data-id");
		$(this).text("Installing...");

		var data = 'install_this_theme='+$(this).attr("data-id");

		klick_ats_send_command('klick_ats_install_theme', data, function (resp) {
			if(resp.status == true){
				set_notice_message_generate('#msg_area',klick_ats_admin.notice_for_success_install);
				$('[data-id="'+data_id+'"]').text("Installed");
				$('[data-id="'+data_id+'"]').attr("disabled","disabled");
				return false;
			} else {
				console.log(klick_ats_admin.notice_for_fail_install);
				set_notice_message_generate('#msg_area',klick_ats_admin.notice_for_fail_install);
				return false;
			}
		});
	});
}
