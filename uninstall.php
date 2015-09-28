<?php

	//only execute the contents of this file if the plugin is really being uninstalled
	if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit ();
	}

	global $wpdb;
	$tablename = $wpdb->prefix . "students"; //geting the name of the table with its prefix

	//checking if there is a table with the same name in the database
	if( $wpdb->get_var("SHOW TABLES LIKE '$tablename'") == $tablename ) {
		//and if its is, delete it
		$sql = "DROP TABLE `$tablename`;";
		$wpdb->query($sql);
	}
	
	/*
	 * // Delete All Custom Type Posts
	 * //defining the arguments for our post (what are we looking for)
	 $del=array(
        'numberposts' => -1,
        'post_type' => 'badge', 
        'post_status' => 'any' 
        );
        //creating a new instance of wp query
        $cpts=new WP_Query($del);
        //the check for the posts
        if($cpts->have_posts()):
        while ($cpts->have_posts()):$cpts->the_post();
        //getting the post id
        $cpt_id=get_the_ID();
        //calling the delete post function
        wp_delete_post($cpt_id, true);
        endwhile;
        endif;
	 * */
	 
	 //Delete custom page
	 //defining the argument for our page
	 $args=array(
		 'meta_key'=>'bsp_delete_page',
		 'post_type'=>'page',
		 'post_status'=>'publish'
	 );
	 
	 //creating a new instance of wp query
	 $pages=new WP_Query($args);
	 //checking if it has post
	 if($pages->have_posts()):
		while ($pages->have_posts()):$pages->the_post();
		//getting the page id
		$post_id=get_the_ID();
		//calling the delete function
		wp_delete_post($post_id, true);
		endwhile;
	endif;
	
	//deleting the options values in settings
	$option_name = 'bsp_plugin_options';
	delete_option( $option_name );
 
