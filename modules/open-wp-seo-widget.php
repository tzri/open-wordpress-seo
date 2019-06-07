<?php
/**
 * Module for UI.
 */
if (!defined('ABSPATH')) return;

class OpenWordPressSEOWidget {
	
	private $content;
	
	public function __construct() {
		$this->content = new OpenWordPressSEOContent();
	}
	
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'open_wp_seo_widget',
			'Open WordPress SEO',
			array($this, 'print_dashboard_widget')
        );
	}
	
	public function print_dashboard_widget() {
		$indexing_denied = get_option('blog_public') === '0';
		
		echo '<div class="open-wp-seo-dashboard-widget">';
		echo '<table>';

		echo '<tr><td><strong>'.__('Overall SEO score', OpenWordPressSEO::TEXT_DOMAIN).'</strong></td><td><strong>'.$this->get_overall_seo_status_score_text().'</strong></td></tr>';
		
		echo '<tr><td>'.__('Frontpage SEO score', OpenWordPressSEO::TEXT_DOMAIN).'</td><td>'.$this->get_frontpage_seo_status_text().'</td></tr>';
				
		echo '<tr><td>'.__('Articles SEO score', OpenWordPressSEO::TEXT_DOMAIN).'</td><td>'.$this->get_posts_seo_status_text('post').'</td></tr>';
		
		echo '<tr><td>'.__('Pages SEO score', OpenWordPressSEO::TEXT_DOMAIN).'</td><td>'.$this->get_posts_seo_status_text('page').'</td></tr>';
		
		echo '<tr><td>'.__('Sitemap updated', OpenWordPressSEO::TEXT_DOMAIN).'</td><td>'.$this->get_sitemap_status().'</td></tr>';
		
		echo '</table>';
		
		if ($indexing_denied) {
			printf('<table><tr><td class="icon"><span class="dashicons dashicons-warning"></span></td><td>'.__('Search engines are told not to index this site. Change the setting in <a href="%s">Reading</a> > Search Engine Visibility.', OpenWordPressSEO::TEXT_DOMAIN).'</td></tr></table>', get_admin_url().'/options-reading.php');
		}
		
		echo '</div>';
	}
	
	private function get_overall_seo_status_score_text() {
		$score = $this->get_overall_seo_status_score();
		$score_text = __('Low', OpenWordPressSEO::TEXT_DOMAIN) . ' <span class="dashicons dashicons-thumbs-down small-icon"></span>';
		
		if ($score > 0.9) {
			$score_text = __('High', OpenWordPressSEO::TEXT_DOMAIN) . ' <span class="dashicons dashicons-thumbs-up small-icon"></span>';
		}
		else if ($score > 0.6) {
			$score_text = __('OK', OpenWordPressSEO::TEXT_DOMAIN);
		}
		
		$style = $score > 0.6 ? 'open-wp-seo-ok' : 'open-wp-seo-fail';
		
		return "<span class=\"{$style}\">{$score_text}</span>";
	}
	
	private function get_overall_seo_status_score() {
		$robots_score = $this->get_robots_seo_status_score();
		$frontpage_score = $this->get_frontpage_seo_status_score();
		$articles_score = $this->get_articles_seo_status_score();
		$pages_score = $this->get_pages_seo_status_score();
		$sitemap_score = $this->get_sitemap_seo_status_score();
		
		$overall_score = ($robots_score + $frontpage_score + $articles_score + $pages_score + $sitemap_score) / 5;
		return $overall_score;
	}

	private function get_robots_seo_status_score() {
		$indexing_denied = get_option('blog_public') === '0';
		
		if ($indexing_denied) {
			return 0;
		}
		
		return 1;
	}
	
	private function get_frontpage_seo_status_score() {
		$settings_ok = 0;
		
		$option_title = get_option('open_wp_seo_frontpage_title');
		$option_description = get_option('open_wp_seo_frontpage_description');		
		
		if (!empty($option_title)) {
			$settings_ok++;
		}
		
		if (!empty($option_description)) {
			$settings_ok++;
		}
		
		return $settings_ok / 2;
	}
	
	private function get_articles_seo_status_score() {
		$seo_status = $this->get_posts_seo_status('post');
		
		if ($seo_status['post_seoed'] == 0) {
			return 0;
		}
		
		return $seo_status['post_seoed'] / $seo_status['post_total'];
	}
	
	private function get_pages_seo_status_score() {
		$seo_status = $this->get_posts_seo_status('page');
		
		if ($seo_status['post_seoed'] == 0) {
			return 0;
		}
		
		return $seo_status['post_seoed'] / $seo_status['post_total'];
	}
	
	private function get_sitemap_seo_status_score() {
		$sitemap_created_time = get_option('open_wp_seo_sitemap_create_time', FALSE);
		
		if ($sitemap_created_time !== FALSE) {
			return 1;
		}
		
		return 0;
	}
	
	private function get_frontpage_seo_status_text() {
		$settings_count = 2;
		$settings_ok = 0;
		
		$option_title = get_option('open_wp_seo_frontpage_title');
		$option_description = get_option('open_wp_seo_frontpage_description');		
		
		if (!empty($option_title)) {
			$settings_ok++;
		}
		
		if (!empty($option_description)) {
			$settings_ok++;
		}
		
		$seo_status_style = $this->get_seo_success_style($settings_count, $settings_ok);
		return "<span class=\"{$seo_status_style}\">{$settings_ok}/{$settings_count}</span>";
	}
	
	private function get_posts_seo_status_text($post_type) {
		$articles_seo_status = $this->get_posts_seo_status($post_type);
		$seo_status_style = $this->get_seo_success_style($articles_seo_status['post_total'], $articles_seo_status['post_seoed']);
		
		return "<span class=\"{$seo_status_style}\">".sprintf('%s / %s', $articles_seo_status['post_seoed'], $articles_seo_status['post_total']).'</span>';
	}
	
	private function get_posts_seo_status($post_type) {
		$result = array('post_total' => 0, 'post_seoed' => 0);
		
		$post_query_arguments = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => -1
		);
		
		$post_query = new WP_Query($post_query_arguments);
		
		while ($post_query->have_posts()) {
			$post_query->the_post();
			
			if ($this->is_excluded_post_id(get_the_ID())) {
				continue;
			}			
			
			$seo_title = get_post_meta(get_the_ID(), 'open_wp_seo_title', TRUE);
			$seo_description = get_post_meta(get_the_ID(), 'open_wp_seo_description', TRUE);
			$too_few_words = $this->content->is_word_count_too_low(get_post_field('post_content', get_the_ID()));
			
			if (!$too_few_words && !empty($seo_title) && !empty($seo_description)) {
				$result['post_seoed']++;
			}
			
			$result['post_total']++;
		}
		
		return $result;		
	}
	
	private function is_excluded_post_id($id) {
		return in_array($id, explode(',', str_replace(' ', '', get_option('open_wp_seo_exclude_posts'))));
	}
	
	private function get_seo_success_style($total_count, $ok_count) {
		if ($total_count === 0) {
			return 'open-wp-seo-ok';
		}
		
		if ($total_count === $ok_count) {
			return 'open-wp-seo-ok';
		}
		
		return 'open-wp-seo-fail';
	}
	
	private function get_sitemap_status() {
		$settings_url = get_admin_url() . OpenWordPressSEO::ADMIN_SETTINGS_URL;
		
		$sitemap_enabled = get_option('open_wp_seo_sitemap_enabled');
		$sitemap_updated = get_option('open_wp_seo_sitemap_create_success', FALSE);
		$sitemap_created_time = get_option('open_wp_seo_sitemap_create_time', FALSE);
	
		if ($sitemap_enabled !== OpenWordPressSEO::OPTION_ON) {
			return '<span class="open-wp-seo-fail">'. sprintf(__('Not enabled. <a href="%s">Enable here</a>', OpenWordPressSEO::TEXT_DOMAIN), $settings_url) . '</span>';
		}
		
		if ($sitemap_updated === OpenWordPressSEO::STATUS_ERROR) {
			return '<span class="open-wp-seo-fail">' . __('Error', OpenWordPressSEO::TEXT_DOMAIN) . '</span>';
		}
		
		if (($sitemap_updated === OpenWordPressSEO::STATUS_OK || $sitemap_updated === FALSE) && $sitemap_created_time !== FALSE) {
			return '<span class="open-wp-seo-ok">' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $sitemap_created_time) . '</span>';
		}	
	}
	
}