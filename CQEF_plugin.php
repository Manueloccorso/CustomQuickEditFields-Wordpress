<?php
/**
 * Plugin Name: Custom Quick Edit Fields
 * Description: Plugin Written for Close-up Engineering and now published
 * Author: ManuelOcc
 * Author URI: http://systems.closeupengineering.it/author/manuel-occorso/
 */
 
 // I Need to thank  bamadesigner 'cause I used her plugin to start. See it here => https://github.com/bamadesigner/manage-wordpress-posts-using-bulk-edit-and-quick-edit/blob/master/manage_wordpress_posts_using_bulk_edit_and_quick_edit.php
 
  
 //SETTINGS : Add your field name, column id, column name and the post type in which you have them
function CQEF_get_columns_id(){
	return array( 	'column-meta-1'		, 	'column-meta' 	); 	//Add the comlumn id
}
function CQEF_get_field_id(){
	return array( 	'fiedl-1'			, 	'field' 		);	//Add the field id  
}
function CQEF_get_columns_name(){
	return array( 	'Field 1 Name'		, 	'Field Name' 	); 	//Add the name
}
function CQEF_get_post_type(){
	return array(	'post'				, 	'videos'		); 						//Add post types in which you have the field	
}
	

add_filter( 'manage_posts_columns', 'CQEF_manage_posts_columns', 10, 2 );
function CQEF_manage_posts_columns( $columns, $post_type ) {
	$columns_id_plugin 	= CQEF_get_columns_id();
	$field_id_plugin 	= CQEF_get_field_id();
	$columns_name_plugin 	= CQEF_get_columns_name();	
	$post_type_plugin	= CQEF_get_post_type();
	
	foreach( $post_type_plugin as $ptp) {			
		if ( $post_type == $ptp) {
			for( $i = 0 ; $i < count($columns_id_plugin); $i++ ) {			
				if ( $post_type == $ptp) {
					$columns[ $columns_id_plugin[i]] = $columns_name_plugin[i]; //Matches every column_id with hid column-name and returns it.
				}
			}
		}					
	}		
	return $columns;	
}


add_action( 'manage_posts_custom_column', 'CQEF_manage_posts_custom_column', 10, 2 );
function CQEF_manage_posts_custom_column( $column_name, $post_id ) {

	$columns_id_plugin 	= CQEF_get_columns_id();
	$field_id_plugin 	= CQEF_get_field_id();
	$columns_name_plugin 	= CQEF_get_columns_name();	
	$post_type_plugin	= CQEF_get_post_type();
	
	for( $i = 0 ; $i < count($field_id_plugin); $i++ ) {	//div for every column and every post (named: column_id + post-id)	
			//Set 	style="display: visible;" if you want to see it 
			echo '<div  style="display: none;" id="'.$field_id_plugin[$i].'-' . $post_id . '">' . get_post_meta( $post_id, $field_id_plugin[$i], true ) . '</div>';	 		
	}	
}



//add_action( 'bulk_edit_custom_box', 'CQEF_quick_edit_custom_box', 10, 2 ); //uncomment if you want to si also in Bulk Edit
add_action( 'quick_edit_custom_box', 'CQEF_quick_edit_custom_box', 10, 2 );
function CQEF_quick_edit_custom_box( $column_name, $post_type ) {

	$columns_id_plugin 	= CQEF_get_columns_id();
	$field_id_plugin 	= CQEF_get_field_id();
	$columns_name_plugin 	= CQEF_get_columns_name();	
	$post_type_plugin	= CQEF_get_post_type();
	
	foreach( $post_type_plugin as $ptp) {			
		if ( $post_type == $ptp) {
			$i = 0;
			foreach( $columns_id_plugin as $cip) {
				//if you want a specific type of input (like date) you have to set an if for that column_name and set your own HTML code						
				if ( $column_name == $cip) {
					echo '
					
					<fieldset class="inline-edit-col-left">
						<div class="inline-edit-col">
							<label>
								<span class="title">'.$columns_name_plugin[$i].'</span>
								<span class="input-text-wrap">
									<input type="text" value="" name="'.$field_id_plugin [$i].'"> 
								</span>
							</label>
						</div>
					</fieldset>';
				
				}
				$i++;
			}							
	}			
}			
		
	
}

add_action( 'admin_print_scripts-edit.php', 'CQEF_admin_print_scripts_edit' );
function CQEF_admin_print_scripts_edit() {
	// if code is in functions.php file
	//wp_enqueue_script( 'manage-wp-posts-using-bulk-quick-edit', trailingslashit( get_bloginfo( 'stylesheet_directory' ) ) . 'CQEF_functions.js', array( 'jquery', 'inline-edit-post' ), '',true);
	// if using code as plugin
	wp_enqueue_script( 'manage-wp-posts-using-bulk-quick-edit', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'CQEF_functions.js', array( 'jquery', 'inline-edit-post' ), '', true );
	
}

add_action( 'save_post', 'CQEF_save_post', 10, 2 );
function CQEF_save_post( $post_id, $post ) {

	$columns_id_plugin 	= CQEF_get_columns_id();
	$field_id_plugin 	= CQEF_get_field_id();
	$columns_name_plugin 	= CQEF_get_columns_name();	
	$post_type_plugin	= CQEF_get_post_type();
	
	// pointless if $_POST is empty (this happens on bulk edit)
	if ( empty( $_POST ) )
		return $post_id;
		
	// verify quick edit nonce
	if ( isset( $_POST[ '_inline_edit' ] ) && ! wp_verify_nonce( $_POST[ '_inline_edit' ], 'inlineeditnonce' ) )
		return $post_id;
			
	// don't save for autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;
		
	// don't save for revisions
	if ( isset( $post->post_type ) && $post->post_type == 'revision' )
		return $post_id;
		
	foreach( $post_type_plugin as $ptp) {			
		if ( $post->post_type == $ptp) {	
			$custom_fields = CQEF_get_field_id(); 
			
			foreach( $custom_fields as $field ) {
			
				if ( array_key_exists( $field, $_POST ) ){
					update_post_meta( $post_id, $field, $_POST[ $field ] );
					}
					
			}
				
		}		
	}
	
}

add_action( 'wp_ajax_manage_wp_posts_using_bulk_quick_save_bulk_edit', 'CQEF_wp_ajax_manage_wp_posts_using_bulk_quick_save_bulk_edit' );
function CQEF_wp_ajax_manage_wp_posts_using_bulk_quick_save_bulk_edit() {
	// we need the post IDs
	$post_ids = ( isset( $_POST[ 'post_ids' ] ) && !empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : NULL;
		
	// if we have post IDs
	if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
	
		// get the custom fields
		$custom_fields = CQEF_get_field_id();
		
		foreach( $custom_fields as $field ) {
			
			// if it has a value, doesn't update if empty on bulk
			if ( isset( $_POST[ $field ] ) && !empty( $_POST[ $field ] ) ) {			
				// update for each post ID
				foreach( $post_ids as $post_id ) {
					update_post_meta( $post_id, $field, $_POST[ $field ] );
				}
				
			}
			
		}
		
	}
	
}
?>