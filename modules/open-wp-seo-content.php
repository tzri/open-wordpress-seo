<?php
/**
 * Module for modifying post content.
 */
if (!defined('ABSPATH')) return;
 
class OpenWordPressSEOContent {

	const RECOMMENDED_MINIMUM_POST_WORD_COUNT = 300;

	public function is_word_count_too_low($content) {
		return str_word_count(strip_tags($content)) < self::RECOMMENDED_MINIMUM_POST_WORD_COUNT;
	}
	
	public function does_all_title_words_appear_in_content($title, $content) {
		$content = strtolower($content);
		$title_words = explode(' ', $title);
		$content_words = explode(' ', $content);
		$title_words_in_content = 0;
		foreach ($title_words as $title_word) {
			$title_word = strtolower($this->remove_special_characters($title_word));
			foreach ($content_words as $content_word) {
				$content_word = $this->remove_special_characters($content_word);
				if (levenshtein($title_word, $content_word) < 4) {
					$title_words_in_content++;
					break;
				}
			}
		}
		return count($title_words) === $title_words_in_content;
	}
	
	public function fill_missing_img_alt_tags_with_filename($content) {
		// Method by Matt Musia https://stackoverflow.com/a/38537901
		$pattern = '#<img(?!.*alt=")(.+src="(([^"]+/)?(.+)\..+)"[^ /]*)( ?\/?)>#i';
		$parsed_html = preg_replace($pattern, '<img$1 alt="$4"$5>', $content);		
		return $parsed_html;
	}

	private function remove_special_characters($text) {
		return preg_replace('/[^A-Za-z0-9\-]/', '', $text);
	}
	
	public function content_contains_hyperlinks($content) {		
		return strpos($content, 'http') !== FALSE
				|| strpos($content, 'www.') !== FALSE
				|| strpos($content, 'ftp.') !== FALSE;
	}
	
}
