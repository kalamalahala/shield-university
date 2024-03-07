<?php
/**
 * Shield University functions and definitions
 * 
 * @package Shield University
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'SHIELD_UNIVERSITY_VERSION', '1.0.0' );

if ( ! function_exists( 'add_jquery_ui' ) ) {
    function add_jquery_ui() {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

        // include search-autocomplete.js
        wp_enqueue_script('search-autocomplete', get_template_directory_uri() . '/../shield-university/search-autocomplete.js', array('jquery'), '1.0.0', true);

		// create an array of strings of post titles type 'article' and published
		$posts = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'article',
			'post_status' => 'publish',
		));

		$titles = array();
		foreach ($posts as $post) {
			$titles[] = $post->post_title;
		}

		// localize the script with the array of post titles
		wp_localize_script('search-autocomplete', 'postTitles', $titles);
    }
}
add_action('wp_enqueue_scripts', 'add_jquery_ui');
if ( ! function_exists( 'list_article_taxonomy_contents' ) ) {
	function list_article_taxonomy_contents() {
		$terms = get_terms( array(
			'taxonomy' => 'article-category',
			'hide_empty' => false,
		) );

		$contents = array();
		foreach ($terms as $term) {
			if ( $term->slug === 'release-notes' || $term->slug === 'application-user-manuals' ) {
				continue;
			}
			// $icon = get_field('card_icon', 'term_' . $term->term_id);
			$icon = get_field('taxonomy_icon', 'term_' . $term->term_id);
			$order = get_field('order_in_grid', 'term_' . $term->term_id);


			$contents[] = array(
				'id' => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
				'description' => $term->description,
				'icon' => $icon,
				'order' => $order,
			);
		}

		usort($contents, function($a, $b) {
			return $a['order'] - $b['order'];
		});

		// $icon_test = get_fields('term_154');

		$output = '<div class="article-category-cards">';
		foreach ($contents as $content) {
			$article_category_link = get_term_link($content['id']);
			/* place the image above a blue background the full size of the card */
			$output .= <<<HTML
				<a href="{$article_category_link}" class="article-category-card">
					
						<div class="article-category-card-content">
							<!-- <div class="article-category-card-icon">{$content['icon']}</div> -->
							<img src="{$content['icon']['url']}" class="article-category-card-image" />
							<h2 class="article-category-title">{$content['name']}</h2>
						</div>
					
				</a>
			HTML;
		}
		$output .= '</div>';
		return $output;
	}
}
add_shortcode('list_article_taxonomy_contents', 'list_article_taxonomy_contents');

if ( ! function_exists( 'lefta_custom_search' ) ) {
	function lefta_custom_search($atts) {
		$atts = shortcode_atts( array(
			'placeholder' => 'Search',
		), $atts );

		$site_url = get_site_url();
		$article_taxons = get_terms( array(
			'taxonomy' => 'article-category',
			'hide_empty' => false,
		) );

		foreach ($article_taxons as $item) {
			$order = get_field('order_in_grid', 'term_' . $item->term_id);
			$item->order = $order;
		}

		usort($article_taxons, function($a, $b) {
			return $a->order - $b->order;
		});

		global $post;

		$dropdown = '<select name="article-category" id="article-category">';
		$dropdown .= '<option value="">All Applications</option>';
		foreach ($article_taxons as $taxon) {
			if (isset($_GET['article-category']) && $_GET['article-category'] === $taxon->slug) {
				$dropdown .= '<option value="' . $taxon->slug . '" selected>' . $taxon->name . '</option>';
			} else if (isset($post) && has_term($taxon->slug, 'article-category', $post)) {
				$dropdown .= '<option value="' . $taxon->slug . '" selected>' . $taxon->name . '</option>';
			} else {
				$dropdown .= '<option value="' . $taxon->slug . '">' . $taxon->name . '</option>';
			}
		}
		$dropdown .= '</select>';

		if (isset($_GET['s'])) {
			$search_query = $_GET['s'];
		} else {
			$search_query = '';
		}

		$output = <<<HTML
			<form role="search" method="get" class="lefta-search-form" action="{$site_url}" id="search-form">
				<label for="search-field" class="screen-reader-text">{$atts['placeholder']}</label>
					<span class="screen-reader-text">{$atts['placeholder']}</span>
					<input id="search-field" type="search" class="lefta-search-field" placeholder="{$atts['placeholder']}" value="{$search_query}" name="s" title="{$atts['placeholder']}" />
				{$dropdown}
				<input type="submit" class="lefta-search-submit" value="Search" />
			</form>
		HTML;
		return $output;
	}
}
add_shortcode('lefta_custom_search', 'lefta_custom_search');

if ( ! function_exists( 'notes_and_manuals' ) ) {
	function notes_and_manuals() {
		$terms = get_terms( array(
			'taxonomy' => 'article-category',
			'hide_empty' => false,
		) );

		$contents = array();
		foreach ($terms as $term) {
			if ( $term->slug === 'release-notes' || $term->slug === 'application-user-manuals' ) {
				// $icon = get_field('card_icon', 'term_' . $term->term_id);
				$icon = get_field('taxonomy_icon', 'term_' . $term->term_id);
				$order = get_field('order_in_grid', 'term_' . $term->term_id);
				// if (!isset($icon['url'])) {
				// 	$icon['url'] = 'https://via.placeholder.com/150';
				// }

				$contents[] = array(
					'id' => $term->term_id,
					'name' => $term->name,
					'slug' => $term->slug,
					'description' => $term->description,
					'icon' => $icon,
					'order' => $order,
				);
			}
		}

		usort($contents, function($a, $b) {
			return $a['order'] - $b['order'];
		});

		$output = '<div class="article-category-cards">';
		foreach ($contents as $content) {
			$article_category_link = get_term_link($content['id']);
			/* place the image above a blue background the full size of the card */
			$output .= <<<HTML
				<a href="{$article_category_link}" class="article-category-card">
					
						<div class="article-category-card-content">
							<img src="{$content['icon']['url']}" class="article-category-card-image" />
							<h2 class="article-category-title">{$content['name']}</h2>
						</div>
					
				</a>
			HTML;
		}
		$output .= '</div>';
		return $output;
	}
}
add_shortcode('notes_and_manuals', 'notes_and_manuals');

if ( ! function_exists( 'lefta_search_filter' ) ) {
	function lefta_search_filter($query) {
		if ($query->is_search && !is_admin() ) {
			if (isset($_GET['article-category']) && $_GET['article-category'] !== '') {
				$tax_query = array(
					array(
						'taxonomy' => 'article-category',
						'field' => 'slug',
						'terms' => $_GET['article-category'],
					),
				);
				$query->set('tax_query', $tax_query);
			}
		}
		return $query;
	}
}
add_filter('pre_get_posts', 'lefta_search_filter');

if ( ! function_exists( 'search_by_title_only' ) ) {
	function search_by_title_only($search, $wp_query) {
		global $wpdb;
		if (empty($search)) {
			return $search;
		}
		$q = $wp_query->query_vars;
		$n = !empty($q['exact']) ? '' : '%';
		$search =
		$searchand = '';
		foreach ((array)$q['search_terms'] as $term) {
			$term = esc_sql($wpdb->esc_like($term));
			$search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
			$searchand = ' AND ';
		}
		if (!empty($search)) {
			$search = " AND ({$search}) ";
			if (!is_user_logged_in()) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}
		return $search;
	}
}
add_filter('posts_search', 'search_by_title_only', 500, 2);

if ( !function_exists('sort_elementor_archive_by_title') ) {
	function sort_elementor_archive_by_title($query) {
		if ( $query->is_main_query() && !is_admin() && $query->is_archive() ) {
			$query->set('orderby', 'title');
			$query->set('order', 'ASC');
		}
	}
}
add_action('pre_get_posts', 'sort_elementor_archive_by_title');