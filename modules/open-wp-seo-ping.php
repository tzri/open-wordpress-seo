<?php
/**
 * Module for pinging search engines.
 */
if (!defined('ABSPATH')) return;
 
class OpenWordPressSEOPing {

	const PING_GOOGLE_URL = 'http://www.google.com/webmasters/sitemaps/ping?sitemap=';
	const PING_BING_URL = 'http://www.bing.com/ping?siteMap=';
	const PING_ASK_URL = 'http://submissions.ask.com/ping?sitemap=';
	const PING_MIN_INTERVAL = 3700;
	
	public function ping_search_engines() {
		$this->ping_google();
		$this->ping_bing();
		$this->ping_ask();
	}

	private function ping_google() {
		$this->send_ping('open_wp_seo_ping_google_time', self::PING_GOOGLE_URL);
	}

	private function ping_bing() {
		$this->send_ping('open_wp_seo_ping_bing_time', self::PING_BING_URL);
	}
	
	private function ping_ask() {
		$this->send_ping('open_wp_seo_ping_ask_time', self::PING_ASK_URL);
	}	

	private function send_ping($ping_time_option, $ping_url_base) {
		$last_ping_time = get_option($ping_time_option, 0);
		
		// FIXME is this working correctly?
		if (!is_integer($last_ping_time)) {
			$last_ping_time = 0;
		}
		
		if ($last_ping_time + self::PING_MIN_INTERVAL > time()) {
			return;
		}
		
		$ping_url = $ping_url_base . $this->get_sitemap_url();
		$result_code = wp_remote_retrieve_response_code($this->ping($ping_url));
		
		if ($result_code == '200') {
			update_option($ping_time_option, time());
			return;
		}
		
		update_option($ping_time_option, OpenWordPressSEO::STATUS_ERROR);
	}
	
	private function ping($url) {
		  return wp_remote_get($url);
	}
	
	private function get_sitemap_url() {
		return get_site_url() . '/' . OpenWordPressSEOSitemap::SITEMAP_FILENAME;
	}
}