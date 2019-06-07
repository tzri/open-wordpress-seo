<?php

/**
 * Module for settings UI.
 */
if (!defined('ABSPATH')) return;

class OpenWordPressSEOUiSettings {

	public function print_settings_page() {
		if (!current_user_can('manage_options')) {
			return;
		}		
		?>
		
		<div class="wrap pm-wp-seo-settings-wrap" style="display: none">
		
		<h1><?= esc_html_e('Poor Man\'s WordPress SEO Settings', OpenWordPressSEO::TEXT_DOMAIN); ?></h1>
					
		<h2 class="nav-tab-wrapper">
			<a href="options-general.php?page=pm-wp-seo&tab=main-settings" class="nav-tab pm-wp-seo-navtab nav-tab-active main-settings-tab-button"><span class="dashicons dashicons-star-filled"></span> <?php _e('Main Settings', OpenWordPressSEO::TEXT_DOMAIN); ?></a>
			<a href="options-general.php?page=pm-wp-seo&tab=automatic-titles" class="nav-tab pm-wp-seo-navtab automatic-titles-tab-button"><span class="dashicons dashicons-admin-settings"></span> <?php _e('Automatic Titles', OpenWordPressSEO::TEXT_DOMAIN); ?></a>
			<a href="options-general.php?page=pm-wp-seo&tab=sitemaps" class="nav-tab pm-wp-seo-navtab sitemaps-tab-button"><span class="dashicons dashicons-networking"></span> <?php _e('Sitemap', OpenWordPressSEO::TEXT_DOMAIN); ?></a>
			<a href="options-general.php?page=pm-wp-seo&tab=advanced" class="nav-tab pm-wp-seo-navtab advanced-tab-button"><span class="dashicons dashicons-welcome-learn-more"></span> <?php _e('Advanced', OpenWordPressSEO::TEXT_DOMAIN); ?></a>
		</h2>
		
		<div class="pm-wp-seo-settings">
		
			<?php $this->print_notifications(); ?>	

			<div class="pm-wp-seo-settings-tab" id="main-settings" style="display: none">
			
				<h2><span class="dashicons dashicons-admin-users"></span> <?php _e('Actions', OpenWordPressSEO::TEXT_DOMAIN); ?></h2>
				<form action="admin-ajax.php" method="post">
					<input type="hidden" name="create-sitemap" value="yes"/>
					<input type="hidden" name="action" value="open_wp_seo_sitemap_create"/>
					<input type="submit" name="submit" value="<?php _e('Create sitemap now', OpenWordPressSEO::TEXT_DOMAIN); ?>"/>
				</form>			
				
				<form action="admin-ajax.php" method="post">
					<input type="hidden" name="create-sitemap" value="yes"/>
					<input type="hidden" name="action" value="open_wp_seo_toggle_gzip"/>
					<?php
						$compression_enabled = get_option('open_wp_seo_gzip_compression') !== OpenWordPressSEO::OPTION_ON;
						
						if ($compression_enabled) : 
					?>				
					<input type="submit" name="submit" value="<?php _e('Enable Gzip compression', OpenWordPressSEO::TEXT_DOMAIN) ?>"/>
					<?php else : ?>
					<input type="submit" name="submit" value="<?php _e('Disable Gzip compression', OpenWordPressSEO::TEXT_DOMAIN) ?>"/>
					<?php endif; ?>
				</form>
			
				<form action="options.php" method="post">
					<?php
						submit_button(__('Save settings', OpenWordPressSEO::TEXT_DOMAIN));
						settings_fields('pm-wp-seo');
						do_settings_sections('pm-wp-seo');
						submit_button(__('Save settings', OpenWordPressSEO::TEXT_DOMAIN));
					?>
				</form>
				
			</div>
			
			<div class="pm-wp-seo-settings-tab" id="automatic-titles" style="display: none">
				<form action="options.php" method="post">
					<?php
						submit_button(__('Save settings', OpenWordPressSEO::TEXT_DOMAIN));
						settings_fields('pm-wp-seo-automatic-titles');
						echo '<table class="form-table">';		
						$this->print_automatic_titles_options_header();
						$this->print_automatic_title_instructions();
						$this->print_option_use_automatic_titles();
						$this->print_automatic_title_setting_fields();
						echo '</table>';
						submit_button(__('Save settings', OpenWordPressSEO::TEXT_DOMAIN));
					?>
				</form>
			</div>
			
			<div class="pm-wp-seo-settings-tab" id="sitemaps" style="display: none">
				<form action="options.php" method="post">
					<?php
						submit_button(__('Save settings', OpenWordPressSEO::TEXT_DOMAIN));
						settings_fields('pm-wp-seo-sitemap');
						do_settings_sections('pm-wp-seo-sitemap');
						submit_button(__('Save settings', OpenWordPressSEO::TEXT_DOMAIN));
					?>
				</form>
			</div>

			<div class="pm-wp-seo-settings-tab" id="advanced" style="display: none">
				<form action="options.php" method="post">
					<?php
						submit_button(__('Save settings', OpenWordPressSEO::TEXT_DOMAIN));
						settings_fields('pm-wp-seo-advanced');
						do_settings_sections('pm-wp-seo-advanced');
						submit_button(__('Save settings', OpenWordPressSEO::TEXT_DOMAIN));
					?>
				</form>
			</div>	
			
			<?php
				delete_option('open_wp_seo_sitemap_create_success');
				delete_option('open_wp_seo_image_sitemap_create_success');
				delete_option('open_wp_seo_htaccess_save');
				delete_option('open_wp_seo_gzip_test_result');				
			?>
		</div>
		</div> <!-- wrap -->
		<?php
	}
	
	private function print_notifications() {
		if (get_option('open_wp_seo_htaccess_save', FALSE) === OpenWordPressSEO::STATUS_ERROR) : ?>
		<div class="notice error">
			<p><strong><?php _e('Enabling Gzip compression failed. Could not not update .htaccess file. Please check that the file is writable.', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
		</div>
		<?php endif; ?>
		
		<?php if (get_option('open_wp_seo_gzip_test_result', FALSE) === OpenWordPressSEO::STATUS_ERROR) : ?>			
			<div class="notice error">
				<p><strong><?php _e('Gzip compression seems not to be working. Perhaps mod_deflate module is not active.', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
			</div>			
		<?php endif; ?>
		
		<?php 
			$htaccess_saved = get_option('open_wp_seo_htaccess_save', FALSE) === OpenWordPressSEO::STATUS_OK;
			$gzip_working = get_option('open_wp_seo_gzip_test_result', FALSE) === OpenWordPressSEO::STATUS_OK;
			if ($htaccess_saved && $gzip_working) : ?>
			<div class="notice updated">
				<?php if (get_option('open_wp_seo_gzip_compression') === OpenWordPressSEO::OPTION_ON) : ?>
				<p><strong><?php _e('Gzip compression is now enabled.', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
				<?php else : ?>
				<p><strong><?php _e('Gzip compression is now disabled.', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		
		<?php
			$sitemap_created_time = get_option('open_wp_seo_sitemap_create_time', FALSE);
			$sitemap_updated = get_option('open_wp_seo_sitemap_create_success', FALSE);
			$image_sitemap_created_time = get_option('open_wp_seo_image_sitemap_create_time', FALSE);
			$image_sitemap_updated = get_option('open_wp_seo_image_sitemap_create_success', FALSE);
							
			if ($sitemap_updated === 'not_enabled') {
				echo '<div class="notice error"><p><strong>'. __('Please check "Enable XML sitemap" option on Sitemap tab and save settings before using the Create sitemap now button.', OpenWordPressSEO::TEXT_DOMAIN).'</strong></p></div>';
			}

			if ($sitemap_updated === OpenWordPressSEO::STATUS_OK) {
				echo '<div class="notice updated"><p><strong>'. sprintf(__('<a  target="_blank" href="/sitemap.xml">Sitemap.xml</a> was succesfully updated %s.', OpenWordPressSEO::TEXT_DOMAIN), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $sitemap_created_time)).'</strong></p></div>';
			}
			
			if ($image_sitemap_updated === OpenWordPressSEO::STATUS_OK) {
				echo '<div class="notice updated"><p><strong>'. sprintf(__('<a href="/image-sitemap.xml" target="_blank">Image-sitemap.xml</a> was succesfully updated %s.', OpenWordPressSEO::TEXT_DOMAIN), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $image_sitemap_created_time)).'</strong></p></div>';
			}
		?>
		
		<?php if (get_option('open_wp_seo_sitemap_create_success', FALSE) === OpenWordPressSEO::STATUS_ERROR) : ?>
		<div class="notice error">
			<p><strong><?php printf(__('Could not create sitemap. Please check that your WordPress directory or %s is writable.', OpenWordPressSEO::TEXT_DOMAIN), OpenWordPressSEOSitemap::SITEMAP_FILENAME); ?></strong></p>
		</div>
		<?php endif; ?>
		
		<?php if (get_option('open_wp_seo_image_sitemap_create_success', FALSE) === OpenWordPressSEO::STATUS_ERROR) : ?>
		<div class="notice error">
			<p><strong><?php printf(__('Could not create image sitemap. Please check that your WordPress directory or %s is writable.', OpenWordPressSEO::TEXT_DOMAIN), OpenWordPressSEOImageSitemap::IMAGE_SITEMAP_FILENAME); ?></strong></p>
		</div>
		<?php endif; ?>	
		
		<?php if (get_option('blog_public') === '0') : ?>
		<div class="notice error">
			<p><strong><?php printf(__('Search engines are told not to index this site. Change the setting in <a href="%s">Reading</a> > Search Engine Visibility.', OpenWordPressSEO::TEXT_DOMAIN), get_admin_url().'/options-reading.php'); ?></strong></p>
		</div>				
		<?php endif; ?>
		
		<?php
		if (strpos(get_option('permalink_structure'), '%postname%') === FALSE) {
			?>
			<div class="notice error">
				<p><strong><?php _e('The permalink structure does not include post name. It is recommended to set permalink structure to "Post name" on Permalinks settings page.', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
			</div>			
			<?php				
		}
		
		if (get_option('open_wp_seo_ping_google_time', 0) + 300 > time()) : ?>
			<div class="notice updated">
				<p><strong><?php _e('Google was recently notified about changes.', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
			</div>			
		<?php endif;
		
		if (get_option('open_wp_seo_ping_bing_time', 0) + 300 > time()) : ?>
			<div class="notice updated">
				<p><strong><?php _e('Bing was recently notified about changes.', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
			</div>			
		<?php endif;

		if (get_option('open_wp_seo_ping_google_time', FALSE) == OpenWordPressSEO::STATUS_ERROR) : ?>
			<div class="notice error">
				<p><strong><?php _e('There was a problem notifying Google about changes.', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
			</div>			
		<?php endif;	

		if (get_option('open_wp_seo_ping_bing_time', FALSE) == OpenWordPressSEO::STATUS_ERROR) : ?>
			<div class="notice error">
				<p><strong><?php _e('There was a problem notifying Bing about changes.', OpenWordPressSEO::TEXT_DOMAIN); ?></strong></p>
			</div>			
		<?php endif;			
	}
	
	public function print_option_frontpage_title() {
		$frontpage_title = get_option('open_wp_seo_frontpage_title');
		echo '<input type="text" maxlength="60" name="open_wp_seo_frontpage_title" id="open_wp_seo_title" value="'.$frontpage_title.'"/>';
	}

	public function print_option_frontpage_description() {
		$frontpage_description = get_option('open_wp_seo_frontpage_description');
		echo '<textarea maxlength="160" name="open_wp_seo_frontpage_description" id="open_wp_seo_description">'.$frontpage_description.'</textarea>';
		?>		
		
		<div class="pm-wp-seo-serp-preview">
			<div class="pm-wp-seo-preview-title">
			<?php 
				$title = get_option('open_wp_seo_frontpage_title'); 
				if (empty($title)) {
					$title = get_bloginfo('name');				
				}
				
				if (mb_strlen($title) > 60) {
					$title = mb_substr($title, 0, 60);
				}
				
				echo $title;
			?>
			</div>
			<div class="pm-wp-seo-preview-address">
			<?php echo get_bloginfo('url'); ?>
			</div>
			<div class="pm-wp-seo-preview-description">
			<?php 
				$description = $frontpage_description; 
				
				if (empty($description)) {
					$description = get_bloginfo('description');
				}
				
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
		<?php
	}
	
	public function print_option_frontpage_seo_credits() {
		$show_credits = get_option('open_wp_seo_show_seo_credits');
		echo '<input type="checkbox" name="open_wp_seo_show_seo_credits" ' . checked(OpenWordPressSEO::OPTION_ON, $show_credits, FALSE) . '/>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Displays a credits notification for this plugin in the footer of the site. If you find this plugin useful, please check this option.', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}
	
	public function print_option_noindex_for_categories() {
		$use_noindex = get_option('open_wp_seo_noindex_for_categories');
		echo '<input type="checkbox" name="open_wp_seo_noindex_for_categories" ' . checked(OpenWordPressSEO::OPTION_ON, $use_noindex, FALSE) . '/>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Guide search engines not to index category pages. They may contain duplicate content.', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}

	public function print_option_noindex_for_tags() {
		$use_noindex = get_option('open_wp_seo_noindex_for_tags');
		echo '<input type="checkbox" name="open_wp_seo_noindex_for_tags" ' . checked(OpenWordPressSEO::OPTION_ON, $use_noindex, FALSE) . '/>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Guide search engines not to index tag archive pages. They may contain duplicate content.', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}
	
	public function print_option_noindex_for_archives() {
		$use_noindex = get_option('open_wp_seo_noindex_for_archives');
		echo '<input type="checkbox" name="open_wp_seo_noindex_for_archives" ' . checked(OpenWordPressSEO::OPTION_ON, $use_noindex, FALSE) . '/>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Guide search engines not to index user or date archive pages. They will most likely contain duplicate content that you don\'t want to have. (Recommended)', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}

	public function print_option_noindex_for_paged() {
		$use_noindex = get_option('open_wp_seo_noindex_for_paged');
		echo '<input type="checkbox" name="open_wp_seo_noindex_for_paged" ' . checked(OpenWordPressSEO::OPTION_ON, $use_noindex, FALSE) . '/>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Guide search engines not to index other than the first page of each page or article. (Recommended)', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}		

	public function print_option_sitemap_enable() {
		$sitemap_enabled = get_option('open_wp_seo_sitemap_enabled');
		echo '<input type="checkbox" name="open_wp_seo_sitemap_enabled" ' . checked(OpenWordPressSEO::OPTION_ON, $sitemap_enabled, FALSE) . '/>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('<p>The plugin will create and automatically maintain an XML sitemap when you add content. (Recommended)</p><p>After saving the settings, use the "Create sitemap now" button on Main Settings to verify that sitemap creation is working.</p>', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}
	
	public function print_option_sitemap_include_lastmod() {
		$sitemap_include_lastmod = get_option('open_wp_seo_sitemap_include_lastmod');
		echo '<input type="checkbox" name="open_wp_seo_sitemap_include_lastmod" ' . checked(OpenWordPressSEO::OPTION_ON, $sitemap_include_lastmod, FALSE) . '/>';
	}
	
	public function print_option_sitemap_include_tags() {
		$sitemap_include_tags = get_option('open_wp_seo_sitemap_include_tags');
		echo '<input type="checkbox" name="open_wp_seo_sitemap_include_tags" ' . checked(OpenWordPressSEO::OPTION_ON, $sitemap_include_tags, FALSE) . ' />';
	}
	
	public function print_option_sitemap_include_categories() {
		$sitemap_include_categories = get_option('open_wp_seo_sitemap_include_categories');
		echo '<input type="checkbox" name="open_wp_seo_sitemap_include_categories" ' . checked(OpenWordPressSEO::OPTION_ON, $sitemap_include_categories, FALSE) . ' />';
	}
	
	public function print_option_redirect_attachment_to_post() {
		$redirect = get_option('open_wp_seo_redirect_attachment_to_post');
		echo '<input type="checkbox" name="open_wp_seo_redirect_attachment_to_post" ' . checked(OpenWordPressSEO::OPTION_ON, $redirect, FALSE) . ' />';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Every image you attach to posts creates an attachment post. Redirect to original article when accessing these attachment posts. (Recommended)', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}
	
	public function print_option_disable_emojis() {
		$redirect = get_option('open_wp_seo_disable_emojis');
		echo '<input type="checkbox" name="open_wp_seo_disable_emojis" ' . checked(OpenWordPressSEO::OPTION_ON, $redirect, FALSE) . ' />';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('If you do not use emojis (little emotion icons) disable them to speed up the loading of website.', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}	
	
	public function print_option_sitemap_prioritities() {
		$sitemap_priorities = get_option('open_wp_seo_sitemap_prioritities');
		
		?>
		<table class="pm-wp-seo-sitemap-priorities">
		
		<tr>
			<th><?php _e('Item type', OpenWordPressSEO::TEXT_DOMAIN); ?></th>
			<th><?php _e('Priority', OpenWordPressSEO::TEXT_DOMAIN); ?></th>
		</tr>
		
		<?php 
			foreach (get_post_types(NULL, 'names') as $post_type) { 
			
				$post_type_details = get_post_type_object($post_type);
				$post_type_name = $post_type_details->labels->singular_name;
				if (empty($post_type_name)) {
					$post_type_name = $post_type;
				}
		?>
				<tr>
				<td><?php echo $post_type_name; ?></td>
				<td>
				<select name="open_wp_seo_sitemap_prioritities[<?php echo $post_type; ?>]" autocomplete="off">
					<?php 
						if (array_key_exists($post_type, $sitemap_priorities)) {
							$current_priority = $sitemap_priorities[$post_type];
						} 
						else {
							$current_priority = OpenWordPressSEOSitemap::PAGE_PRIORITY_MEDIUM;
						}
					
						$this->print_sitemap_priority_option(OpenWordPressSEOSitemap::PAGE_PRIORITY_HIGH, $current_priority, $post_type, __('High', OpenWordPressSEO::TEXT_DOMAIN)); 
						$this->print_sitemap_priority_option(OpenWordPressSEOSitemap::PAGE_PRIORITY_MEDIUM, $current_priority, $post_type, __('Medium', OpenWordPressSEO::TEXT_DOMAIN));
						$this->print_sitemap_priority_option(OpenWordPressSEOSitemap::PAGE_PRIORITY_LOW, $current_priority, $post_type, __('Low', OpenWordPressSEO::TEXT_DOMAIN)); 
					?>
				</select>
				<?php 
					if ($post_type == 'page') { 
						echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Medium or High value recommended.', OpenWordPressSEO::TEXT_DOMAIN) .'</span>'; 
					} 
					else if ($post_type == 'post') { 
						echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Medium or High value recommended.', OpenWordPressSEO::TEXT_DOMAIN) .'</span>'; 
					}
				?>
				</td>
				</tr>
			
		<?php } // end of for each ?>
		</table>
		
		<?php
	}
	
	public function print_option_exclude_posts() {
		$excluded_posts = get_option('open_wp_seo_exclude_posts');
		echo '<textarea style="width: 85%" name="open_wp_seo_exclude_posts" placeholder="'. __('Enter post IDs separated by commas...', OpenWordPressSEO::TEXT_DOMAIN) .'">'.$excluded_posts.'</textarea>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Enter the IDs you wish to exclude separated by commas.', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}
	
	public function print_option_add_code_to_footer() {
		$footer_code = get_option('open_wp_seo_add_code_to_footer');
		echo '<textarea style="width: 85%" cols="5" rows="7" name="open_wp_seo_add_code_to_footer" placeholder="'. __('Copy/paste your code here...', OpenWordPressSEO::TEXT_DOMAIN) .'">'.$footer_code.'</textarea>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('Here you can enter HTML / JavaScript (e.g. statistics scripts) that will be inserted into the footer of each page.', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}
	
	public function print_option_facebook_app_id() {
		$facebook_app_id = get_option('open_wp_seo_facebook_app_id');
		echo '<input type="text" class="narrow" name="open_wp_seo_facebook_app_id" value="'.$facebook_app_id.'"/>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. __('In order to use Facebook Insights you must add the app ID to your page. Insights lets you view analytics for traffic to your site from Facebook.', OpenWordPressSEO::TEXT_DOMAIN) .'</span>';
	}
	
	private function print_sitemap_priority_option($priority, $current_priority, $page_type, $text) {
		echo "<option value=\"{$priority}\" " . selected($current_priority, $priority, FALSE) . ">{$text}</option>";
	}
	
	public function print_option_google_analytics_code() {
		$tracking_code = get_option('open_wp_seo_google_analytics_code');
		echo '<input type="text" class="half-width" name="open_wp_seo_google_analytics_code" value="'.$tracking_code.'"/>';
		echo '<span class="dashicons dashicons-editor-help info"><span class="description">'. sprintf(__('The tracking code you get from Google Analytics (%s).', OpenWordPressSEO::TEXT_DOMAIN), 'www.google.com/analytics') .'</span>';
	}	
	
	private function print_automatic_titles_options_header() {
		echo '<h2>'.__('<span class="dashicons dashicons-admin-settings"></span> Automatic Titles', OpenWordPressSEO::TEXT_DOMAIN).'</h2>';
	}
	
	private function print_automatic_title_instructions() {
		echo '<div class="pm-wp-seo-instructions"><p><span class="dashicons dashicons-info"></span> ';
		_e('Use the following variables in the titles to print out post or site related information:', OpenWordPressSEO::TEXT_DOMAIN);
		echo '</p><ul>';
		echo '<li><strong>%article_name%</strong> - '.__('The name of the item', OpenWordPressSEO::TEXT_DOMAIN).'</li>';
		echo '<li><strong>%site_name%</strong> - '.__('The name of the site', OpenWordPressSEO::TEXT_DOMAIN).'</li>';	
		echo '<li><strong>%category_name%</strong> - '.__('The name of the item\'s first category', OpenWordPressSEO::TEXT_DOMAIN).'</li>';
		echo '<li><strong>%author_name%</strong> - '.__('The name of the item\'s author', OpenWordPressSEO::TEXT_DOMAIN).'</li>';
		echo '<li><strong>%article_date%</strong> - '.__('The publish date of the item', OpenWordPressSEO::TEXT_DOMAIN).'</li>';
		echo '</ul>';
		echo '</div>';
	}
	
	public function print_option_use_automatic_titles() {
		echo '<tr><th scope="row">'.__('Use automatic titles when post specific title has not been set', OpenWordPressSEO::TEXT_DOMAIN).'</th><td>';
		
		$use_automatic_titles = get_option('open_wp_seo_use_automatic_titles');
		echo '<input type="checkbox" name="open_wp_seo_use_automatic_titles" ' . checked(OpenWordPressSEO::OPTION_ON, $use_automatic_titles, FALSE) . ' />';
		
		echo '</td></tr>';
	}
	
	public function print_automatic_title_setting_fields() {
		foreach (get_post_types(NULL, 'names') as $post_type) {
			$title = get_option('open_wp_seo_automatic_title_' . $post_type);
			$post_type_details = get_post_type_object($post_type);
			$post_type_name = $post_type_details->labels->singular_name;
			if (empty($post_type_name)) {
				$post_type_name = $post_type;
			}
			
			echo '<tr><th scope="row">'.__('Title format for post type: ', OpenWordPressSEO::TEXT_DOMAIN).$post_type_name.'</th><td><input type="text" maxlength="200" name="open_wp_seo_automatic_title_'.$post_type.'" value="'.$title.'"></td></tr>';
		}
	}
	
}