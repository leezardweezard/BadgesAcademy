/*
 * Funcionalities are basing of wp-csv-to-database plugin
 * 
 * */
jQuery(document).ready(function($) {

	var formfield;




	$("#add_meta_button").on("click",(function () {
		alert('Its alive!');
	}));


	// Function for blur event on file upload field (used for input field AND upload button)
	function blur_file_upload_field() {
		
		file_upload_url = $('#csv_file').val();
		extension = file_upload_url.substr((file_upload_url.lastIndexOf('.') +1));
		
		// If the file upload does not contain a valid .csv file extension
		if(extension !== 'csv') {
			
			// File extension .csv popup error
			$( "#dialog_csv_file" ).dialog({
			  modal: true,
			  buttons: {
				Ok: function() {
				  $( this ).dialog( "close" );
				}
			  }
			});
			$('#return_csv_col_count').text('0');
			return;
		}
		
		// Setup ajax variable
		var data = {
			action: 'bsp_get_csv_cols',
			file_upload_url: file_upload_url
		};
		
		// Run ajax request
		$.post(ajaxurl, data, function(response) {
			$('#return_csv_col_count').text(response.column_count);
			$('#num_cols_csv_file').val(response.column_count);
		});
	}

	// Click "Table Preview" button each time page is loaded
	$('#repop_table_ajax').trigger('click');
	
	// Disable 'disable auto-increment' button until needed
	//$('#remove_autoinc_column').prop('disabled', true); 
	
	// Click to hide error/success messages
	$('.error_message, .success_message, .info_message_dismiss').click(function() {  
		 $( this ).fadeOut( "slow", function() {
		});
	}); 
	
	//Media upload window (media library)
	var file_frame;
	$('#csv_file_button').on('click', function(event){
		event.preventDefault();
		if ( file_frame ) {
			file_frame.open();
      return;
    }
    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
      title: 'CSV file upload',
      button: {
        text: $( this ).data( 'csv_file_button' ),
      },
     /* library: {
		  type:'file'
	  },*/
      multiple: false  // Set to true to allow multiple files to be selected
    });
 
    // When an image is selected, run a callback.
    file_frame.on( 'select', function() {
      // We set multiple to false so only get one image from the uploader
      attachment = file_frame.state().get('selection').first().toJSON();

		$('#csv_file').attr('value',attachment.url);
    });
 
    // Finally, open the modal
    file_frame.open();
  });
	
	// ******* Begin 'Select Table' dropdown change function ******* //
	$('#table_select').change(function() {  // Get column count and load table
		
		// Begin ajax loading image
		$('#table_preview').html('<img src="'+bsp_pass_js_vars.ajax_image+'" />');
		
		// Clear 'disable auto_inc' checkbox
		$('#remove_autoinc_column').prop('checked', false);
		
		// Get new table name from dropdown
		sel_val = $('#table_select').val();
		
		// Setup ajax variable
		var data = {
			action: 'bsp_get_columns',
			sel_val: sel_val
			//disable_autoinc: disable_autoinc
		};
		
		// Run ajax request
		$.post(bsp_pass_js_vars.ajaxurl, data, function(response) {
			
			// Populate Table Preview HTML from response
			$('#table_preview').html(response.content);
			
			// Determine if column has an auto_inc value.. and enable/disable the checkbox accordingly
			if(response.enable_auto_inc_option == 'true') {
				$("#remove_autoinc_column").prop('disabled', false);
			}
			if(response.enable_auto_inc_option == 'false') {
				$("#remove_autoinc_column").prop('disabled', true);
			}
			
			
			// Get column count from ajax table and populate hidden div for form submission comparison
			var colCount = 0;
			$('#ajax_table tr:nth-child(1) td').each(function () {  // Array of table td elements
				if ($(this).attr('colspan')) {  // If the td element contains a 'colspan' attribute
					colCount += +$(this).attr('colspan');  // Count the 'colspan' attributes
				} else {
					colCount++;  // Else count single columns
				}
			});
			
			// Populate #num_cols hidden input with number of columns
			$('#num_cols').val(colCount);  
		});
	});
	// ******* End 'Select Table' dropdown change function ******* //
	
	
	
	// ******* Begin 'Reload Table Preview' button AND 'Disable auto-increment Column' checkbox click function ******* //
	$('#repop_table_ajax, #remove_autoinc_column').click(function() {  // Reload Table
	
		// Begin ajax loading image
		$('#table_preview').html('<img src="'+bsp_pass_js_vars.ajax_image+'" />');
	
		// Get value of disable auto-increment column checkbox
		if($('#remove_autoinc_column').is(':checked')){
			disable_autoinc = 'true';
		}else{
			disable_autoinc = 'false';
		}
		// Get new table name from dropdown
		sel_val = $('#table_select').val();
		
		// Setup ajax variable
		var data = {
			action: 'bsp_get_columns',
			sel_val: sel_val,
			disable_autoinc: disable_autoinc
		};
		
		// Run ajax request
		$.post(bsp_pass_js_vars.ajaxurl, data, function(response) {
			
			// Populate Table Preview HTML from response
			$('#table_preview').html(response.content);
			
			// Determine if column has an auto_inc value and enable/disable the checkbox accordingly
			if(response.enable_auto_inc_option == 'true') {
				$("#remove_autoinc_column").prop('disabled', false);
			}
			if(response.enable_auto_inc_option == 'false') {
				$("#remove_autoinc_column").prop('disabled', true);
			}
			
			// Get column count from ajax table and populate hidden div for form submission comparison
			var colCount = 0;
			$('#ajax_table tr:nth-child(1) td').each(function () {  // Array of table td elements
				if ($(this).attr('colspan')) {  // If the td element contains a 'colspan' attribute
					colCount += +$(this).attr('colspan');  // Count the 'colspan' attributes
				} else {
					colCount++;  // Else count single columns
				}
			});
			
			// Populate #num_cols hidden input with number of columns
			$('#num_cols').val(colCount);
			
			// Re-populate column count value
			remove_auto_col_val = $('#column_count').html('<strong>'+colCount+'</strong>');
		});
	});
	// ******* End 'Reload Table Preview' button AND 'Disable auto-increment Column' checkbox click function ******* //
	
});
