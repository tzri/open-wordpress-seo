<?php
/**
 * Module for creating an image sitemap.
 */
if (!defined('ABSPATH')) return;

require_once(ABSPATH . 'wp-admin/includes/file.php');

class OpenWordPressSEOImageSitemap {

	const IMAGE_SITEMAP_FILENAME = 'image-sitemap.xml';

	public function create_sitemap() {
		$xml_data = $this->generate_sitemap_xml();
		$filename = get_home_path() . self::IMAGE_SITEMAP_FILENAME;
		return file_put_contents($filename, $xml_data);
	}
	
	private function generate_sitemap_xml() {
		$sitemap_xml = new SimpleXMLElement("<urlset></urlset>");
		$sitemap_xml->addAttribute('xmlns', 'http://www.google.com/schemas/sitemap-image/1.1');
		
		$image_query_arguments = array(
			'post_type' => 'attachment',
			'post_mime_type' =>'image',
			'post_status' => 'inherit',			
			'posts_per_page' => -1
		);
		$image_query = new WP_Query($image_query_arguments);
		foreach ($image_query->posts as $image) {
			$url = $sitemap_xml->addChild('image:image');
			$url->addChild('image:loc', wp_get_attachment_url($image->ID));
			$url->addChild('image:caption', $image->post_excerpt);
			$url->addChild('image:title', $image->post_title);
		}
		wp_reset_postdata();
		
		return $sitemap_xml->asXML();
	}
}