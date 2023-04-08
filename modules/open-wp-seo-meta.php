<?php
/**
 * Module for site meta information.
 */
if (!defined('ABSPATH')) return;
 
class OpenWordPressSEOMeta {
	
	const REMOVE_HEADERS_REGEXP = '#(<h([1-6])[^>]*>)\s?(.*)?\s?(<\/h\2>)#';
	const META_DESCRIPTION_MAX_LENGTH = 160;
	
	public function get_meta_title($title){
		$front_page_title_option = get_option('open_wp_seo_frontpage_title');
		if (is_front_page() && !empty($front_page_title_option)) {
			return $front_page_title_option;
		}	
		
		if (!is_single() && !is_page() && !is_attachment()) {
			return $title;
		}
		
		global $post;
		$post_specific_title = get_post_meta($post->ID, 'open_wp_seo_title', TRUE);
		
		if (!empty($post_specific_title)) {
			return $post_specific_title;
		}
		
		$use_automatic_titles = get_option('open_wp_seo_use_automatic_titles') === OpenWordPressSEO::OPTION_ON;
		
		if (!$use_automatic_titles) {
			return $title;
		}
		
		$type_specific_title = get_option('open_wp_seo_automatic_title_'.$post->post_type);
		
		if (!empty($type_specific_title)) {
			return $this->fill_title_template($type_specific_title, $post);
		}
		
		return $title;
	}

	public function print_meta_description() {
		$description = $this->get_meta_description();
		if (!empty($description)) {
			echo "<meta name=\"description\" content=\"{$description}\" />\n";
		}
	}
	
	private function get_meta_description() {
		return htmlentities(get_meta_description_unencoded());
	}
	
	private function get_meta_description_unencoded() {
		$front_page_description_option = get_option('open_wp_seo_frontpage_description');
		if (is_front_page() && !empty($front_page_description_option)) {
			return $front_page_description_option;
		}
		
		if (!is_single() && !is_page() && !is_attachment()) {
			return '';
		}
		
		global $post;
		$description = get_post_meta($post->ID, 'open_wp_seo_description', TRUE);
		if (!empty($description)) {
			return $description;
		}
		
		$description = get_post_field('post_content', $post->ID);
		$description = apply_filters('the_content', $description);
		$description = preg_replace(self::REMOVE_HEADERS_REGEXP, '', $description);
		$description = wp_strip_all_tags($description, TRUE);
		
		if (strlen($description) > self::META_DESCRIPTION_MAX_LENGTH) {
			$description = substr($description, 0, self::META_DESCRIPTION_MAX_LENGTH) . '...';
		}
		
		return $description;
	}
	
	public function print_meta_opengraph() {
		$url = get_permalink();
		if (is_front_page()) {
			$url = get_site_url();
		}
		$title = strip_tags($this->get_meta_title(get_the_title()));
		$description = strip_tags($this->get_meta_description());
		$image_url = $this->get_post_image_url();
		$facebook_app_id = get_option('open_wp_seo_facebook_app_id', FALSE);
		
		echo '<meta property="og:url" content="'.$url.'" />'.PHP_EOL;
		echo '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
		
		if (!empty($description)) {
			echo '<meta property="og:description" content="'.$description.'"/>'.PHP_EOL;
		}
		
		if (!empty($image_url)) {
			echo '<meta property="og:image" content="'.$image_url.'"/>'.PHP_EOL;
		}
		
		if (!empty($facebook_app_id)) {
			echo '<meta property="fb:app_id" content="'.$facebook_app_id.'"/>'.PHP_EOL;
		}
	}
	
	private function get_post_image_url() {
		if (has_post_thumbnail()) {
			$image_id = get_post_thumbnail_id();
			$image_url = wp_get_attachment_image_src($image_id, 'full');
			return $image_url[0];
		}
		
		global $post;
		if ($post === NULL) {
			return NULL;
		}
		
		$found = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		
		if ($found === 1) {
			return $matches[1][0];
		}
		
		return NULL;
	}
	
	public function print_meta_keywords() {
		global $post;
		
		if (!is_single() && !is_page()) {
			return;
		}

		$tags = get_the_tags($post->ID);
		if (empty($tags)) {
			return;
		}
		
		$keywords = '';
		$first = TRUE;
		foreach ($tags as $tag) {
			if (!$first) {
				$keywords .= ', ';
			}
			$keywords .= $tag->name;
			$first = FALSE;
		}
		if (!empty($keywords)) {
			echo "<meta name=\"keywords\" content=\"{$keywords}\" />\n";
		}
	}
	
	public function print_meta_noindex() {
		global $post;
		$print_noindex = FALSE;		
		
		if (is_tag()) {
			$noindex_for_tags = get_option('open_wp_seo_noindex_for_tags');
			if ($noindex_for_tags === OpenWordPressSEO::OPTION_ON) {
				$print_noindex = TRUE;
			}
		}
		
		if (is_category()) {
			$noindex_for_categories = get_option('open_wp_seo_noindex_for_categories');
			if ($noindex_for_categories === OpenWordPressSEO::OPTION_ON) {
				$print_noindex = TRUE;
			}			
		}
		
		if (is_date() || is_author()) {
			$noindex_for_archives = get_option('open_wp_seo_noindex_for_archives');
			if ($noindex_for_archives === OpenWordPressSEO::OPTION_ON) {
				$print_noindex = TRUE;
			}				
		}
		
		if (is_paged()) {
			$noindex_for_paged_pages = get_option('open_wp_seo_noindex_for_paged');
			if ($noindex_for_paged_pages === OpenWordPressSEO::OPTION_ON) {
				$print_noindex = TRUE;
			}			
		}
			
		if ($post !== NULL && $this->is_excluded_post_id($post->ID)) {
			$print_noindex = TRUE;
		}
		
		if ($print_noindex) {
			echo "<meta name=\"robots\" content=\"noindex\"/>\n";
		}
	}
	
	private function is_excluded_post_id($id) {
		return in_array($id, explode(',', str_replace(' ', '', get_option('open_wp_seo_exclude_posts'))));
	}
	
	public function print_meta_canonical() {
		$canonical_url = '';
		$current_term = get_queried_object();
		
		if (is_tag()) {
			$canonical_url = get_tag_link($current_term->term_id);
		}
		else if (is_category()) {
			$canonical_url = get_category_link($current_term->term_id);
		}
		else if (is_front_page()) {
			$canonical_url = get_site_url();
		}
		else if (is_single()) {
			$canonical_url = get_permalink();

		}
		
		if (!empty($canonical_url)) {
			echo "<link rel=\"canonical\" href=\"{$canonical_url}\" />\n";
		}
	}
	
	public function print_script_version() {
		echo "<!-- Open WordPress SEO " . OpenWordPressSEO::VERSION . " -->\n";
	}
	
	public function print_google_analytics_script() {
		$tracking_code = get_option('open_wp_seo_google_analytics_code', FALSE);
		
		if (empty($tracking_code)) {
			return;
		}
		
		echo str_replace("\t", '', '
			<!-- Global Site Tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id='.$tracking_code.'"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments)};
			  gtag(\'js\', new Date());

			  gtag(\'config\', \''.$tracking_code.'\');
			</script>
			');
	}
	
	public function get_type_specific_title($post) {
		$use_automatic_titles = get_option('open_wp_seo_use_automatic_titles') === OpenWordPressSEO::OPTION_ON;
		
		if (!$use_automatic_titles) {
			return NULL;
		}
		
		$type_specific_title = get_option('open_wp_seo_automatic_title_'.$post->post_type);
		
		if (!empty($type_specific_title)) {
			return $this->fill_title_template($type_specific_title, $post);
		}
		
		return NULL;
	}
	
	private function fill_title_template($title_template, $post) {
		$article_name = $post->post_title;
		$site_name = get_bloginfo();
		$category_name = '';
		$categories = get_the_category($post->ID);
		if (!empty($categories)) {
			$category_name = $categories[0]->cat_name;
		}
		$author_name = get_author_name($post->post_author);
		$article_date = get_the_date();
		
		$title = str_replace('%article_name%', $article_name, $title_template);
		$title = str_replace('%site_name%', $site_name, $title);
		$title = str_replace('%category_name%', $category_name, $title);
		$title = str_replace('%author_name%', $author_name, $title);
		$title = str_replace('%article_date%', $article_date, $title);
		
		return $title;
	}
}