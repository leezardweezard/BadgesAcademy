<?php
/*
 * Plugin name: Badges plugin
 * Plugin URI: http://www.badges4languages.org
 * Description: Plugin for school teacher to add a student to a database and award a badge
 * Version 1.0
 * Author: Badges4languages
 * Author URI: http://www.badges4languages.org
 * License: GPL2
 */

//activation hook for plugin to create custom tables
register_activation_hook(__FILE__, 'bsp_create_update_table');

//deactivation hook for plugin
register_deactivation_hook(__FILE__,'bsp_deactivate');

//function for deactivating the plugin
function bsp_deactivate() {
    error_log('plugin deactivated');
}
add_action('wp_enqueue_scripts', 'bsp_issuer_api');
//adding the action so the ajaxurl is defined on frontend and we can use it
add_action('wp_head','pluginname_ajaxurl');
function pluginname_ajaxurl() {
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}

//adding action for ajax and jquery to be only on our page
if(isset($_GET['page']) && $_GET['page']=='add-new-students'){
	
add_action('admin-print-styles', 'bsp_admin_styles');
add_action('admin_print_scripts', 'bsp_admin_scripts');
}

add_action( 'wp_ajax_bsp_award_ajax', 'bsp_award_ajax_handle' );
add_action( 'wp_ajax_nopriv_bsp_award_ajax', 'bsp_award_ajax_handle' );



function bsp_issuer_api(){
	//wp_enqueue_script( 'openbadges', 'https://backpack.openbadges.org/issuer.js', array()); //for issuer API //not working if included like that, don't know why
	/*wp_enqueue_script( 'bsp-awards', plugins_url( 'js/award_badge.js', __FILE__ ), array( 'jquery' ) );*/
	wp_localize_script( 'bsp-awards', 'BSP_Awards', array(
                'ajaxurl'       => admin_url( 'admin-ajax.php' ),
            ) );
	wp_enqueue_script('custom', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery' ));
}

//function for ajax request
function bsp_award_ajax_handle(){
	
	wp_die();
}

//enqueueing scripts
function bsp_admin_scripts(){
	wp_enqueue_script('media-upload'); //for wp media upload
	wp_enqueue_script('thickbox'); //for wp media upload
	wp_enqueue_script('jquery-ui-dialog');  // For admin panel popup alerts
	
	wp_register_script( 'wp_csv_to_db', plugins_url( 'js/admin_page.js', __FILE__ ), array('jquery','media-upload','thickbox') );  //including external admin_page javascript file
	wp_enqueue_script('wp_csv_to_db');
	wp_localize_script( 'wp_csv_to_db', 'bsp_pass_js_vars', array( 'ajax_image' => plugin_dir_url( __FILE__ ).'images/loading.gif', 'ajaxurl' => admin_url('admin-ajax.php') ) );
	wp_enqueue_media();
}

//function for enqueueing styles
function bsp_admin_styles(){
	wp_enqueue_style('thickbox');
}

//action for registering our custom post type 
add_action('init', 'bsp_badges_register');

//function for custom post type 
function bsp_badges_register() {
 
	$labels = array(
		'name' =>_x('Badge School', 'post type general name'),
		'singular_name' =>_x('Badge', 'post type singular name'),
		'add_new' =>_x('Add New', 'badge item'),
		'add_new_item' =>__('Add New Badge'),
		'edit_item' =>__('Edit Badge'),
		'new_item' =>__('New Badge'),
		'view_item' =>__('View Badge'),
		'search_items' =>__('Search Badge'),
		'not_found' =>__('Nothing found'),
		'not_found_in_trash' =>__('Nothing found in Trash'),
		'parent_item_colon' => ''
	);
 
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'menu_icon' => 'dashicons-welcome-learn-more',
		'rewrite' => true,
		'capability_type' => 'post',
		//capabilities just for admin, because just admin can see the custom post
		'capabilities'=>array(
			'edit_post'=>'update_core',
			'read_post'=>'update_core',
			'delete_post'=>'update_core',
			'edit_posts'=>'update_core',
			'edit_others_posts'=>'update_core',
			'publish_posts'=>'update_core',
			'read_private_posts'=>'update_core'
		),
		
		'hierarchical' => false,
		'menu_position' => 75,
		'supports' => array('title','editor','thumbnail','page-attributes'),
		'has_archive'=>true
	  ); 
	
	//registering the custom post type
	register_post_type( 'badge' , $args );
    flush_rewrite_rules();
        
   
    
    register_taxonomy(
    'levels',
    array('badge'), 
    array(
    'label'=>'Levels',
    'labels'=>array(
		'name'=> _x( 'Levels', 'taxonomy general name' ),
		'singular_name'=>'Level',
		'menu_name'=>__('Levels')
		),
	'show_ui'=>true,
	'hierarchical'=>true,
	'rewrite'=>array('slug'=>'level'),
	'capabilities'=>array(
		'manage_terms'=>'manage_options',
		'edit_terms'=>'manage_options',
		'delete_terms'=>'manage_options',
		'assign_terms'=>'manage_options',
		),
	
    )
    );
      
    register_taxonomy(
    'skills',
    array('badge'), 
    array(
    'hierarchical'=>true,
    'public'=>true,
    'label'=>'Skills',
    'labels'=>array(
		'name'=> _x( 'Skills', 'taxonomy general name' ),
		'singular_name'=>'Skill',
		'menu_name'=>__('Skills')
		),
	'show_ui'=>true,
	'rewrite'=>array('slug'=>'skill'),
	'capabilities'=>array(
		'manage_terms'=>'manage_options',
		'edit_terms'=>'manage_options',
		'delete_terms'=>'manage_options',
		'assign_terms'=>'manage_options',
		),
    )
    );

    register_taxonomy(
    'tags',
    array('badge'), 
    array(
    'label'=>'tag',
    'labels'=>array(
		'name'=> _x( 'Tags', 'taxonomy general name' ),
		'singular_name'=>'Tag',
		'menu_name'=>__('Tags')
		),
	'show_ui'=>true,
	'hierarchical'=>false,
	'rewrite'=>array('slug'=>'tag'),
	'capabilities'=>array(
		'manage_terms'=>'manage_options',
		'edit_terms'=>'manage_options',
		'delete_terms'=>'manage_options',
		'assign_terms'=>'manage_options',
		),
	
    )
    );
}//end of function

//adding action for admin menu
add_action('admin_menu','bsp_plugin_menu');

//function for adding a menu page into admin panel menus
function bsp_plugin_menu(){
	//first paramether is the slug name of a parent (in this case the page of a custom post type), second is the name of the page, name of the menu, clearenece (capability), slug and function
	add_submenu_page('edit.php?post_type=badge', 'Add new student', 'Add new students', 'publish_posts','add-new-students','bsp_add_new_students');
	add_submenu_page('edit.php?post_type=badge', 'Show current students', 'Show current students', 'publish_posts','show-students','bsp_students_show');
	add_submenu_page('edit.php?post_type=badge', 'Choose badges', 'Choose badges', 'publish_posts','choose-badges','bsp_choose_badges');
	add_options_page('Badges settings', 'Settings Badge', 'manage_options', 'badges', 'bsp_plugin_options_page');
}

//function for creating (if it does not exist) or updating (it the table already exists) a table and adding a new page
function bsp_create_update_table(){
	
	global $wpdb; //geting the acces to a wp tables
	$tablename=$wpdb->prefix . "students"; //the name of a table with it's prefix
	$charset_collate=$wpdb->get_charset_collate();
	
	//checking if the table with the name we created a line above exists
	if($wpdb->get_var("SHOW TABLES LIKE '$tablename'") != $tablename){
		
		//if it does not exists we create the table
		$sql = "CREATE TABLE $tablename (
		students_id INT( 11 ) NOT NULL AUTO_INCREMENT,
		students_name VARCHAR ( 50 ) NOT NULL,
		students_lastname VARCHAR ( 50 ) NOT NULL,
		students_email VARCHAR ( 50 ) NOT NULL, 
		students_date DATETIME,
		PRIMARY KEY (students_id)
		)$charset_collate;";
		
		//wordpress function for updating the table
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
	}
	
	//adding a new page
	//checking if the page already exsists if not we create it
	if (get_page_by_title('Accept badge') == NULL) {
		//creating post object
		$bsp_award_page=array(
		'post_name'=>'accept-badge',
		'post_title'=>'Accept badge',
		'post_content'=>'You got a badge!',
		'post_excerpt'=>'badges',
		'post_status'=>'publish',
		'post_type'=>'page',
		'page_template'=>'badges-accept-template.php',
		'comment_status'=>'closed'
		);
	}
	//inserting the page
	$post_id=wp_insert_post($bsp_award_page);
	
	//adding the post meta so we can easily find it and delete it (or do other things)
	add_post_meta($post_id,'bsp_delete_page','delete page', true);
}//end of function

//function for adding the students into the custom table
function bsp_add_new_students(){
	//form for adding a student
	?>
	<!-- using wp classes for css -->
	<div class="wrap">
		<!--header -->
		<h2>Add student</h2><br />
		<!-- start of the form with method post -->
		<form action="edit.php?post_type=badge&page=add-new-students" method="post">
			<!-- wp formation of the page -->
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="firstname">First name </label></th> <!-- label for our first paramether which is first name -->
					<td>
						<input type="text" name="firstname" id="firstname" value="" placeholder="Name of a student" required /> 
						<br/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastname">Last name </label></th> <!-- label for our second paramether which is lastn name or surname -->
					<td>
						<input type="text" name="lastname" id="lastname" value="" placeholder="Surname of a student" required /> 
						<br/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="email">Email </label></th> <!-- label for our thirf paramether which is name of a course -->
					<td>
						<input type="text" name="email" id="email" value="" placeholder="example@example.com" required /> 
						<br/>
					</td>
				</tr>
			</table>
			<?php @submit_button('Add a student'); ?> <!-- adding a submit button to our form using wp css, with a custom value of Add student -->
			
		</form>
	</div>
	
	<?php
	//csv file upload to database (wp-csv-to-database plugin)
	
		// Set variables		
		global $wpdb;
		$error_message = '';
		$success_message = '';
		$message_info_style = '';
		
		// If button is pressed to "Import to DB"
		if (isset($_POST['execute_button'])) {
			
			// If the "Select Table" input field is empty
			if(empty($_POST['table_select'])) {
				$error_message .= '* '.__('No Database Table was selected. Please select a Database Table.','wp_csv_to_db').'<br />';
			}
			// If the "Select Input File" input field is empty
			if(empty($_POST['csv_file'])) {
				$error_message .= '* '.__('No Input File was selected. Please enter an Input File.','wp_csv_to_db').'<br />';
			}
			// Check that "Input File" has proper .csv file extension
			$ext = pathinfo($_POST['csv_file'], PATHINFO_EXTENSION);
			if($ext !== 'csv') {
				$error_message .= '* '.__('The Input File does not contain the .csv file extension. Please choose a valid .csv file.','wp_csv_to_db');
			}
			// If all fields are input; and file is correct .csv format; continue
			if(!empty($_POST['table_select']) && !empty($_POST['csv_file']) && ($ext === 'csv')) {
				
				// If "disable auto_inc" is checked.. we need to skip the first column of the returned array (or the column will be duplicated)
				if(isset($_POST['remove_autoinc_column'])) {
					$db_cols = $wpdb->get_col( "DESC " . $_POST['table_select'], 0 );  
					unset($db_cols[0]);  // Remove first element of array (auto increment column)
				} 
				// Else we just grab all columns
				else {
					$db_cols = $wpdb->get_col( "DESC " . $_POST['table_select'], 0 );  // Array of db column names
				}
				// Get the number of columns from the hidden input field (re-auto-populated via jquery)
				$numColumns = $_POST['num_cols'];
				
				// Open the .csv file and get it's contents
				if(( $fh = @fopen($_POST['csv_file'], 'r')) !== false) {
					
					// Set variables
					$values = array();
					$too_many = '';  // Used to alert users if columns do not match
					
					while(( $row = fgetcsv($fh)) !== false) {  // Get file contents and set up row array
						if(count($row) == $numColumns) {  // If .csv column count matches db column count
							$values[] = '("' . implode('", "', $row) . '")';  // Each new line of .csv file becomes an array
						}
					}
					// If user elects to input a starting row for the .csv file
					if(isset($_POST['sel_start_row']) && (!empty($_POST['sel_start_row']))) {
						
						// Get row number from user
						$num_var = $_POST['sel_start_row'] - 1;  // Subtract one to make counting easy on the non-techie folk!
						
						// If user input number exceeds available .csv rows
						if($num_var > count($values)) {
							$error_message .= '* '.__('Starting Row value exceeds the number of entries being updated to the database from the .csv file.','wp_csv_to_db').'<br />';
							$too_many = 'true';  // set alert variable
						}
						// Else splice array and remove number (rows) user selected
						else {
							$values = array_slice($values, $num_var);
						}
					}
					// If there are no rows in the .csv file AND the user DID NOT input more rows than available from the .csv file
					if( empty( $values ) && ($too_many !== 'true')) {
						$error_message .= '* '.__('Columns do not match.','wp_csv_to_db').'<br />';
						$error_message .= '* '.__('The number of columns in the database for this table does not match the number of columns attempting to be imported from the .csv file.','wp_csv_to_db').'<br />';
						$error_message .= '* '.__('Please verify the number of columns attempting to be imported in the "Select Input File" exactly matches the number of columns displayed in the "Table Preview".','wp_csv_to_db').'<br />';
					}else {
						// If the user DID NOT input more rows than are available from the .csv file
						if($too_many !== 'true') {
							
							$db_query_update = '';
							$db_query_insert = '';
								
							// Format $db_cols to a string
							$db_cols_implode = implode(',', $db_cols);
								
							// Format $values to a string
							$values_implode = implode(',', $values);
							
							
							// If "Update DB Rows" was checked
							if (isset($_POST['update_db'])) {
								
								// Setup sql 'on duplicate update' loop
								$updateOnDuplicate = ' ON DUPLICATE KEY UPDATE ';
								foreach ($db_cols as $db_col) {
									$updateOnDuplicate .= "$db_col=VALUES($db_col),";
								}
								$updateOnDuplicate = rtrim($updateOnDuplicate, ',');
								
								
								$sql = 'INSERT INTO '.$_POST['table_select'] . ' (' . $db_cols_implode . ') ' . 'VALUES ' . $values_implode.$updateOnDuplicate;
								$db_query_update = $wpdb->query($sql);
							}else {
								$sql = 'INSERT INTO '.$_POST['table_select'] . ' (' . $db_cols_implode . ') ' . 'VALUES ' . $values_implode;
								$db_query_insert = $wpdb->query($sql);
							}
							
							// If db db_query_update is successful
							if ($db_query_update) {
								$success_message = __('Congratulations!  The database has been updated successfully.','wp_csv_to_db');
							}
							// If db db_query_insert is successful
							else if ($db_query_insert) {
								$success_message = __('Congratulations!  The database has been updated successfully.','wp_csv_to_db');
								$success_message .= '<br /><strong>'.count($values).'</strong> '.__('record(s) were inserted into the', 'wp_csv_to_db').' <strong>'.$_POST['table_select'].'</strong> '.__('database table.','wp_csv_to_db');
							}
							// If db db_query_insert is successful AND there were no rows to udpate
							else if( ($db_query_update === 0) && ($db_query_insert === '') ) {
								$message_info_style .= '* '.__('There were no rows to update. All .csv values already exist in the database.','wp_csv_to_db').'<br />';
							}
							else {
								$error_message .= '* '.__('There was a problem with the database query.','wp_csv_to_db').'<br />';
								$error_message .= '* '.__('A duplicate entry was found in the database for a .csv file entry.','wp_csv_to_db').'<br />';
								$error_message .= '* '.__('If necessary; please use the option below to "Update Database Rows".','wp_csv_to_db').'<br />';
							}
						}
					}
				}else {
					$error_message .= '* '.__('No valid .csv file was found at the specified url. Please check the "Select Input File" field and ensure it points to a valid .csv file.','wp_csv_to_db').'<br />';
				}
			}
		}
		// If there is a message - info-style
		if(!empty($message_info_style)) {
			echo '<div class="info_message_dismiss">';
			echo $message_info_style;
			echo '<br /><em>('.__('click to dismiss','wp_csv_to_db').')</em>';
			echo '</div>';
		}
		
		// If there is an error message	
		if(!empty($error_message)) {
			echo '<div class="error_message">';
			echo $error_message;
			echo '<br /><em>('.__('click to dismiss','wp_csv_to_db').')</em>';
			echo '</div>';
		}
		
		// If there is a success message
		if(!empty($success_message)) {
			echo '<div class="success_message">';
			echo $success_message;
			echo '<br /><em>('.__('click to dismiss','wp_csv_to_db').')</em>';
			echo '</div>';
		}
	?>
	<!-- form for csv upload -->
	<div class="wrap">
		<h2>CSV file upload</h2>
	<form id="wp_csv_to_db_form" method="post" action="">
                    <table class="form-table"> 
                        <tr valign="top"><th scope="row"><?php _e('Database Table:','wp_csv_to_db'); ?></th>
                            <td>
                                <select id="table_select" name="table_select" value="">
                                <option name="" value=""></option>
                                
                                <?php 
                                global $wpdb;
                                $repop_table=$wpdb->prefix."students";
                                ?>
                                <option name="<?php echo $repop_table ?>" value="<?php echo $repop_table ?>" <?php echo 'selected="selected"'; ?>><?php echo $repop_table ?></option>
                                <?php
                                //if you want to display all the table names in the database use the code in comments /**/
                                // Get all db table names
                               /* $sql = "SHOW TABLES";
                                $results = $wpdb->get_results($sql);
                                $repop_table = isset($_POST['table_select']) ? $_POST['table_select'] : null;
                                foreach($results as $index => $value) {
                                    foreach($value as $tableName) {
                                        ?><option name="<?php echo $tableName ?>" value="<?php echo $tableName ?>" <?php if($repop_table === $tableName) { echo 'selected="selected"'; } ?>><?php echo $tableName ?></option><?php
                                    }
                                }*/
                                ?>
                            </select>
                            </td> 
                        </tr>
                         <tr valign="top"><th scope="row"><?php _e('CSV file for upload:','wp_csv_to_db'); ?></th>
                            <td>
                                <?php $repop_file = isset($_POST['csv_file']) ? $_POST['csv_file'] : null; ?>
                                <?php $repop_csv_cols = isset($_POST['num_cols_csv_file']) ? $_POST['num_cols_csv_file'] : '0'; ?>
                                <input id="csv_file" name="csv_file" type="text" size="70" value="<?php echo $repop_file; ?>" />
                                <input id="csv_file_button" type="button" value="Upload" />
                                <input id="num_cols" name="num_cols" type="hidden" value="" />
                                <input id="num_cols_csv_file" name="num_cols_csv_file" type="hidden" value="" />
                                <br><?php _e('File must end with a .csv extension.','wp_csv_to_db'); ?>
                                <br><?php _e('Number of .csv file Columns:','wp_csv_to_db'); echo ' '; ?><span id="return_csv_col_count"><?php echo $repop_csv_cols; ?></span>
                            </td>
                        </tr>
                       <!-- <tr valign="top"><th scope="row"><?php// _e('Select Starting Row:','wp_csv_to_db'); ?></th>
                            <td>
                            	<?php// $repop_row = isset($_POST['sel_start_row']) ? $_POST['sel_start_row'] : null; ?>
                                <input id="sel_start_row" name="sel_start_row" type="text" size="10" value="<?php// echo $repop_row; ?>" />
                                <br><?php// _e('Defaults to row 1 (top row) of .csv file.','wp_csv_to_db'); ?>
                            </td>
                        </tr>-->
                        <!--<tr valign="top"><th scope="row"><?php _e('Disable "auto_increment" Column:','wp_csv_to_db'); ?></th>-->
                            <td>
                                <input id="remove_autoinc_column" name="remove_autoinc_column" type="hidden"/>
                               <!-- <br><?php _e('Bypasses the "auto_increment" column;','wp_csv_to_db'); ?>
                                <br><?php _e('This will reduce (for the purposes of importation) the number of DB columns by "1".'); ?>-->
                            </td>
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('Update Database Rows:'); ?></th>
                            <td>
                                <input id="update_db" name="update_db" type="checkbox" />
                                <br><?php _e('Will update exisiting database rows when a duplicated primary key is encountered.'); ?>
                                <br><?php _e('Defaults to all rows inserted as new rows.'); ?>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input id="execute_button" name="execute_button" type="submit" class="button-primary" value="<?php _e('Import to DB', 'wp_csv_to_db') ?>" />
                    </p>
                   </form>
                   </div>
        
         <h3><?php _e('Table Preview:','wp_csv_to_db'); ?><input id="repop_table_ajax" name="repop_table_ajax" value="<?php _e('Reload Table Preview','wp_csv_to_db'); ?>" type="button" style="margin-left:20px;" /></h3>
            
        <div id="table_preview">
        </div>
        
        <p><?php _e('Click on the "Reload table preview" to see the fields.','wp_csv_to_db'); ?>
        <br><?php _e('Use the outputed fields as reference when verifying the .csv file is formatted properly.','wp_csv_to_db'); ?>
        
        <!-- Alert invalid .csv file - jquery dialog -->
        <div id="dialog_csv_file" title="<?php _e('Invalid File Extension','wp_csv_to_db'); ?>" style="display:none;">
        	<p><?php _e('This is not a valid .csv file extension.','wp_csv_to_db'); ?></p>
        </div>
        
        <!-- Alert select db table - jquery dialog --
        <div id="dialog_select_db" title="<?php _e('Database Table not Selected','wp_csv_to_db'); ?>" style="display:none;">
        	<p><?php _e('First, please select a database table from the dropdown list.','wp_csv_to_db'); ?></p>
        </div>-->
	<?php
}//end of function

//  Ajax call for showing table column names
add_action( 'wp_ajax_bsp_get_columns', 'bsp_get_columns_callback' );

function bsp_get_columns_callback() {
	
	// Set variables
	global $wpdb;
	$sel_val = isset($_POST['sel_val']) ? $_POST['sel_val'] : null;
	$disable_autoinc = isset($_POST['disable_autoinc']) ? $_POST['disable_autoinc'] : 'false';
	$enable_auto_inc_option = 'false';
	$content = '';
	
	// Ran when the table name is changed from the dropdown
	if ($sel_val) {
		// Get table name
		$table_name = $sel_val;
		// Setup sql query to get all column names based on table name
		$sql = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'.$wpdb->dbname.'" AND TABLE_NAME ="'.$table_name.'" AND EXTRA like "%auto_increment%"';
		// Execute Query
		$run_qry = $wpdb->get_results($sql);
		// Begin response content
		$content .= '<table id="ajax_table"><tr>';
		// If the db query contains an auto_increment column
		if((isset($run_qry[0]->EXTRA)) && (isset($run_qry[0]->COLUMN_NAME))) {
			// If user DID NOT check 'disable_autoinc'; we need to add that column back with unique formatting 
			if($disable_autoinc === 'false') {
				$content .= '<td class="auto_inc"><strong>'.$run_qry[0]->COLUMN_NAME.'</strong></td>';
			}
			// Get all column names from database for selected table
			$column_names = $wpdb->get_col( 'DESC ' . $table_name, 0 );
			$counter = 0;
			
			// IMPORTANT - If the db results contain an auto_increment; we remove the first column below; because we already added it above.
			foreach ( $column_names as $column_name ) {
				if( $counter++ < 1) continue;  // Skip first iteration since 'auto_increment' table data cell will be duplicated
			    $content .= '<td><strong>'.$column_name.'</strong></td>';
			}
		}
		// Else get all column names from database (unfiltered)
		else {
			$column_names = $wpdb->get_col( 'DESC ' . $table_name, 0 );
			foreach ( $column_names as $column_name ) {
			  $content .= '<td><strong>'.$column_name.'</strong></td>';
			}
		}
		$content .= '</tr></table><br />';
		$content .= __('Number of Database Columns:','wp_csv_to_db').' <span id="column_count"><strong>'.count($column_names).'</strong></span><br />';
		
		// If there is an auto_increment column in the returned results
		if((isset($run_qry[0]->EXTRA)) && (isset($run_qry[0]->COLUMN_NAME))) {
			// If user DID NOT click the auto_increment checkbox
			/*if($disable_autoinc === 'false') {
				$content .= '<div class="warning_message">';
				$content .= __('This table contains an "auto increment" column.','wp_csv_to_db').'<br />';
				$content .= __('Please be sure to use unique values in this column from the .csv file.','wp_csv_to_db').'<br />';
				$content .= __('Alternatively, the "auto increment" column may be bypassed by clicking the checkbox above.','wp_csv_to_db').'<br />';
				$content .= '</div>';
				
				// Send additional response
				$enable_auto_inc_option = 'true';
			}*/
			// If the user clicked the auto_increment checkbox
			if($disable_autoinc === 'true') {
				$content .= '<div class="info_message">';
				$content .= __('This table contains an "auto increment" column that has been removed via the checkbox above.','wp_csv_to_db').'<br />';
				$content .= __('This means all new .csv entries will be given a unique "auto incremented" value when imported (typically, a numerical value).','wp_csv_to_db').'<br />';
				$content .= __('The Column Name of the removed column is','wp_csv_to_db').' <strong><em>'.$run_qry[0]->COLUMN_NAME.'</em></strong>.<br />';
				$content .= '</div>';
				
				// Send additional response 
				$enable_auto_inc_option = 'true';
			}
		}
	}
	else {
		$content = '';
		$content .= '<table id="ajax_table"><tr><td>';
		$content .= __('No Database Table Selected.','wp_csv_to_db');
		$content .= '<br />';
		$content .= __('Please select a database table from the dropdown box above.','wp_csv_to_db');
		$content .= '</td></tr></table>';
	}
	
	// Set response variable to be returned to jquery
	$response = json_encode( array( 'content' => $content, 'enable_auto_inc_option' => $enable_auto_inc_option ) );
	header( "Content-Type: application/json" );
	echo $response;
	die();
}

// Ajax call to process .csv file for column count
add_action('wp_ajax_bsp_get_csv_cols','bsp_get_csv_cols_callback');
function bsp_get_csv_cols_callback() {
	
	// Get file upload url
	$file_upload_url = $_POST['file_upload_url'];
	// Open the .csv file and get it's contents
	if(( $fh = @fopen($_POST['file_upload_url'], 'r')) !== false) {
		// Set variables
		$values = array();
		// Assign .csv rows to array
		while(( $row = fgetcsv($fh)) !== false) {  // Get file contents and set up row array
			// Each new line of .csv file becomes an array
			$rows[] = array(implode('", "', $row));
		}
		// Get a single array from the multi-array... and process it to count the individual columns
		$first_array_elm = reset($rows);
		$xplode_string = explode(", ", $first_array_elm[0]);
		// Count array entries
		$column_count = count($xplode_string);
	}
	else {
		$column_count = 'There was an error extracting data from the.csv file. Please ensure the file is a proper .csv format.';
	}
	// Set response variable to be returned to jquery
	$response = json_encode( array( 'column_count' => $column_count ) );
	header( "Content-Type: application/json" );
	echo $response;
	die();
}

//adding the action for saving the data from the form to the database
add_action('init', 'bsp_plugin_options_save');

//function for saving the data form the form to the database (adding te student)
function bsp_plugin_options_save(){
	
	global $wpdb;
	$tablename=$wpdb->prefix."students"; //geting our table name with prefix
	//checking if the button for submit is clicked
	if(isset($_POST['submit'])){
		//getting the values from a form
		$name=esc_attr($_POST['firstname']);
		$surname=esc_attr($_POST['lastname']);
		$email=sanitize_email($_POST['email']);
		
		if(!is_email($email)) {
			//Display invalid email error and exit
			?>
			<div class="wrap">
			<div class="error"><p>Invalid e-mail!</p></div>
			</div>
			<?php
			return;
		}
		
	//Checking to see if the user email already exists
    $data = $wpdb->get_results("SELECT * FROM $tablename WHERE students_email = '".$email."'");
		//if the count is greater then 0, it means that the user exsists
		if($wpdb->num_rows > 0){
        //Display duplicate entry error message and exit
			?>
				<div class="wrap">
					<div class="error"><p>Student exsist!</p></div> <!-- wp class error for error notices --->
				</div>
			<?php 
			return;
		}
			
		//assigning the new data to the table rows
		$newdata = array(
		'students_name'=>$name,
		'students_lastname'=>$surname,
		'students_email'=>$email,
		'students_date'=>current_time( 'mysql' ),
		);
		//inserting a record to the database
		$wpdb->insert(
		$tablename,
		$newdata
		);
			
		//displaying the success message when student is added
		?>
			<div class="wrap"><!-- wp class for wraping the text-->
				<div class="updated"><p>Student added!</p></div><!--wp class updated for success notices -->
			</div>
		<?php
	
	}//end of isset
}//end of function

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if(!class_exists('WP_List_Table')){
   require_once(ABSPATH .'wp-admin/includes/class-wp-list-table.php');
}

//we extend The WP List table class with our name bsp_list_table
class bsp_list_table extends WP_List_Table {

	 /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
    function __construct(){
		
   		parent:: __construct(array(
		'singular'=>"student",
		'plural'=>"students",
		'ajax'=>false
		));
	}
	//the most important function, that shows our data
	function prepare_items(){
		//setting the variables
		global $wpdb, $_wp_column_headers;
		$tablename=$wpdb->prefix."students"; //geting our table name with prefix
		//selecting everything from our database table
		$data="SELECT * FROM $tablename";
		//calling the function for bulk actions (delete in our example)
		$this->process_bulk_action();
		$screen=get_current_screen();
		//setting the sortable columns 
		//if no order, default to asc
		$orderby = !empty($_GET['orderby']) ? mysql_real_escape_string($_GET['orderby']) : 'ASC';
		//if no order leave it blank (as it is) - we can order it by name or id or...
        $order = !empty($_GET['order']) ? mysql_real_escape_string($_GET['order']) : '';
        //if it's not empty then query it from databse
        if(!empty($orderby) & !empty($order)){ $data.=' ORDER BY '.$orderby.' '.$order; }
 
		//Pagination parameters
        $totalitems = $wpdb->query($data); //return the total number of affected rows
        //How many to display per page?
        $perpage = 10;
        //Which page is this?
        $paged = !empty($_GET['paged']) ? mysql_real_escape_string($_GET['paged']) : '';
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);
        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage)){
            $offset=($paged-1)*$perpage;
            $data.=' LIMIT '.(int)$offset.','.(int)$perpage;
        }
        
        //Register the pagination
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ) );
        
        //register the columns
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        //fetch the items - getting the data from our table
        $this->items=$wpdb->get_results($data,'ARRAY_A');
	}
	
	//function for displaying the columns
	function get_columns(){
		$columns=array(
		'cb'=>'<input type="checkbox" />',
		//the name of the column in the database and the name we want shown in the table in wp
		'students_name'=>__('Name'),
		'students_lastname'=>__('Lastname'),
		'students_email'=>__('E-mail')
		);
		
		return $columns;
	}
	//function for sortable columns
	function get_sortable_columns(){
		$sortable=array(
		//we just want the columns name and lastname to be sortable, false means they are not sorted yet
		'students_name'=>array('students_name', false),
		'students_lastname'=>array('students_lastname', false)
		);
		return $sortable;
	}
	//the default columns to show
	function column_default($item, $column_name){
		switch($column_name){
			case 'students_name':
			case 'students_lastname':
			case 'students_email':
			return $item[$column_name];
			
			default:
			//for debugging purposes we print out the whole array
			return print_r($item, true);
		}
	}
	//function for showing the delete mouse over option in the name column
	function column_students_name($item){
	
		$nonce = wp_create_nonce( 'bsp_delete_student' );
		$title = '<strong>' . $item['students_name'] . '</strong>';
		$actions=array(
		
		//creating the delete link under the name column
		'delete' => sprintf( '<a href="?post_type=%s&page=%s&action=%s&student=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['post_type']), esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['students_id'] ), $nonce )
		);
		
		return $title . $this->row_actions( $actions );
		//Return the title contents
       /* return sprintf('%1$s %2$s',
            /*$1%s/ $item['students_name'],
            /*$2%s/ $this->row_actions($actions)
        );*/
	}
	
	//rewriting the get_bulk_actions function for showing the delete option
	function get_bulk_actions(){
		$actions=array(
		'delete'=> __( 'Delete' )
		);
		return $actions;	
	}	
	//function that handles the delete action
	function process_bulk_action() {

		//Detect when a bulk action is being triggered
		if ('delete' === $this->current_action() ) {
		/*	// In our file that handles the request, verify the nonce.
			$nonce = $_REQUEST['_wpnonce'];

			if ( !wp_verify_nonce( $nonce, 'bsp_delete_student' ) ) {
				die( 'Invalid security check!' );
			}
			else {*/
				self::delete_student( absint( $_GET['student'] ) );
				//wp_redirect( esc_url( add_query_arg())); exit;
			//}
		}
		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'delete' )
		) {

			$delete_ids = esc_sql( $_POST['student'] );

			// loop over the array of record IDs and delete them
			foreach ($delete_ids as $id ) {
				self::delete_student( $id );

			}

			//wp_redirect( esc_url( add_query_arg())); exit;
			//wp_die('Items deleted (or they would be if we had items to delete)!');
		}
	}
	
	//function for deleting a student from the table
	public static function delete_student($id){
		global $wpdb;
		$wpdb->delete("{$wpdb->prefix}students", array(
			 'students_id' => $id ,
			 //'%d' 
		));
	}
	
	//ading the checkboxes to the table %1$s we use the tables singular label, %2$s the value of the chechbox is the students id
	function column_cb( $item ) {
		return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', 
			$this->_args['singular'], 
			$item['students_id']
		);
	}
	//function for displaying the text when database is empty
	function no_items() {
		_e( 'The database is empty.' );
	}
	

}//end of class

/** Singleton instance */
	function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

//function for displaying the custom table data inwp_list_table
function bsp_students_show(){
	//new instance of wp_list_table
	$bsplisttable = new bsp_list_table();
	//outputing the headline before table
	echo '<div class="wrap"><h2>'. __('Enroled students').'</h2>';
	//calling function prepare_items and display
		$bsplisttable->prepare_items();
	?>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form name ="award_badges" action="<?php echo admin_url( 'admin.php' ); ?>" method="post">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <!-- Now we can render the completed list table -->
        <?php
        
        $bsplisttable->display();
        ?>
    
		<h2>Available badges to award</h2>
		<?php
		//getting the current user meta data
			global $current_user;
			get_currentuserinfo();
			$id=$current_user->ID;
			//getting the posts id of selected post in an array
			$all_meta_user=get_user_meta($id, 'bsp_teacher_badges', true);
			$postid = get_the_ID();
			?>
			
			<!--<form name="award_badges" action="<?php echo admin_url( 'admin.php' ); ?>" method="post">
			--><input type="hidden" name="action" value="bsp_award_badges" />
			<?php
			
			//printing just the titles of the post ids
			foreach($all_meta_user as $all_meta){
				?>
				<div><p><input type="checkbox" name="bsp_selected[]" value="<?php echo $all_meta; ?>" <?php echo gettype($all_meta) == 'array' && in_array($all_meta) ? 'checked="checked"':' '; ?> />
				<?php echo get_the_title($all_meta); ?>
				</p>
				</div>
				<?php
					}

	?>
		<p class="submit">
		<input id="bsp_award_submit" name="bsp_award_submit" type="submit" class="button-primary" value="<?php _e('Award badges') ?>" />
	   </p>
	  </form>
	   </div>	
    <?php
}//end of function

add_action('admin_action_bsp_award_badges', 'bsp_save_awarded_badges');

function bsp_save_awarded_badges(){
	//getting the wp database
	global $wpdb;
	$tablename=$wpdb->prefix."students"; //geting our table name with prefix
		
	//getting the meta data for current user
	global $current_user;
	get_currentuserinfo();
	//our checked items are "saved" in the $_POST array
	$badges=$_POST['bsp_selected'];
	$students=$_POST['student'];
	
	//getting the data from our custom table and checking if the id in the table matches the id of selected student	
	$data=$wpdb->get_results("SELECT students_name, students_lastname, students_email FROM $tablename WHERE students_id in (".implode(', ', $students).")");
				
	//if the button is clicked	
	if(isset($_POST['bsp_award_submit'])){
		//we need to check the array of selected badges to get them
		foreach($badges as $badge){
			//and get the title
			$badge_name=get_the_title($badge);
			//the id
			$badge_id=$badge;
			//the image src url
			$badge_image=wp_get_attachment_image_src(get_post_thumbnail_id($badge_id));
				$badge_image=$badge_image[0];
			//the description
			$desc=get_post($badge_id);
			$badge_desc=$desc->post_content;
			
		
			
			//get the levels
			$termslvl=wp_get_post_terms($badge, array('levels'));
			$badge_lvl='';
			if(!is_wp_error($termslvl)){
				$termslvl_all=array();
				foreach($termslvl as $termlvl){
					$termslvl_all[]= $termlvl->name;
				}
				$badge_lvl=implode($termslvl_all, ', ');
			}
			
			//getting the skill
			$termsskil=wp_get_post_terms($badge, array('skills'));
			$badge_skil='';
			if(!is_wp_error($termsskil)){
				$termsskil_all=array();
				foreach($termsskil as $termskil){
					$termsskil_all[]=$termskil->name;
				}
				$badge_skil=implode($termsskil_all, ', ');
			}
				
			//sent the email for each selected student
			foreach($data as $da){
				$email_stud=$da->students_email;
				bsp_send_badge_email($email_stud, $badge_id, $badge_name, $badge_desc, $badge_image, $badge_lan, $badge_skil, $badge_lvl);	
			}
			
		}//end of foreach badge
		
	//displaying the success message when student is added
		?>
			<div class="wrap"><!-- wp class for wraping the text-->
				<div class="updated"><p>Awards sent!</p></div><!--wp class updated for success notices -->
			</div>
		<?php
		
	}//end of isset
		  
	//need to use wp_redirect so that we stay on the same page
	wp_redirect($_SERVER['HTTP_REFERER'] );
	
    exit();
}
   
//function for sending the email and json files  
function bsp_send_badge_email($email_stud, $badge_id, $badge_name, $badge_desc, $badge_image, $badge_lan, $badge_skil, $badge_lvl){
	
	//adding a salt to our hashed email
    $salt=uniqid(mt_rand(), true);
    //using sha256 hash metod (open badges api defined)
    $hash='sha256$' . hash('sha256', $email_stud. $salt);
    //setting the current date
    $date=date('Y-m-d');

	//getting the settings data
	$name_issuer=get_option('bsp_issuer_name');
	$org_issuer=get_option('bsp_issuer_org');
	$email_issuer=get_option('bsp_issuer_email');
	$url_issuer=get_option('bsp_issuer_url');
	
	//string for encoding the email student and badge name (used in str_rot13)
	$str = $email_stud.$badge_name;
	//encoding the json files
	$file_json=str_rot13($badge_id . '-' . preg_replace("/ /", "_", $email_stud));
	//getting the dir path of the plugin to use
	$dir_path=plugin_dir_path( __FILE__ );
	//adding the folder json and encoded file name and addind the extenson of json
	$path_json=$dir_path.'json/'.$file_json.'.json';
	
	//handle for opening or creating the file and writing to it (w)
	$handle=fopen($path_json, 'w') or die ('Can not open file: '.$file_json);
	if($handle){
		//data for issuing the badge (mozilla open badges api specified)
		$data=array(
			'recipient'=> $hash,
			'salt'=>$salt,
			'badge'=>array(
				'name'=>$badge_name,
				'description'=>$badge_desc,
				'image'=>$badge_image,
				'criteria'=>'http://about.badges4languages.org/',
				'issuer'=>array(
					'name'=>$name_issuer,
					'origin'=>$url_issuer,
					'email'=>$email_issuer,
				)
			),
			'verify'=>array(
				'type'=>'hosted',
				'url'=>plugins_url( 'json/', __FILE__ ).$file_json.'.json',
			),
			'issued_on'=>$date
			);
		//encoding the data into json format	
		if(fwrite($handle, json_encode($data))){
			fclose($handle);
			//getting the url of the page by title (our custom created page)
			$pagelink=esc_url( get_permalink( get_page_by_title( 'Accept Badge' ) ) );
			
				//form for sending an email in html 
				$mail = $email_stud; //setting the to who this email is send
				$mailFrom = $email_issuer; //setting the from who this email is
				$subject = "You have just earned a badge"; //entering a subject for email
				//encoding the url
				$url = str_rot13(base64_encode(plugins_url('json/', __FILE__).$file_json.'.json'));

				//the actual message, which is displayed in an email
				$message= ' 
				<html>
					<head>
						<meta http-equiv="Content-Type" content="text/html"; charset="utf-8" />
					</head>
					<body>
					<div id="bsp-award-actions-wrap">
					<img src="' . plugins_url( 'images/OpenBadges.png', __FILE__ ) . '" align="right">
					<div align="center">
					<img src="' . plugins_url( 'images/logo_b.png', __FILE__ ) . '" > 
						<h1>Congratulations you have just earned a badge!</h1>
							<h2>'.$badge_name.' '.$badge_lan.' '.$badge_lvl.' '.$badge_skil.'</h2>
							
							<a href="'.$pagelink.'?id='.$badge_id.'&filename='.$url.'">
							<img src="'.$badge_image.'"></a></br>
							<p>Description: '.$badge_desc.'</p>
						<h2 class="acceptclick">Click on the badge to add it to your Mozilla Backpack!</h2>
						<div class="browserSupport"><b>Please use Firefox or Google Chrome to retrieve your badge.<b></div>
						</div>
					</body>
				</html>
				';
				$json_hosted_file=plugins_url('json/', __FILE__ ).$file_json.'.json';
				
				//seting headers so it's a MIME mail and a html
				// Always set content-type when sending HTML email
				$headers = "From: Badges4languages "."<".$mailFrom. ">"."\n";
				$headers .= "MIME-Version: 1.0"."\n";
				$headers .= "Content-type: text/html; charset=ISO-8859-1"."\n";
				$headers .= "Reply-To: info@badges4languages.org"."\n";

				mail($mail, $subject, $message, $headers); //the call of the mail function with parameters
		}//end of if fwrite
	}//end of if handle	
	
}//end of function

//adding the hook for adding to the content of the page
add_filter('the_content','bsp_content_filter');
//function for adding the content to the page
function bsp_content_filter($content){
	//we are checking if we are on the page accept-badge, because we want the content to be displayed only there
	if ( is_page( 'accept-badge' )){
	
	//getting the filename and the id from the url
	if(isset( $_GET['filename']) && ($_GET['id']) ){
       $path_json = $_GET['filename'];
	 
        //and decoding it (so we get a "normal" file name)
		$path_json = base64_decode(str_rot13($path_json));

		$badge_name=$_GET['id'];
		$badge_title=get_the_title($badge_name);
	}
	?>
	<!-- including the issuer api script -->
	<script src="https://backpack.openbadges.org/issuer.js"></script>
	<script type="text/javascript">
	<!--- function for issuing the badge -->
	jQuery(document).ready(function($) {
	
	$('.js-required').hide();
	
	if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)){  //The Issuer API isn't supported on MSIE Bbrowsers
		$('.acceptclick').hide();
		$('.browserSupport').show();
		}else{
			$('.browserSupport').hide();
		}
	
	$('#badge-error').hide();
	$('.acceptclick').click(function() {		
	var assertionUrl = '<?php echo $path_json; ?>';
       OpenBadges.issue([''+assertionUrl+''], function(errors, successes) { 
		   
					if (errors.length > 0 ) {
						$('#badge-error').show();	
						$.ajax({
    					url: '<?php get_post_type_archive_link( 'badge' ); ?>',
    					type: 'POST',
    					data: { 
							action:'award_action'
							}
						});
					}
					
					if (successes.length > 0) {
							$('.acceptclick').hide();
							$('#badge-error').hide();
							$.ajax({
    						url: '<?php get_post_type_archive_link( 'badge' ); ?>',
    						type: 'POST',
    						data: { 
								action:'award_action'
								}
							});
						}	
					});    
				});
			});


   </script>
   
   <?php

   //the content to be displayed on the template page
		
		 $content = <<<EOHTML
                <div id="bsp-award-actions-wrap">
                <div id="badgeSuccess">
                    <p>Congratulations! The "{$badge_title}" badge has been awarded to you.</p>
                    <p class="acceptclick">Please <a href='#' class='acceptclick'>accept</a> the award.</p>
                </div>
                </div>
                <div class="browserSupport">
                    <p>Microsoft Internet Explorer is not supported at this time. Please use Firefox or Chrome to retrieve your award.</p>
                </div>
                <div id="badge-error">
                    <p>An error occured while adding this badge to your backpack.</p>
                </div>
                </div>
                {$content}
EOHTML;
	
	//$content .= $content;
	
	
     }//end of is page   
	return $content;
}//end of function
    
//action for columns
add_action("manage_posts_custom_column",  "bsp_badge_custom_columns");
//filter for columns
add_filter("manage_edit-badge_columns", "bsp_badge_edit_columns");

//function for setting up the custom columns in show all cpts (show all badges)
function bsp_badge_edit_columns($columns){
  $columns = array(
	'cb'=> '<input type="checkbox" />',
    'title' => 'Badge Title',
    'description' => 'Description',
    'levels' => 'Levels',
    'skills' => 'Skills',
    'featured_image' => 'Image'
  );
 
  return $columns;
}//end of function

//function for showing columns from our database table in show all cpts (show all badges)
function bsp_badge_custom_columns($column){
  global $post;
 
  switch ($column) {
    case 'description':
      the_excerpt();
      break;
    case 'levels':
     echo get_the_term_list($post->ID, 'levels' , '' , ',' , '');
      break;
    case 'skills':
      echo get_the_term_list($post->ID, 'skills', '' , ',' , '');
      break;
    case 'featured_image':
	 the_post_thumbnail('thumbnail' );
	  break;
  }
}//end of function

//action for adding meta boxes
add_action('add_meta_boxes','bsp_add_meta_box');

//function for adding a meta box
function bsp_add_meta_box(){
	//slug, name, callback function, post type and priority-where to show the meta box
	add_meta_box('bsp_mother_language', 'Mother Language', 'bsp_show_metabox', 'badge', 'normal' );
}

//function for showing the metabox on admin menu
function bsp_show_metabox($post){
	//security check
	wp_nonce_field( basename( __FILE__ ), 'bsp_nonce' );
	//geeting the content of a post where metabox is
    $bsp_stored_meta = get_post_meta( $post->ID );
    //html code for actualy showing the metabox
    ?>
    <p><!--label -->
        <label for="bsp_meta-text" class="bsp-row-title"><?php _e( 'Your mother language', 'bsp-textdomain' )?></label>
        <!-- textarea where the content for metabox is placed -->
        <textarea name="bsp_meta-text" id="bsp_meta-text" class="widefat" style="overflow:auto; resize:none" rows="15"><?php if ( isset ( $bsp_stored_meta['bsp_meta-text'] ) ) echo $bsp_stored_meta['bsp_meta-text'][0]; ?></textarea>
    </p>
 
    <?php
}//end of function

//action for saving post with meta box
add_action('save_post','bsp_meta_save');

//function for saving metabox content with post
function bsp_meta_save($post_id){
	// Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'bsp_nonce' ] ) && wp_verify_nonce( $_POST[ 'bsp_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
    // Checks for input and saves if needed
    if( isset( $_POST[ 'bsp_meta-text' ] ) ) {
        update_post_meta( $post_id, 'bsp_meta-text',  $_POST[ 'bsp_meta-text' ] );
    }
}//end of function

//adding the filter the_content to show the meta box in themes single.php page
add_action('the_content', 'bsp_meta_content');

function bsp_meta_content($content){
	//checking if it's single
	if(is_single()){
		global $post; //geting the post
		//getting the value of our metabox
		$value=get_post_meta($post->ID, 'bsp_meta-text', true);
		//checking if we have value
		if($value){
			//adding to the content of a single page our value of metabox
			$content .= $value;
		}
	}
	return $content;
}//end of function

//for the theme listify (currently using) we don't need to show the feautered image in the single.php because the theme does that already
/*
//another filter for showing the images on the post
add_action('the_content', 'bsp_image_content');

//function for showing images on the post
function bsp_image_content($content){
	//checking if it's single post of  our custom post named badge and if we are it that query
	if(is_singular( 'badge' ) && is_main_query()){
		//new variable to store our thumbnail image
		$image=the_post_thumbnail();
		//adding the image to the content
		$content.=$image;
	}
    //returning the new content
    return $content;

}//end of function*/

//widget for taxonomies
function bsp_widgets_init() {
  if ( !is_blog_installed() )
    return;
 
  register_widget( 'WP_Widget_Taxonomy_Terms' );
 
  do_action( 'widgets_init' );
}
 
add_action( 'init' , 'bsp_widgets_init' , 1 );

//our class extends the WP_Widget class 
class WP_Widget_Taxonomy_Terms extends WP_Widget {
 
 //widget settings
  function WP_Widget_Taxonomy_Terms() {
    $widget_ops = array( 'classname' => 'widget_taxonomy_terms' , 'description' => __( "A list of taxonomy terms" ) );
    $this->__construct( 'taxonomy_terms' , __( 'Taxonomy Terms' ) , $widget_ops );
  }
 
  function widget( $args , $instance ) {
    extract( $args );
 
    $current_taxonomy = $this->_get_current_taxonomy( $instance );
    $tax = get_taxonomy( $current_taxonomy );
    if ( !empty( $instance['title'] ) ) {
      $title = $instance['title'];
    } else {
      $title = $tax->labels->name;
    }
 
    global $t;
    $t = $instance['taxonomy'];
    $f = $instance['format'];
 
    $w = $args['widget_id'];
    $w = 'ttw' . str_replace( 'taxonomy_terms-' , '' , $w );
 
    echo $before_widget;
    if ( $title )
      echo $before_title . $title . $after_title;
 
    $tax_args = array( 'orderby' => 'name' , 'taxonomy' => $t );
 
    if ( $f == 'list' ) {
?>
    <ul>
<?php
    $tax_args['title_li'] = '';
    wp_list_categories( apply_filters( 'widget_categories_args' , $tax_args ) );
?>
    </ul>
<?php
    } else {
?>
    <div>
<?php
      wp_tag_cloud( apply_filters( 'widget_tag_cloud_args' , array( 'taxonomy' => $t ) ) );
?>
    </div>
<?php
 
    }
    echo $after_widget;
  }
 
 //function for getting the new values
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['taxonomy'] = stripslashes( $new_instance['taxonomy'] );
    $instance['format'] = stripslashes( $new_instance['format'] );
 
    return $instance;
  }
 
 //function for how the widgets look on admin side
  function form( $instance ) {
    //Defaults
    $instance = wp_parse_args( (array) $instance , array( 'title' => '' ) );
    $current_taxonomy = $this->_get_current_taxonomy( $instance );
    $current_format = esc_attr( $instance['format'] );
    $title = esc_attr( $instance['title'] );
 
?>
    <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
 
    <p><label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy:' ); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
<?php
 
    $args = array(
      'public' => true ,
      '_builtin' => false
    );
    $output = 'names';
    $operator = 'and';
 
    $taxonomies = get_taxonomies( $args , $output , $operator );
    $taxonomies = array_merge( $taxonomies, array( 'category' , 'post_tag' ) );
    foreach ( $taxonomies as $taxonomy ) {
      $tax = get_taxonomy( $taxonomy );
      if ( empty( $tax->labels->name ) )
        continue;
?>
    <option value="<?php echo esc_attr( $taxonomy ); ?>" <?php selected( $taxonomy , $current_taxonomy ); ?>><?php echo $tax->labels->name; ?></option>
<?php
    }
?>
    </select></p>
 
    <p><label for="<?php echo $this->get_field_id( 'format' ); ?>"><?php _e( 'Format:' ) ?></label>
   <select class="widefat" id="<?php echo $this->get_field_id( 'format' ); ?>" name="<?php echo $this->get_field_name( 'format' ); ?>">
<?php
 
    $formats = array( 'list' );
    foreach( $formats as $format ) {
 
?>
    <option value="<?php echo esc_attr( $format ); ?>" <?php selected( $format , $current_format ); ?>><?php echo ucfirst( $format ); ?></option>
<?php
    }
?>
    </select></p>
<?php
  }
  //function for displaying the taxonomies
  function _get_current_taxonomy( $instance ) {
    if ( !empty( $instance['taxonomy'] ) && taxonomy_exists( $instance['taxonomy'] ) )
      return $instance['taxonomy'];
    else
      return 'category';
  }
}//end of widget class

//function for selecting the badges		
function bsp_choose_badges(){
	//getting the data for the user
	global $current_user;
    get_currentuserinfo();
    //meta data from user and adding the value of bsp_teacher_badges
    $meta = get_user_meta($current_user->ID, 'bsp_teacher_badges', true);
	?>
	<div class="wrap"><h2>Select your badges</h2>
	<form name="select_badges" action="<?php echo admin_url( 'admin.php' ); ?>" method="post">
	<input type="hidden" name="action" value="bsp_check_badges" />
     <?php
     //getting all the custom post types
		$bspposts = get_posts( array( 'post_type' => 'badge' ) );
		foreach($bspposts as $bsppost){			
			?>
			<div>
				<p><!--outputing the check boxes and the title of the custom post, our value is the post id -->
					<input type="checkbox" name="bsp_ba_title[]" value="<?php echo $bsppost->ID; ?>" <?php echo gettype($meta) == 'array' && in_array($bsppost->ID, $meta) ? 'checked="checked"':' '; ?> />
						<?php echo $bsppost->post_title; ?>
				</p>
			</div>
			<?php
			}		
	?><!-- the button for submiting the selected badges -->
	<p class="submit">
     <input id="bsp_ba_submit" name="bsp_ba_submit" type="submit" class="button-primary" value="<?php _e('Save badges') ?>" />
   </p>
   <div><p><small>After clicking the Save badges button you can see your selected badges and send them to the students. To see selected badges click on the "Show current students" left menu</p></small></div>
   </form>
   </div>
	<?php
}//end of function

//adding the action for checked checkboxes
//good practice to add admin action for plugins
add_action('admin_action_bsp_check_badges', 'bsp_save_checked_badges');

//function for saving the selected badges to the current user meta data
function bsp_save_checked_badges() {
	//getting the meta data for current user
	global $current_user;
	get_currentuserinfo();
	$title=$_POST['bsp_ba_title'];
	$postid = get_the_ID();
	
	if(isset($title) && count($title) > 0) {
    $old_user_meta = get_user_meta($current_user->ID, 'bsp_teacher_badges', true);
    if(isset($old_user_meta) && gettype($old_user_meta) == 'array') {
      //Find the badges the user has deselected
      $absent_badges = array_diff($old_user_meta, $title);
      if(count($absent_badges) > 0) {
        foreach($absent_badges as $absent_badge) {
          //Remove the user id from the deselected badges
          $old_badge_meta = get_post_meta($absent_badge, 'bsp_teacher_badges', true);
          if(isset($old_badge_meta) && gettype($old_badge_meta) == 'array') {
            if(($key = array_search($current_user->ID, $old_badge_meta)) != false) {
              unset($old_badge_meta[$key]);
              update_post_meta($absent_badge, 'bsp_teacher_badges', $old_badge_meta);
            }
          }
        }
      }
    }
     //Update every badge's post meta with the current user's ID
    foreach($title as $single) {
      $old_badge_meta = get_post_meta($single, 'bsp_teacher_badges', true);
      if(!isset($old_badge_meta) || gettype($old_badge_meta) != 'array') {
        $old_badge_meta = array($current_user->ID);
      } else {
        //Important: If the user has already selected the badge, then do nothing
        if(!in_array($current_user->ID, $old_badge_meta)) {
          $old_badge_meta[] = $current_user->ID;
        }
      }
      update_post_meta($single, 'bsp_teacher_badges', $old_badge_meta);
    }
    
    //Update the current user's meta with selected badge ids
    update_user_meta($current_user->ID, 'bsp_teacher_badges', $title);
    wp_redirect( $_SERVER['HTTP_REFERER'] );
	} else {
    //Remove the current user's id from all the badges he has
    $old_user_meta = get_user_meta($current_user->ID, 'bsp_teacher_badges', true);
    if(isset($old_user_meta) && gettype($old_user_meta) === 'array') {
      foreach($old_user_meta as $old) {
        $old_badge_meta = get_post_meta($old, 'bsp_teacher_badges', true);
        if(isset($old_badge_meta) && gettype($old_badge_meta) === 'array') {
          if(($key = array_search($current_user->ID, $old_badge_meta)) !== false) {
            unset($old_badge_meta[$key]);
            update_post_meta($old, 'bsp_teacher_badges', $old_badge_meta);
          }
        }
      }
    }

    //Delete this user's badges since he selected none
    delete_user_meta($current_user->ID, 'bsp_teacher_badges');
    wp_redirect( $_SERVER['HTTP_REFERER']);
  }
	
  exit();
}//end of function

//for hiding the media from other users - user can only see his uploaded media
//http://codex.wordpress.org/Plugin_API/Filter_Reference/ajax_query_attachments_args#Examples
add_filter( 'ajax_query_attachments_args', 'bsp_show_current_user_attachments' );

//for the ajax image uploader
function bsp_show_current_user_attachments( $query ) {
    global $current_user;
    if ( !$current_user_can('manage_options') ) {
        $query['author'] = $current_user->ID;
    }
    return $query;
}
//for the upload in media library
function bsp_my_files_only( $wp_query ) {
	if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/wp-admin/upload.php' ) !== false ) {
	    if ( !current_user_can( 'manage_options' ) ) {
			global $current_user;
			$wp_query->set( 'author', $current_user->ID );
	    }
	}
}
 
add_filter('parse_query', 'bsp_my_files_only' );

//function for setting up custom options inthe settings page
function bsp_plugin_options_page(){
	?>
		<!-- html formatting for following the wp styles -->
		<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Settings for badges</h2> <!-- title for the page  -->
		<form action="options.php" method="post"> <!-- takes care of saving the options to the database -->
		<?php settings_fields('bsp_plugin_options'); ?> <!-- name of the settings fields -->
		<?php @do_settings_fields('bsp_plugin_options');?>
		<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="bsp_issuer_name">Name of the issuer</label></th>
					<td>
						<input type="text" name="bsp_issuer_name" id="bsp_issuer_name" value="<?php echo get_option('bsp_issuer_name'); ?>" /> <!-- getting the current value for this setting -->
						<!-- help text -->
						<br/><small>Input the name of the issuer for badges</small>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="bsp_issuer_org">Name of the issuing organization</label></th>
					<td>
						<input type="text" name="bsp_issuer_org" id="bsp_issuer_org" value="<?php echo get_option('bsp_issuer_org'); ?>" />
						<br/><small>Input the name of the issuing organization</small>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="bsp_issuer_email">Email of the issuer</label></th>
					<td>
						<input type="text" name="bsp_issuer_email" id="bsp_issuer_email" value="<?php echo get_option('bsp_issuer_email'); ?>" />
						<br/><small>Input the email of the issuer</small>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="bsp_issuer_url">URL of the issuer</label></th>
					<td>
						<input type="text" name="bsp_issuer_url" id="bsp_issuer_url" value="<?php echo get_option('bsp_issuer_url'); ?>" />
						<br/><small>Input the URL address of the issuer page</small>
					</td>
				</tr>
			
			</table> <?php @submit_button(); ?> <!-- adding the save changes button -->
	</div>
	</form>
	<?php
}

//adding the action for settings page
add_action('admin_init', 'bsp_settings_init');

function bsp_settings_init(){
//register_settings is called as many times as you have fields
register_setting( 'bsp_plugin_options', 'bsp_issuer_name'); //first parameter must be the same as the name in settings_fields and the second paramater is the name of the field (defined in the form)
register_setting( 'bsp_plugin_options', 'bsp_issuer_org');
register_setting( 'bsp_plugin_options', 'bsp_issuer_email');
register_setting( 'bsp_plugin_options', 'bsp_issuer_url');
}

//adding a filter to include custom template
add_filter( 'template_include', 'bsp_accept_badge_template');

//function for setting the custom template
function bsp_accept_badge_template( $template ) {
	//checking if the page has the slug of accept-badge
	if ( is_page( 'accept-badge' )  ) {
		//creating new template
		$new_template = locate_template( array( 'badges-accept-template.php' ) );
		//if the new tempalte is not empty then use the template
		if ( '' != $new_template ) {
			return $new_template ;
		}
	}
	return $template;
}//end of function

//widget for selfawarding a badge
//using a wp class WP_Widget for creating a widget
class wp_widget_selfaward_badge extends WP_Widget {
	//constructor
	function wp_widget_selfaward_badge(){
		parent::__construct(false, $name= __('Selfaward Badge Widget', 'wp_widget_selfaward_badge'));
	}
	
	//widget form creation
	function form($instance) {
		if($instance){
			$title = esc_attr($instance['title']); //title of the widget
		}else{
			$title='';
		}
			
		?>
		
		<!--label for title of the widget-->
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Enter title: ', 'wp_widget_plugin'); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" />
		</p>
	<?php
	
	}
	
	//widget update - replacing the old instance with a new one
	function update($new_instance, $old_instance){
		$instance =$old_instance;
		
		$instance['title']=strip_tags($new_instance['title']);
		
		return $instance; //returning the new instance with new data
	}
	
	//widget display-how the widget is displayed
	function widget($args, $instance){
		wp_enqueue_style('bsp_style',plugins_url('css/style.css', __FILE__));
		
			extract($args);
			
			$title=apply_filters('widget_title', $instance['title']);
			
			echo $before_widget;
			
			//display the widget
			echo '<div>';
			
			//check if title is set
			if($title){
			echo $before_title.$title.$after_title;
			}
			
			//showing the available languages
			?>
			<div id="languages">
			<select id="bsp_lang_badges" name="bsp_lang_badges"> 
			 <?php 
			 //getting  languages
                        global $wpdp;
                        $table_name=$wpdp->base_prefix."languages";
                        $languages =$wpdb->get_results("SELECT ref_name,short_code FROM $table_name",ARRAY_A); ;
                                                             
			  foreach ($languages as $language) {
				echo '<option value="'.$language["short_code"].'">'.$language["ref_name"].'</option>';
			  }
			 ?>
			</select>
			</div>
			</br>
			<!-- showing the available skills -->
			<div id="select_skills">
			 <?php 
			 //getting the taxonomy skills
			  $skills = get_categories('taxonomy=skills');
			  ?>
			  <ul>
				  &#123; <!-- left curly bracket { -->
			  <?php
			  
			  foreach ($skills as $skill) {
					echo '<li class="bsp_skills" value="'.$skill->cat_name. '" id="skill-'.$skill->term_id.'"><a id="skill-'.$skill->term_id.'" >'.$skill->cat_name. '</a></li>';
					echo '<div class="bsp_desc" id="desc-'.$skill->term_id.'">'.$skill->description.'</div>';
					}
					?>
					&#125; <!-- right curly bracket } -->
					</ul> 
			
			</div>
			</br>
			<div id="levels">
			<select id="bsp_lvl_badges" name="bsp_lvl_badges"> 
			<?php
				 //getting the taxonomy skills
			  $levels = get_categories('taxonomy=levels');
				 foreach ($levels as $level) {
					echo '<option value="'.$level->term_id.'">'.$level->cat_name.'</option>';
					}
				?>
				</select>
				</div>
				</br>
				
				<?php
			
				echo '<div id="bsp_images"></div>';
				
			
			echo '</br>'; 
			?>
			
			<!--Form for sendig information via email -->	
			<input type="text" id="bsp_email" placeholder="Enter your email" value=""/> 
			<button id="bsp_send">Send email</button>
			
			<div id="bsp_success">You have just sent a badge!</div>
	
			
			<?php
			echo '</div>';
			echo $after_widget;
		}
	
}//end of widget class


//register widget
add_action('widgets_init', create_function('', 'return register_widget("wp_widget_selfaward_badge");'));


add_action('wp_ajax_nopriv_bsp_select_cpt', 'bsp_select_cpt_by_skill');
add_action('wp_ajax_bsp_select_cpt', 'bsp_select_cpt_by_skill');

function bsp_select_cpt_by_skill() {
  $skill = $_POST['skill'];
  $args = array(
        'post_type' => array(
          'attachment',
          'badge'
        ),
        'tax_query' => array(
          array(
            'taxonomy' => 'skills',
            'field'    => 'term_id',
            'terms'    => $skill
          )
        ));
 
  $badges = get_posts($args);
  $images = array();
  foreach($badges as $badge){
    $image_id=$badge->ID;
    $image= wp_get_attachment_image_src(get_post_thumbnail_id($image_id));
    $image=$image[0];
  
    if($image){
      $images[] = array( 'badge'=> $badge->ID, 'image'=> $image );
    }
  }
      echo json_encode($images);
  wp_die();
}

add_action('wp_ajax_nopriv_bsp_send_email', 'bsp_send_badge_email_ajax');
add_action('wp_ajax_bsp_send_email', 'bsp_send_badge_email_ajax');

function bsp_send_badge_email_ajax() {
  $email_stud = $_POST['data']['email_add'];
  $badge_id = $_POST['data']['badge'];

  //and get the title
  $badge_name=get_the_title($badge_id);
  
  //the image src url
  $badge_image=wp_get_attachment_image_src(get_post_thumbnail_id($badge_id));
  $badge_image=$badge_image[0];
  //the description
  $desc=get_post($badge_id);
  $badge_desc=$desc->post_content;
      
  //getting the languages
  //join them on a comma if multiple nad leave blank it there is no language
  //getting the custom taxonomies, name and description
  $terms = wp_get_post_terms($badge_id, array('languages')); //we want to get all term in taxonomy with the name languages
  $badge_lan='';
  //if everything is ok
  if(!is_wp_error($terms)){
    //get the term into an array
    $terms_all=array();
    //loop through the array
    foreach($terms as $term){
      //and get the names of the taxonomy
      $terms_all[]=$term->name;
    }
    //divide the names by comma
    $badge_lan=implode($terms_all, ', ');
  }
      
  //get the levels
  $termslvl=wp_get_post_terms($badge_id, array('levels'));
  $badge_lvl='';
  if(!is_wp_error($termslvl)){
    $termslvl_all=array();
    foreach($termslvl as $termlvl){
      $termslvl_all[]= $termlvl->name;
    }
    $badge_lvl=implode($termslvl_all, ', ');
  }
      
  //getting the skill
  $termsskil=wp_get_post_terms($badge_id, array('skills'));
  $badge_skil='';
  if(!is_wp_error($termsskil)){
    $termsskil_all=array();
    foreach($termsskil as $termskil){
      $termsskil_all[]=$termskil->name;
    }
    $badge_skil=implode($termsskil_all, ', ');
  }
  
  
  
  bsp_send_badge_email($email_stud, $badge_id, $badge_name, $badge_desc, $badge_image, $badge_lan, $badge_skil, $badge_lvl);  
  echo $email_stud.', '.$badge_id.', '.$badge_name.', '.$badge_desc.', '.$badge_image.', '.$badge_lan.', '.$badge_skil.', '.$badge_lvl;
  wp_die();
}
