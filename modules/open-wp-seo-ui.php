<?php
/**
 * Module for UI.
 */
if (!defined('ABSPATH')) return;

class OpenWordPressSEOUi {
	
	private $content;
	private $meta;
	
	public function __construct() {
		$this->content = new OpenWordPressSEOContent();
		$this->meta = new OpenWordPressSEOMeta();
	}
	
	public function add_post_metaboxes() {
		foreach (get_post_types(NULL, 'names') as $post_type) {
			add_meta_box('open_wp_seo', '<span class="dashicons dashicons-admin-generic open-wp-seo-animated"></span> Open WordPress SEO', array($this, 'create_metabox_main'), $post_type, 'normal', 'high');
			add_meta_box('open_wp_seo_serp_preview', '<span class="dashicons dashicons-visibility open-wp-seo-animated"></span> '. __('SERP Preview', OpenWordPressSEO::TEXT_DOMAIN), array($this, 'create_metabox_serp_preview'), $post_type, 'normal', 'high');
		}
	}

	public function create_metabox_main($post){
		?>
		<div class="open-wp-seo-settings">
		
		<p><strong><?php _e('Title', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
		<input type="text" maxlength="60" name="open_wp_seo_title" id="open_wp_seo_title" value="<?php echo get_post_meta($post->ID, 'open_wp_seo_title', TRUE); ?>" /><br />
		
		<p><strong><?php _e('Meta Description', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
		<textarea maxlength="160" id="open_wp_seo_description" name="open_wp_seo_description"><?php echo get_post_meta($post->ID, 'open_wp_seo_description', TRUE); ?></textarea>
		
		<?php if ($this->content->is_word_count_too_low($post->post_content)) : ?>				
		<p><?php echo sprintf(__('<span class="dashicons dashicons-warning open-wp-seo-fail"></span> The article word count is too low. A minimum of %s words is recommended.', OpenWordPressSEO::TEXT_DOMAIN), OpenWordPressSEOContent::RECOMMENDED_MINIMUM_POST_WORD_COUNT); ?></p>
		<?php endif; ?>
		
		<?php if (!$this->content->does_all_title_words_appear_in_content($this->meta->get_meta_title($post->post_title), $post->post_content)) : ?>				
		<p><?php _e('<span class="dashicons dashicons-warning open-wp-seo-fail"></span> Not all the words in the title seem to appear in content. Consider adding them.', OpenWordPressSEO::TEXT_DOMAIN); ?></p>
		<?php endif; ?>	

		<?php if (!$this->content->content_contains_hyperlinks($post->post_content)) : ?>
		<p><?php _e('<span class="dashicons dashicons-warning open-wp-seo-fail"></span> The content does not contain any hyperlinks. Consider adding them. ', OpenWordPressSEO::TEXT_DOMAIN); ?></p>

		<?php endif; ?>			
		
		</div>
		<?php 
	}
	
	public function create_metabox_serp_preview($post){
		?>
		<div class="open-wp-seo-settings">

		<div class="open-wp-seo-serp-preview">
			<div class="open-wp-seo-preview-title">
			<?php 
				$title = get_post_meta($post->ID, 'open_wp_seo_title', TRUE); 
						
				if (empty($title)) {
					$title = $this->meta->get_type_specific_title($post);
				}
				
				if (empty($title)) {
					$title = get_the_title($post->ID);
				}
				
				if (mb_strlen($title) > 60) {
					$title = mb_substr($title, 0, 60);
				}
				
				echo $title;
			?>
			</div>
			<div class="open-wp-seo-preview-address">
			<?php echo get_permalink($post->ID); ?>
			</div>
			<div class="open-wp-seo-preview-description">
			<?php 
				$description = get_post_meta($post->ID, 'open_wp_seo_description', TRUE); 
				if (empty($description)) {
					$description = __('No description set.', OpenWordPressSEO::TEXT_DOMAIN);
				}
				
				if (mb_strlen($description) > 160) {
					$description = mb_substr($description, 0, 160) . ' ...';
				}
				
				echo $description;
			?>
			</div>
		</div>
		
		</div>
		<?php 
	}	
	
	public function seo_column_head($columns) {
		$columns['seo_status'] = __('SEO', OpenWordPressSEO::TEXT_DOMAIN);
		return $columns;
	}
	
	public function seo_column_content($column_name, $post_id) {
		if ($column_name === 'seo_status') {
			$seo_title = get_post_meta($post_id, 'open_wp_seo_title', TRUE);
			$seo_description = get_post_meta($post_id, 'open_wp_seo_description', TRUE);
			$post = get_post($post_id);
			
			$too_few_words = $this->content->is_word_count_too_low($post->post_content);
			$hyperlinks_exist = $this->content->content_contains_hyperlinks($post->post_content);
			
			if (!$too_few_words && $hyperlinks_exist && !empty($seo_title) && !empty($seo_description)) {
				echo '<span title="'.__('SEO for this item is in good condition.', OpenWordPressSEO::TEXT_DOMAIN).'" class="open-wp-seo-table-icon open-wp-seo-ok">&#10003;</span>';
			}
			else {
				echo '<span title="'.__('SEO for this item needs some work.', OpenWordPressSEO::TEXT_DOMAIN).'" class="open-wp-seo-table-icon open-wp-seo-fail">&#10007;</span>';
			}
		}
	}	
}
