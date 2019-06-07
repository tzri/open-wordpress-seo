/**
 * Poor Man's WordPress SEO JavaScript functionality
 */
jQuery(document).ready(function($) {
    var $titleInput = $('#pm_wp_seo_title'),
		$descriptionArea = $('#pm_wp_seo_description');
		
	$titleInput.keyup(function () {
		$('.pm-wp-seo-preview-title').text($titleInput.val());
	});
	
	$descriptionArea.keyup(function () {
		$('.pm-wp-seo-preview-description').text($descriptionArea.val());
	});
	
	if (window.location.href.indexOf('tab=automatic-titles') !== -1) {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.automatic-titles-tab-button').addClass('nav-tab-active');
		$('.pm-wp-seo-settings-tab').hide();
		$('#automatic-titles').show();
	}
	else if (window.location.href.indexOf('tab=sitemap') !== -1) {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.sitemaps-tab-button').addClass('nav-tab-active');
		$('.pm-wp-seo-settings-tab').hide();
		$('#sitemaps').show();
	}
	else if (window.location.href.indexOf('tab=advanced') !== -1) {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.advanced-tab-button').addClass('nav-tab-active');
		$('.pm-wp-seo-settings-tab').hide();
		$('#advanced').show();
	}
	else {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.main-settings-tab-button').addClass('nav-tab-active');
		$('.pm-wp-seo-settings-tab').hide();
		$('#main-settings').show();		
	}
	
	$('.pm-wp-seo-settings-wrap').show();
	
	console.log('Poor Man\'s WordPress SEO loaded.');
});