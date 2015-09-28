/*
jQuery(document).ready(function($) {
	
	$('.js-required').hide();
	
	if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)){  //The Issuer API isn't supported on MSIE Bbrowsers
		$('.acceptclick').hide();
		$('.browserSupport').show();
	}else{
		$('.browserSupport').hide();
	}
	
	//Function that issues the badge
	
	$('.acceptclick').click(function() {		
	var assertionUrl = BSP_Awards.assertion_url;
	//var assertionUrl= '<?php echo $path_json= base64_decode(str_rot13($path_json)); ?>';
	//<?php print $issuer_url; ?>/badge-it-gadget-lite/digital-badges/issued/json/<?php print $_GET[id]; ?>.json";
       OpenBadges.issue([''+assertionUrl+''], function(errors, successes) { 
				//	alert(errors.toSource()) 
				//	alert(successes.toSource()) 
					if (errors.length > 0 ) {
						/*$('#errMsg').text('Error Message: '+ errors.toSource());*
						$('#badge-error').show();	
						
						//var data = 'ERROR, <?php echo $badge_name?>, <?php echo $recipient_name; ?>, ' +  errors.toSource();
						$.ajax({
    					url: 'http://demo.rutwick.com/badge/',
    					type: 'POST',
    					data: { 
							action:'award_action'
							}
						});
					}
					
					if (successes.length > 0) {
							$('.acceptclick').hide();
							$('#badgeSuccess').show();
							//var data = 'SUCCESS, <?php echo $badges_array[$badgeId]['name']; ?>, <?php echo $recipient_name; ?>';
							$.ajax({
    						url: 'http://demo.rutwick.com/badge/',
    						type: 'POST',
    						data: { 
								action:'award_action'
								}
							});
						}	
					});    
				});
	});
*/
