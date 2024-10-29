<div id="klick_ats_tab_second">
	<div class="klick-save-notice-message"></div>
	<div class="klick-ats-data-listing-wrap">
		 <div class="klick-ats-form-wrapper"> <!-- Form wrapper starts -->
			<form>
	            <table class="form-table">
	                <tbody>
	                    <p id="klick_ats_blank_error" class="klick-ats-error"></p>
	                    <tr>
	                        <th>
	                            <label for="ats_advance_search_toggle"><?php _e('Enable/Disable','klick-ats'); ?> : </label>
	                        </th>
	                        <td>
	                        	<?php $urltoggle = $options -> get_option('advanced-search-toggle'); ?>
	                            <?php _e('Enable','klick-ats'); ?> : <input type="radio" name="ats_advance_search_toggle" value="<?php _e('ON','klick-ats'); ?>" class="klick-ats-advance-search-toggle" <?php echo (!empty($urltoggle) ? 'checked = "checked"' : '' ); ?>> 
	                            <?php _e('Disable','klick-ats'); ?> : <input type="radio" name="ats_advance_search_toggle" value="<?php _e('OFF','klick-ats'); ?>" class="klick-ats-advance-search-toggle" <?php echo (empty($urltoggle) ? 'checked = "checked"' : '' ); ?>>
	                            <span class="klick-ats-error-text"></span>
	                        </td>
	                    </tr>
	                </tbody>
	            </table>
	        </form>
	        <p class="submit">
	            <button id="klick_ats_advanced_Save" name="klick_ats_advanced_Save" class="klick_btn button button-primary"><?php _e('Save','klick-ats'); ?></button>
	        </p>
	       </div> <!-- Form wrapper starts -->
	</div>
	</div>	
</div>