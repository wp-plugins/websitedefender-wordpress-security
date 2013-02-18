<?php
wp_enqueue_script('jquery');
	//echo 'hello'; return;
?>
<div class="wrap" id="wsdplugin_blog_container"></div>
<script type="text/javascript">
jQuery(function($){
	$('#wsdplugin_blog_container').append(
		$(document.createElement('iframe'))
			.css({
			'border': 'none', 
			'display': 'block',
			'width': '100%',
			'min-width': '1050px', 
			'height': '2840px',
			'overflow': 'hidden'
		})
		.attr('src', 'http://www.websitedefender.com/websitedefender-features/')
	);
});
</script>
