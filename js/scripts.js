jQuery(document).ready(function () {
  //functionality for showing the description of taxonomies
  jQuery('#bsp_success').hide();
  jQuery(".bsp_desc").hide();
  jQuery("#select_skills").hide();
  jQuery("#bsp_lvl_badges").hide();
  jQuery("#bsp_lang_badges").val(jQuery("bsp_lang_badges option:first").val()); //so that no option is selected
  jQuery("#bsp_skill_badges").val(jQuery("bsp_skill_badges option:first").val());
  jQuery("#bsp_lvl_badges").val(jQuery("bsp_lvl_badges option:first").val());
  
  var id;
  //on click function to get the ids for skills description
  jQuery(".bsp_skills" ).on("click", function (e){
    var curr_id = jQuery(this).attr('id').split('-')[1];

    //if the id was clicked already then don't do anything
    if(curr_id === id) {
      return;
    }
    
    //Update id
    id = curr_id;
    
    //showing and hiding the skill description depending on what skill is clicked
    if(jQuery('#desc-'+id).is(':visible')){
      jQuery('#desc-'+id).hide('slow');

    }else{
      jQuery('.bsp_desc').hide();
      jQuery('#desc-'+id).show('slow');

    }
        //showing the image for selected skill
         jQuery.post(
         ajaxurl, {
           action: 'bsp_select_cpt', 
           skill: id 
           }, 
           function(response) {
             //Remove the current images
             jQuery('#bsp_images').empty();
				var parsed = JSON.parse(response);
				if(parsed !== -1) {
				var list = jQuery('<ul/>');
				jQuery.each(parsed, function(k, v) {
              
					list.append('<li><img src="'+v.image+'" alt="'+v.badge+'" class="input_hidden" id="badge-'+v.badge+'" /></li>');
            
				});
				jQuery('#bsp_images').append(list);
				} else {
					jQuery('#bsp_images').append('<span>No badge for this skill.</span>');
				}
          }); 
});
    //getting the id of a badge and selecting it and putting a red frame around selected image
	var badge_id;
   jQuery('#bsp_images').on('click', '.input_hidden', function(e) {
      e.preventDefault();
      jQuery('.input_hidden').css('border', 0);
      badge_id = jQuery(this).attr('id').split('-')[1];
      jQuery(this).css({ 'border': '1px solid red' });
    });

  //getting the languages and hiding the skills and then showing them
  jQuery('#bsp_lang_badges').on('change', function(e) {
    jQuery('.bsp_desc').hide();
    var language=document.getElementById("bsp_lang_badges").value;
    jQuery("#bsp_lvl_badges").show();
    
  });
  
  //getting the languages and hiding the skills and then showing them
  jQuery('#bsp_lvl_badges').on('change', function(e) {
    jQuery('.bsp_desc').hide();
    var language=document.getElementById("bsp_lang_badges").value;
    jQuery("#select_skills").show();
    
  });


      
  //button click function for sending the email 
  jQuery('#bsp_send').on('click', function(e) {
    e.preventDefault();
    var email = jQuery('#bsp_email').val();
    var language = jQuery('#bsp_lang_badges').val();
    console.log(email);
    console.log(badge_id);
    jQuery.post(ajaxurl, 
    {
      action: 'bsp_send_email', 
      data: 
      { 
        email_add: email, 
        badge: badge_id, 
        lang: language
      }
    }, 
      function(response) {
      jQuery('#bsp_success').show();
      jQuery('.bsp_desc').remove();
      jQuery('#select_skills').remove();
      jQuery('#bsp_lvl_badges').remove();
      jQuery('#bsp_lang_badges').remove();
      jQuery('#bsp_skill_badges').remove();
      jQuery('#bsp_lvl_badges').remove();
      jQuery('.bsp_skills').remove();
      jQuery('#bsp_send').remove();
      jQuery('#bsp_images').remove();
      jQuery('#bsp_email').remove();
      
      console.log(response);
    });    
    
    return false;
  });
});
