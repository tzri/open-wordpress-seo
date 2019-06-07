<?php
/**
 * Module for creating sitemap.
 */
if (!defined('ABSPATH')) return;

require_once(ABSPATH . 'wp-admin/includes/file.php');

class OpenWordPressSEOSitemap {
	
	const SITEMAP_FILENAME = 'sitemap.xml';
	const PAGE_PRIORITY_HIGH = 0.8;
	const PAGE_PRIORITY_MEDIUM = 0.7;
	const PAGE_PRIORITY_LOW = 0.4;
	const PAGE_PRIORITY_DEFAULT = 0.5;
	
	private $imageSitemap;
	private $pinger;
	
	public function __construct() {
		$this->imageSitemap = new OpenWordPressSEOImageSitemap();
		$this->pinger = new OpenWordPressSEOPing();
	}
	
	public function create_sitemap_from_control_panel() {
		$sitemap_enabled = get_option('open_wp_seo_sitemap_enabled') === OpenWordPressSEO::OPTION_ON;
		
		if (!$sitemap_enabled) {
			update_option('open_wp_seo_sitemap_create_success', 'not_enabled');
			$this->redirect_to_settings_page();
		}
		
		$sitemap_success = $this->create_sitemap();
		$image_sitemap_success = $this->imageSitemap->create_sitemap();
		
		if ($sitemap_success) {
			update_option('open_wp_seo_sitemap_create_time', time());
			update_option('open_wp_seo_sitemap_create_success', OpenWordPressSEO::STATUS_OK);
			$this->pinger->ping_search_engines();
		}
		else {
			update_option('open_wp_seo_sitemap_create_success', OpenWordPressSEO::STATUS_ERROR);
		}
		
		if ($image_sitemap_success) {
			update_option('open_wp_seo_image_sitemap_create_time', time());
			update_option('open_wp_seo_image_sitemap_create_success', OpenWordPressSEO::STATUS_OK);
		}
		else {
			update_option('open_wp_seo_image_sitemap_create_success', OpenWordPressSEO::STATUS_ERROR);
		}
		
		$this->redirect_to_settings_page();
	}
	
	public function create_sitemap_from_save() {
		$sitemap_enabled = get_option('open_wp_seo_sitemap_enabled') === OpenWordPressSEO::OPTION_ON;
		if ($sitemap_enabled) {			
			$this->imageSitemap->create_sitemap();
			$success = $this->create_sitemap();
			if ($success) {
				$this->pinger->ping_search_engines();
			}
		}
	}
	
	private function create_sitemap() {
		$xml_data = $this->generate_sitemap_xml();
		$filename = get_home_path() . self::SITEMAP_FILENAME;
		return file_put_contents($filename, $xml_data);
	}
	
	private function generate_sitemap_xml() {
		$option_priorities = get_option('open_wp_seo_sitemap_prioritities');
		$option_include_lastmod = get_option('open_wp_seo_sitemap_include_lastmod') === OpenWordPressSEO::OPTION_ON;
		$option_include_tags = get_option('open_wp_seo_sitemap_include_tags') === OpenWordPressSEO::OPTION_ON;
		$option_include_categories = get_option('open_wp_seo_sitemap_include_categories') === OpenWordPressSEO::OPTION_ON;
		
		$sitemap_xml = new SimpleXMLElement("<urlset></urlset>");
		$sitemap_xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		
		$post_query_arguments = array(
			'post_type' => array('post', 'page'),
			'post_status' => 'publish',
			'posts_per_page' => -1
		);
		$post_query = new WP_Query($post_query_arguments);
		while ($post_query->have_posts()) {
			$post_query->the_post();
			
			if ($this->is_excluded_post_id(get_the_ID())) {
				continue;
			}			
			
			$url = $sitemap_xml->addChild('url');
			$url->addChild('loc', get_permalink());
			if ($option_include_lastmod) {
					$url->addChild('lastmod', the_modified_date('Y-m-d', '', '', FALSE));
			}
			$url->addChild('priority', $this->get_post_type_priority($option_priorities, get_post_type()));
		}
		wp_reset_postdata();
		
		$this->add_front_page_to_sitemap($sitemap_xml, $option_include_lastmod);

		if ($option_include_tags) {
			$this->add_tags_to_sitemap($sitemap_xml, $option_priorities['other']);
		}
		
		if ($option_include_categories) {
			$this->add_categories_to_sitemap($sitemap_xml, $option_priorities['other']);
		}
		
		return $sitemap_xml->asXML();
	}
	
	private function is_excluded_post_id($id) {
		return in_array($id, explode(',', str_replace(' ', '', get_option('open_wp_seo_exclude_posts'))));
	}
	
	private function get_post_type_priority($priorities, $post_type) {
		if (array_key_exists($post_type, $priorities)) {
			return $priorities[$post_type];
		}
		
		return self::PAGE_PRIORITY_DEFAULT;
	}
	
	private function add_front_page_to_sitemap($sitemap_xml, $include_last_mod) {
		$url = $sitemap_xml->addChild('url');
		$url->addChild('loc', get_site_url().'/');
		if ($include_last_mod) {
			$url->addChild('lastmod', date('Y-m-d'));
		}
		$url->addChild('priority', '1');
	}
	
	private function add_tags_to_sitemap($sitemap_xml, $priority) {
		$tags = get_tags();
		foreach ($tags as $tag) {
			$url = $sitemap_xml->addChild('url');
			$url->addChild('loc', get_tag_link($tag->term_id));
			$url->addChild('priority', $priority);
		}		
	}
	
	private function add_categories_to_sitemap($sitemap_xml, $priority) {		
		$categories = get_categories();
		foreach ($categories as $category) {
			$url = $sitemap_xml->addChild('url');
			$url->addChild('loc', get_category_link($category->term_id));
			$url->addChild('priority', $priority);
		}
	}
	
	private function redirect_to_settings_page() {
		header('Location: ' . get_admin_url() . OpenWordPressSEO::ADMIN_SETTINGS_URL);
		exit();
	}
}