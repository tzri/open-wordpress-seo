/**
 * Open WordPress SEO JavaScript functionality
 */
jQuery(document).ready(function($) {
    var $titleInput = $('#open_wp_seo_title'),
		$descriptionArea = $('#open_wp_seo_description');
		
	$titleInput.keyup(function () {
		$('.open-wp-seo-preview-title').text($titleInput.val());
	});
	
	$descriptionArea.keyup(function () {
		$('.open-wp-seo-preview-description').text($descriptionArea.val());
	});
	
	if (window.location.href.indexOf('tab=automatic-titles') !== -1) {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.automatic-titles-tab-button').addClass('nav-tab-active');
		$('.open-wp-seo-settings-tab').hide();
		$('#automatic-titles').show();
	}
	else if (window.location.href.indexOf('tab=sitemap') !== -1) {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.sitemaps-tab-button').addClass('nav-tab-active');
		$('.open-wp-seo-settings-tab').hide();
		$('#sitemaps').show();
	}
	else if (window.location.href.indexOf('tab=advanced') !== -1) {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.advanced-tab-button').addClass('nav-tab-active');
		$('.open-wp-seo-settings-tab').hide();
		$('#advanced').show();
	}
	else {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.main-settings-tab-button').addClass('nav-tab-active');
		$('.open-wp-seo-settings-tab').hide();
		$('#main-settings').show();		
	}
	
	$('.open-wp-seo-settings-wrap').show();
	
	console.log('Open WordPress SEO loaded.');
});