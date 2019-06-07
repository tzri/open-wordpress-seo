<?php
/**
 * Uninstallation of Open WordPress SEO plugin
 */
require_once(__DIR__.'/modules/open-wp-seo-compression.php');

if (!defined('WP_UNINSTALL_PLUGIN')) die();

$posts = get_posts('numberposts=-1&post_type=post&post_status=any');

foreach ($posts as $post) {
	delete_post_meta($post->ID, 'open_wp_seo_title');
	delete_post_meta($post->ID, 'open_wp_seo_description');
}

delete_option('open_wp_seo_frontpage_title');
delete_option('open_wp_seo_frontpage_description');
delete_option('open_wp_seo_sitemap_enabled');
delete_option('open_wp_seo_noindex_for_tags');
delete_option('open_wp_seo_noindex_for_categories');
delete_option('open_wp_seo_sitemap_include_lastmod');
delete_option('open_wp_seo_sitemap_prioritities');
delete_option('open_wp_seo_sitemap_include_categories');
delete_option('open_wp_seo_sitemap_include_tags');
delete_option('open_wp_seo_google_analytics_code');
delete_option('open_wp_seo_sitemap_create_time');
delete_option('open_wp_seo_sitemap_create_success');
delete_option('open_wp_seo_image_sitemap_create_time');
delete_option('open_wp_seo_image_sitemap_create_success');

$compression = new OpenWordPressSEOCompression();
$compression->remove_gzip_compression_from_htaccess();
