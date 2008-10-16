<?php
/*
Plugin Name: Idealien Category Enhancements
Plugin URI: http://www.idealienstudios.com/code/ice/
Description: ICE exposes the ability to use named templates for posts and categories maintained through the <strong>manage > categories</strong> admin panel, similar to the default behaviour for page templates. Plugin options are accessible under <strong>settings > category enhancements</strong>.
Version: 1.0
Author: Idealien Studios
Author URI: http://www.idealienstudios.com/
*/

//Global Debug var
//Debug info will be echoed to screen when set to true
$DEBUG = false;

//Retrieve plugin settings from options table
$ice_settings = get_option('ice_settings');
	
//Define hooks to activate / de-activate the plugin
register_activation_hook(__FILE__,"set_ice_options");
register_deactivation_hook(__FILE__,"unset_ice_options");

//Use admin_menu hook to create ICE menu (under settings tab)
add_action('admin_menu', 'ice_add_pages');

//Modify the add / edit category form
add_action('edit_category_form_pre', 'ice_edit_category_form_ob_start');
add_action('edit_category_form', 'ice_edit_category_form_ob_end_flush');
add_action('add_category_form_pre', 'ice_edit_category_form_ob_start');
add_action('add_category_form', 'ice_edit_category_form_ob_end_flush');

//Parse results from form to add / edit / delete data from custom table
add_filter ('edit_category', 'ice_edit_category');
add_filter ('create_category', 'ice_add_category');
add_filter ('delete_category', 'ice_delete_category');

//Modify category template presentation logic when Enhance Category is turned on.
if ( $ice_settings['ice_cat_enhance'] == 'Y' ) { add_filter('category_template', 'ice_category_template'); }

//Modify post template presentation logic when Enhance Post is turned on.
if ( $ice_settings['ice_post_enhance'] == 'Y' ) { add_filter('single_template', 'ice_post_template'); }

//Debug - Only use this to validate 
//add_action('wp_footer', 'debugPrefixOutput')

function set_ice_options () { 
//Generate ICE options as an array within a single record in options table
	$data = array(
		'ice_cat_enhance'		=> 'Y',
		'ice_cat_prefix'		=> 'categoryTemplate',
		'ice_cat_inherit'		=> 'Y',
		'ice_post_enhance'		=> 'Y',
		'ice_post_prefix'		=> 'singleTemplate',
		'ice_version'			=> '1.0'
	);

	add_option('ice_settings',$data);
	
	//Create custom table for category / template relationships
	global $wpdb;
	global $ice_db_version;
	global $DEBUG;

	$table_name = $wpdb->prefix . "catTemplate_relationships";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
		$sql = "CREATE TABLE " . $table_name . " (
	  		id mediumint(9) NOT NULL AUTO_INCREMENT,
			theme tinytext NOT NULL,
			themeVersion tinytext NOT NULL,
			category bigint(9) NOT NULL,
		  	template tinytext NOT NULL,
		  	UNIQUE KEY id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

function unset_ice_options () {
	//De-activate the two options which impact category / post template presentation logic
	global $ice_settings;
	global $DEBUG;
	
	$ice_settings['ice_cat_enhance'] = "N";
	$ice_settings['ice_post_enhance'] = "N";
	update_option('ice_settings',$ice_settings);

	//If you are extending this plugin, in development mode it may be better to use the table delete option when de-activating
	//delete_option("ice_settings");
}

function ice_add_pages() {
	//Create plugin options page under settings admin panel
	global $DEBUG;
	
    if (function_exists('add_options_page')) {
		// Add a new submenu under Options:
		add_options_page('Category Enhancements', 'Category Enhancements', 8, 'categoryenhancements', 'ice_options_page');
	}
}

function ice_options_page() {
	global $DEBUG;
	
  echo "<div class='wrap'>";
	echo "<h2>" . __( 'Category Enhancements', 'ice_trans_domain' ) . "</h2>";
	echo "<p></p>";	

	//Validate user entries if form has been submitted
	if ($_POST['submit']) {
		  update_iceOptions();
    }
	
	//Display options form
	print_iceOptions_form();
	echo "</div>";
}

function update_iceOptions() {
	//Parse through each user-modifiable option and update options table with new details
	$updated = false;
	global $ice_settings;
	global $DEBUG;
	
	if (isset($_POST['ice_cat_enhance'])) { 
		$ice_settings['ice_cat_enhance'] = htmlentities($_POST['ice_cat_enhance']);
		update_option('ice_settings',$ice_settings);
		$updated = true;
	}
	
	if (isset($_POST['ice_cat_prefix'])) { 
		$ice_settings['ice_cat_prefix'] =  htmlentities($_POST['ice_cat_prefix']);
		update_option('ice_settings',$ice_settings);
		$updated = true;
	}
	
	if (isset($_POST['ice_cat_inherit'])) { 
		$ice_settings['ice_cat_inherit'] =  htmlentities($_POST['ice_cat_inherit']);
		update_option('ice_settings',$ice_settings);
		$updated = true;
	}
	
	if (isset($_POST['ice_post_enhance'])) { 
		$ice_settings['ice_post_enhance'] =  htmlentities($_POST['ice_post_enhance']);
		update_option('ice_settings',$ice_settings);
		$updated = true;
	}
	
	if (isset($_POST['ice_post_prefix'])) { 
		$ice_settings['ice_post_prefix'] =  htmlentities($_POST['ice_post_prefix']);
		update_option('ice_settings',$ice_settings);
		$updated = true;
	}
	
	//Output success / fail notification to the user
	if ($updated) {
		echo '<div id="message" class="updated fade">';
        echo '<p>Options Updated</p>';
        echo '</div>';
    } else {
        echo '<div id="message" class="error fade">';
        echo '<p>Unable to update options</p>';
        echo '</div>';
   }
}

function print_iceOptions_form() {
	global $DEBUG;
	
	//retrieve options table variables from database
	$ice_settings = get_option('ice_settings');
	
	$form_ice_cat_enhance = html_entity_decode($ice_settings['ice_cat_enhance']);
	$form_ice_cat_prefix = html_entity_decode($ice_settings['ice_cat_prefix']);
	$form_ice_cat_inherit = html_entity_decode($ice_settings['ice_cat_inherit']);
	$form_ice_post_enhance = html_entity_decode($ice_settings['ice_post_enhance']);
	$form_ice_post_prefix = html_entity_decode($ice_settings['ice_post_prefix']);
	
	//Present data to user in a form for modification
	?>
	<style type="text/css">
		.iceRight { 
			width:160px;
			float:right;
			padding: 7px;
		}
		.iceRight h2 {
			background-color: #247fab; 
			border: 1px solid #f4f4f4; 
			text-align:center;
			padding: 4px;
			color: #FFF;
			font-size: 16px;
			font-weight: bold;
		}
		.iceRight ul {
			padding-left: 15px;
		}
	</style>
	
	
	<?php _e("Configure the options below and adjust category / template associations through the manage > categories admin panel.", 'ice_trans_domain' ); ?>
	<div class="iceRight">
		<h2><?=__('Plugin Support', 'ice_trans_domain')?></h2>
		<ul>
			<li><a href="http://www.idealienstudios.com/code/ICE/"><?=__('Plugin Support Page', 'ice_trans_domain')?></a></li>
			<li><a href="http://www.idealienstudios.com"><?=__('Idealien Studios', 'ice_trans_domain')?></a></li>
			<li><a href="http://www.phpexperts.ca"><?=__('PHP Experts', 'ice_trans_domain')?></a></li>
			<li><a href="http://wordpress.org/support/"><?=__('WordPress Support', 'ice_trans_domain')?></a></li>
		</ul>			
		<h2><?=__('Donations', 'ice_trans_domain')?></h2>
		<div style="text-align:center;">
			<?=__('Are appreciated!', 'ice_trans_domain')?><br />
			<form name="_xclick" action="https://www.paypal.com/row/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="jamie.oastler@gmail.com">
			<input type="hidden" name="item_name" value="Wordpress Plugin Development Donation">
			<input type="hidden" name="currency_code" value="CAD">
			<input type="image" src="http://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Paypal - It's fast, free and secure!">
			</form>
			<?=__('via paypal', 'ice_trans_domain')?>
		</div>
	</div>
	<form method="post">
		<table class="form-table" style="width:800px;">
			<tr class="form-field form-required">
				<th valign="top" scope="row" style="width:220px;"><label for="ice_cat_enhance"><?php _e("Enhanced Categories?", 'ice_trans_domain' ); ?></label></th>
				<td>
					<input name="ice_cat_enhance" type="radio" value="Y" <?php if ($form_ice_cat_enhance == "Y") { echo "checked";} ?>><?=__('Enhanced', 'ice_trans_domain')?></input>
					<input name="ice_cat_enhance" type="radio" value="N" <?php if ($form_ice_cat_enhance == "N") { echo "checked";} ?>><?=__('Default', 'ice_trans_domain')?></input>
					<br/><?php _e("This will determine whether category presentation uses the enhanced plugin logic or default Wordpress category logic. When you de-activate the plugin, this will be reset to default as an extra precaution.", 'ice_trans_domain' ); ?>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th valign="top" scope="row"><label for="ice_cat_prefix"><?php _e("Category Template Prefix:", 'ice_trans_domain' ); ?></label></th>
				<td>
					<input type="text" name="ice_cat_prefix" size="20" value="<?php echo $form_ice_cat_prefix; ?>" />
					<br/><?php _e("This represents the naming convention for the category templates inside your theme directory. The default Wordpress value would be 'category-'.", 'ice_trans_domain' ); ?>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th valign="top" scope="row"><label for="ice_post_enhance"><?php _e("Enhance Posts?", 'ice_trans_domain' ); ?></label></th>
				<td>
					<input name="ice_post_enhance" type="radio" value="Y" <?php if ($form_ice_post_enhance == "Y") { echo "checked";} ?>><?=__('Enhanced', 'ice_trans_domain' ); ?></input>
					<input name="ice_post_enhance" type="radio" value="N" <?php if ($form_ice_post_enhance == "N") { echo "checked";} ?>><?=__('Default', 'ice_trans_domain' ); ?></input>
					<br/><?php _e("This will determine whether post presentation uses the enhanced plugin logic or default Wordpress post logic. When you de-activate the plugin, this will be reset to default as an extra precaution.", 'ice_trans_domain' ); ?>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th valign="top" scope="row"><label for="ice_cat_enhance"><?php _e("Post Template Prefix:", 'ice_trans_domain' ); ?></label></th>
				<td>
					<input type="text" name="ice_post_prefix" size="20" value="<?php echo $form_ice_post_prefix; ?>">
					<br/><?php _e("This represents the naming convention for the post templates inside your theme directory. The default Wordpress value would be 'single'.", 'ice_trans_domain' ); ?>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th valign="top" scope="row"><label for="ice_cat_inherit"><?php _e("Sub-Category Interitance?", 'ice_trans_domain' ); ?></label></th>
				<td>
					<input name="ice_cat_inherit" type="radio" value="Y" <?php if ($form_ice_cat_inherit == "Y") { echo "checked";} ?>><?=__('Yes', 'ice_trans_domain' ); ?></input>
					<input name="ice_cat_inherit" type="radio" value="N" <?php if ($form_ice_cat_inherit == "N") { echo "checked";} ?>><?=__('No', 'ice_trans_domain' ); ?></input>
					<br/><?php _e("If set to 'No', only categories with specific template associations will use the modified logic.<br/>If set to 'Yes', sub-categories will use the custom template of a parent category (if they don't have a template association defined to itself) before using the standard Wordpress logic.", 'ice_trans_domain' ); ?>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th valign="top" scope="row"></th>
				<td>
					 <input type="submit" name="submit" value="<?=__('Update Options', 'ice_trans_domain' ); ?>" />
				</td>
			</tr>
		</table>
	</form>
	
	
	<h2>Notes:</h2>
	<?php $current_Theme = get_theme(get_current_theme()); ?>
	<ul>
		<li>All category / post templates and settings are relative to the current theme applied to Wordpress (<?php echo $current_Theme['Name'] . " ver. " . $current_Theme['Version']; ?>)</li>
		<li>If you select a different theme, the entries will remain in the database in case you want to revert back.</li>
		<li>If you delete a category, all records (regardless of theme / version) will be deleted</li>
		<li>The Sub-Category Inheritance option applies for both category templates and post templates.</li> 
		<li>Similar to a theme styles.css file, for best usability your category template files should have the following comments at the top:<br/>
		<pre>&lt;?php /*
Category Template: Custom Template Name
Template URI: URL path where bloggers can find more information about your template(s)
Description: A short description goes here.
*/ ?></pre></li>
	</ul>
	<?php
}

function ice_edit_category_form_ob_start($category)  {
	//Initiate ob to trap form elements with the edit_category_form_pre hook. 
	//The row (defined in ice_edit_category_form_draw) will be added before 
	//the submit button and </table> to look like standard WP presentation as a result.
	global $DEBUG;
	
	ob_start('ice_edit_category_form_trap');
}

function ice_edit_category_form_trap ($buffer) {
	//If WP improves the position of the edit_category_form hook call, this may become outdated.
	global $DEBUG;
	
	$new_row = ice_edit_category_form_draw();
	$buffer = preg_replace('/<\/table>/', $new_row.'</table>', $buffer);
	return $buffer;
}

function ice_edit_category_form_ob_end_flush () {
	//Stop the ob trap when the edit_category_form hook is triggered.
	ob_end_flush();
}

function ice_edit_category_form_draw()  {
	//Check current theme for category templates which match filename prefix from options table.
	//Inspect for template information (similar to what styles.css uses for theme info)
	//Compare against database for current category and modify selected option
	global $wpdb, $cat_ID;
	global $DEBUG;
	
	$table_name = $wpdb->prefix . "catTemplate_relationships";
	
	//Create a new form table row 
	$html = "
	<tr class=\"form-field\">
		<th scope=\"row\" valign=\"top\"><label for=\"category_template\">". __( 'Template', 'ice_trans_domain' ) ."</label></th>
		<td>
			<select id=\"category_template\" class=\"postform\" name=\"category_template\">
			<option></option>";
	
	$template_dir = get_theme_root() . "/" . get_option('template');
			
	//If theme directory exists open it for parsing category templates
	if ($handle = @ opendir($template_dir)) {
			
		//Retrieve the category prefix from the options table
		$ice_settings = get_option('ice_settings');
		$categoryPrefix = $ice_settings['ice_cat_prefix'];
				
		//Loop through all the files in the directory
		while (false !== ($file = readdir($handle))) {
				
			//Filter to only work with those files which match the category prefix
			if (preg_match("/$categoryPrefix/", $file, $templateFilename)) {

				//Inspect header of category template file for generic info to display
				$categoryData = get_category_template_data($template_dir . "/" . $file);
				$filename = str_replace(array($categoryPrefix,'.php'),'',$file);
				if ($categoryData['template_name'] == "") {
					$details = $filename;
				} else {
					$details = $categoryData['template_name'] . " - " . $categoryData['template_desc'];
				}
				
				$ct = current_theme_info();
				$current_ThemeName = $ct -> name;
				$current_ThemeVersion = $ct -> version;
						
				if ($wpdb->get_var ("SELECT template FROM {$table_name} WHERE theme = '{$current_ThemeName}' AND themeVersion = '{$current_ThemeVersion}' AND category = {$cat_ID} AND template = '{$filename}'")) {
					$html .= "<option selected value=" . $filename . ">" . $details . "</option>";
				} else {
					$html .= "<option value=" . $filename . ">" . $details . "</option>";
				}
			}
		}
	    closedir($handle);
				
	} else { 
		//This scenario "should" never happen - when would you not have a theme enabled?
		$html .= "<option>Unable to locate " . $template_dir . "</option>";
	}
	$html .= "</select><br/>";
	$html .= __('Select the template (from the theme) you would like this category to use.');
	$html .= "</td>";
	$html .= "</tr>";

	return $html;	
}

function get_category_template_data( $template_file ) {
	//Used in ice_edit_category_form_draw to extract template details
	global $DEBUG;
	
	$template_allowed_tags = array(
		'a' => array(
			'href' => array(),'title' => array()
			),
		'abbr' => array(
			'title' => array()
			),
		'acronym' => array(
			'title' => array()
			),
		'code' => array(),
		'em' => array(),
		'strong' => array()
	);
	
	$template_data = implode( '', file( $template_file ) );
	$template_data = str_replace ( '\r', '\n', $template_data );
	
	preg_match( '|Category Template:(.*)$|mi', $template_data, $template_name );
	preg_match( '|Template URI:(.*)$|mi', $template_data, $template_uri );
	preg_match( '|Description:(.*)$|mi', $template_data, $template_desc );
	
	$template_name = wp_kses( trim( $template_name[1] ), $template_allowed_tags );
	$template_uri = clean_url( trim( $template_uri[1] ) );
	$template_desc = wp_kses( trim( $template_desc[1] ), $template_allowed_tags );
	
	return array( 'template_name' => $template_name, 'template_uri' => $template_uri, 'template_desc' => $template_desc );						
}

function ice_add_category ($cat_ID) {
	//Write new record into plugin table for current theme / version, category and selected template.
	global $wpdb;
	global $DEBUG;

	$cat_template = $wpdb->escape($_REQUEST['category_template']);
	if ($cat_template != "") {
		$table_name = $wpdb->prefix . "catTemplate_relationships";
		$ct = current_theme_info();
		$current_ThemeName = $ct -> name;
		$current_ThemeVersion = $ct -> version;

		$querystr = "INSERT INTO {$table_name} (theme, themeVersion, category, template) VALUES ('{$current_ThemeName}', '{$current_ThemeVersion}', {$cat_ID}, '{$cat_template}')";
		$results = $wpdb->query( $querystr );
	}
}

function ice_edit_category ($arg) {
	//Create or update record in plugin table for current theme / version, category and selected template.
	global $wpdb;
	global $DEBUG;
	
	$table_name = $wpdb->prefix . "catTemplate_relationships";
	
	$cat_template = $wpdb->escape($_REQUEST['category_template']);
	$cat_ID = $wpdb->escape($_REQUEST['cat_ID']);
	
	$ct = current_theme_info();
	$current_ThemeName = $ct -> name;
	$current_ThemeVersion = $ct -> version;

	if ($wpdb->get_var ("SELECT category FROM {$table_name} WHERE category = {$cat_ID} AND theme = '{$current_ThemeName}' AND themeVersion = '{$current_ThemeVersion}'")) {
		//echo "DO AN UPDATE";
		$querystr = "UPDATE {$table_name} SET template = '{$cat_template}' WHERE category = {$cat_ID} AND theme = '{$current_ThemeName}' AND themeVersion = '{$current_ThemeVersion}'";
	} else {
		//echo "DO AN INSERT";
		$querystr = "INSERT INTO {$table_name} (theme, themeVersion, category, template) VALUES ('{$current_ThemeName}', '{$current_ThemeVersion}', {$cat_ID}, '{$cat_template}')";
	}
	//echo $querystr;
	$results = $wpdb->query( $querystr );
}

function ice_delete_category($category) {
	//Called from the delete_category hook - doesn't really need to be explained I hope.
	//Function is called iteratively with each category that is selected for deletion (if more than 1)
	global $wpdb;
	global $DEBUG;
	
	$table_name = $wpdb->prefix . "catTemplate_relationships";
	
	$querystr = "DELETE FROM $table_name WHERE category = {$category}";
	$results = $wpdb->query( $querystr );
}

function ice_category_template($template, $category_id = '') {
	//Now that the nasty admin stuff is out of the way, actual functionality can occur
	//This is executed with the category_template filter to replace default category presentation logic
	global $wpdb, $ice_db_version, $ice_settings;
	global $DEBUG;
	
	// Pull the category id from the variable passed to the function 
	// or from the url request if we haven't received anything in the passed variale
	$category_id = ($category_id) ? $category_id : get_query_var('cat');
	
	// Query the category object for the current category
	$category = get_category( $category_id );
	
	// This echo is just for debug to show the category object
	if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>Category:<strong><pre>".print_r($category,true)."</pre></strong></div><br/><br/>";
	
		$current_Theme = get_theme(get_current_theme());
		$current_ThemeName = $current_Theme['Name'];
		$current_ThemeVersion = $current_Theme['Version'];
				
		$table_name = $wpdb->prefix . "catTemplate_relationships";
		$sql = "SELECT  * FROM `$table_name` WHERE category = $category_id AND theme = '$current_ThemeName' AND themeVersion = '$current_ThemeVersion'";
		$result = $wpdb->get_results( $sql );
		
		// This echo is just for debug to show the query
		if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>Querying: <strong>$sql</strong><br/>Result:<strong><pre>".print_r($result,true)."</pre></strong></div><br/><br/>";
			
		$custom_template = TEMPLATEPATH . '/' . $ice_settings['ice_cat_prefix'] . $result[0] -> template . ".php";

		// This echo is just for debug to show what we were looking for
		if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>Checking for existence of: <strong>$custom_template</strong></div><br/><br/>";
			
		// If the custom template has been found in the current template directory
		if ( file_exists( $custom_template ) ) {
			
			// This echo is just for debug to show we're pulling the category id
			if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>This is the custom template for category id: <strong>$category_id</strong><br/><br/>It's located at <strong>$custom_template</strong></div><br/><br/>";
			
			// Return the location of the custom template file.  This needs to be the 
			// absolute location relative to the root filesystem on the host server.
			return $custom_template;
		} 
		else {
			
			// If a custom template hasn't been found
			// and sub categories inherit from their parents
			// check the category's parents
			if ( $ice_settings['ice_cat_inherit'] == 'Y' ) {
				
				// If not already at the top level category ($category -> category_parent != 0)
				if ( $category -> category_parent != 0 ) {
					
					// We assign the end result of the recursive calls to the $template variable
					// and pass that same variable through the function by reference
					$template = ice_category_template ( $template, $category -> category_parent );
				}
			}
			
			// This echo is just for debug to show we're pulling the category id
			if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>This is the custom template for category id: <strong>$category_id</strong><br/><br/>It's located at <strong>$template</strong></div><br/><br/>";
			
			// Return the default template this function was orignially passed
			return $template;
		}

}

function ice_post_template($template, $category_id = '') {
	//Same general logic approach as ice_category_template, except for post template loop
	//Called by single_template hook
	
	global $wpdb, $ice_db_version, $ice_settings;
	global $DEBUG;
	
	// Pull the category id from the variable passed to the function 
	// or from the get_the_category() function if we haven't received anything in the passed variale
	// this was using get_query_var('cat'); at one point but it stopped working.
	$CurrentCategory = get_the_category();
	$CurrentCategory = $CurrentCategory[0]->cat_ID;	
	$category_id = ($category_id) ? $category_id : $CurrentCategory;

	// Query the category object for the current category in question
	$category = get_category( $category_id );
	
	// This echo is just for debug to show the category object
	if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>Category:<strong><pre>".print_r($category,true)."</pre></strong></div><br/><br/>";
			
		$current_Theme = get_theme(get_current_theme());
		$current_ThemeName = $current_Theme['Name'];
		$current_ThemeVersion = $current_Theme['Version'];
		
		$table_name = $wpdb->prefix . "catTemplate_relationships";
		$sql = "SELECT  * FROM `$table_name` WHERE category = $category_id AND theme = '$current_ThemeName' AND themeVersion = '$current_ThemeVersion'";
		$result = $wpdb->get_results( $sql );
		
		// This echo is just for debug to show the query
		if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>Querying: <strong>$sql</strong><br/>Result:<strong><pre>".print_r($result,true)."</pre></strong></div><br/><br/>";
			
		$custom_template = TEMPLATEPATH . '/' . $ice_settings['ice_post_prefix'] . $result[0] -> template . ".php";

		// This echo is just for debug to show what we were looking for
		if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>Checking for existence of: <strong>$custom_template</strong></div><br/><br/>";
			
		// If the custom template has been found in the current template directory
		if ( file_exists( $custom_template ) ) {
			
			// This echo is just for debug to show we're pulling the category id
			if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>This is the custom template for category id: <strong>$category_id</strong><br/><br/>It's located at <strong>$custom_template</strong></div><br/><br/>";
			
			// Return the location of the custom template file.  This needs to be the 
			// absolute location relative to the root filesystem on the host server.
			return $custom_template;
		} 
		else {
			
			// If a custom template hasn't been found
			// and sub categories inherit from their parents
			// check the category's parents
			if ( $ice_settings['ice_cat_inherit'] == 'Y' ) {
				
				// If not already at the top level category ($category -> category_parent != 0)
				if ( $category -> category_parent != 0 ) {
					
					// We assign the end result of the recursive calls to the $template variable
					// and pass that same variable through the function by reference
					$template = ice_post_template ( $template, $category -> category_parent );
				}
			}
			
			// This echo is just for debug to show we're pulling the category id
			if ($DEBUG) echo "<div style='border: 1px solid #666; padding: 15px; background: #CCF;'>This is the custom template for category id: <strong>$category_id</strong><br/><br/>It's located at <strong>$template</strong></div><br/><br/>";
			
			// Return the default template this function was orignially passed
			return $template;
		}
}

//A test function which should be deleted - hard output of wp_options db entries
function debugPrefixOutput () {
	global $DEBUG;
	
  $ice_settings = get_option('ice_settings');
	
  echo "<table border='1' cellpadding='5' cellspacing='5'>";
	echo "<tr><td><b>Variable</b></td><td><b>Value</b></td></tr>";
	echo "<tr><td><b>ice_cat_enhance</b></td><td>" . $ice_settings['ice_cat_enhance'] . "</td></tr>";
	echo "<tr><td><b>ice_cat_prefix</b></td><td>" . $ice_settings['ice_cat_prefix'] . "</td></tr>";
	echo "<tr><td><b>ice_cat_inherit</b></td><td>" . $ice_settings['ice_cat_inherit'] . "</td></tr>";
	echo "<tr><td><b>ice_post_enhance</b></td><td>" . $ice_settings['ice_post_enhance'] . "</td></tr>";
	echo "<tr><td><b>ice_post_prefix</b></td><td>" . $ice_settings['ice_post_prefix'] . "</td></tr>";
	echo "</table>";
}

function ice_debug_echo ( $val, $name='', $line='' ) {
	if ($line) echo "<strong>Line $line,</strong>";
	echo $name;
	
	if ( is_array( $val ) ) {
		echo "<pre>".print_r($val,true)."</pre>";
	}
	else {
		echo "$val";
	}
}

function ice_debug_div ( $val, $name='', $line='' ) {
	if ($line) echo "<strong>Line $line,</strong>";
	echo $name;
	
	if ( is_array( $val ) ) {
		echo "<pre>".print_r($val,true)."</pre>";
	}
	else {
		echo "$val";
	}
}