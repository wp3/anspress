<?php
/**
 * The Category module.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Tag;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\Plugin;

/**
 * Category module class.
 *
 * @since 5.0.0
 */
class TagModule extends AbstractModule {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'registerQuestionTag' ), 1 );
		add_action( 'init', array( $this, 'registerBlocks' ) );

		add_action( 'ap_settings_menu_features_groups', array( $this, 'add_to_settings_page' ) );

		add_action( 'ap_admin_menu', array( $this, 'admin_tags_menu' ) );
		add_action( 'ap_question_info', array( $this, 'ap_question_info' ) );
		add_action( 'ap_enqueue', array( $this, 'ap_assets_js' ) );
		add_action( 'ap_enqueue', array( $this, 'ap_localize_scripts' ) );
		add_filter( 'term_link', array( $this, 'term_link_filter' ), 10, 3 );
		add_filter( 'ap_page_title', array( $this, 'page_title' ) );
		add_filter( 'ap_breadcrumbs', array( $this, 'ap_breadcrumbs' ) );
		add_action( 'ap_rewrites', array( $this, 'rewrite_rules' ), 10, 3 );
		add_filter( 'ap_main_questions_args', array( $this, 'ap_main_questions_args' ) );
		add_filter( 'ap_category_questions_args', array( $this, 'ap_main_questions_args' ) );
		add_filter( 'ap_current_page', array( $this, 'ap_current_page' ) );
		add_action( 'posts_pre_query', array( $this, 'modify_query_archive' ), 9999, 2 );

		// List filtering.
		add_filter( 'ap_list_filters', array( $this, 'ap_list_filters' ) );
		add_action( 'ap_ajax_load_filter_qtag', array( $this, 'load_filter_tag' ) );
		add_action( 'ap_ajax_load_filter_tags_order', array( $this, 'load_filter_tags_order' ) );
		add_filter( 'ap_list_filter_active_qtag', array( $this, 'filter_active_tag' ), 10, 2 );
		add_filter( 'ap_list_filter_active_tags_order', array( $this, 'filter_active_tags_order' ), 10, 2 );
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public function registerBlocks() {
		register_block_type( Plugin::getPathTo( 'build/frontend/tags' ) );
	}

	/**
	 * Register tag taxonomy for question cpt.
	 *
	 * @return void
	 * @since 2.0
	 */
	public function registerQuestionTag() {
		ap_add_default_options(
			array(
				'max_tags'      => 5,
				'min_tags'      => 1,
				'tags_per_page' => 20,
				'tag_page_slug' => 'tag',
			)
		);

		$tag_labels = array(
			'name'               => __( 'Question Tags', 'anspress-question-answer' ),
			'singular_name'      => __( 'Tag', 'anspress-question-answer' ),
			'all_items'          => __( 'All Tags', 'anspress-question-answer' ),
			'add_new_item'       => __( 'Add New Tag', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit Tag', 'anspress-question-answer' ),
			'new_item'           => __( 'New Tag', 'anspress-question-answer' ),
			'view_item'          => __( 'View Tag', 'anspress-question-answer' ),
			'search_items'       => __( 'Search Tag', 'anspress-question-answer' ),
			'not_found'          => __( 'Nothing Found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'anspress-question-answer' ),
			'parent_item_colon'  => '',
		);

		/**
		 * FILTER: ap_question_tag_labels
		 * Filter ic called before registering question_tag taxonomy
		 */
		$tag_labels = apply_filters( 'ap_question_tag_labels', $tag_labels );
		$tag_args   = array(
			'hierarchical' => true,
			'labels'       => $tag_labels,
			'rewrite'      => false,
			'show_in_rest' => true,
		);

		/**
		 * FILTER: ap_question_tag_args
		 * Filter ic called before registering question_tag taxonomy
		 */
		$tag_args = apply_filters( 'ap_question_tag_args', $tag_args );

		/**
		 * Now let WordPress know about our taxonomy
		 */
		register_taxonomy( 'question_tag', array( 'question' ), $tag_args );
	}

	/**
	 * Add tags menu in wp-admin.
	 */
	public function admin_tags_menu() {
		add_submenu_page( 'anspress', __( 'Question Tags', 'anspress-question-answer' ), __( 'Tags', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_tag' );
	}

	/**
	 * Add tags settings to features settings page.
	 *
	 * @param array $groups Features settings group.
	 * @return array
	 * @since 4.2.0
	 */
	public function add_to_settings_page( $groups ) {
		$groups['tag'] = array(
			'label' => __( 'Tag', 'anspress-question-answer' ),
		);

		return $groups;
	}

	/**
	 * Hook tags after post.
	 *
	 * @param object $post Post object.
	 * @since 1.0
	 */
	public function ap_question_info( $post ) {
		if ( ap_question_have_tags() ) {
			echo wp_kses_post( '<div class="widget"><span class="ap-widget-title">' . esc_attr__( 'Tags', 'anspress-question-answer' ) . '</span>' );

			echo wp_kses_post(
				'<div class="ap-post-tags clearfix">' .
				ap_question_tags_html(
					array(
						'list'  => true,
						'label' => '',
					)
				) .
				'</div></div>'
			);
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function ap_assets_js() {
		wp_enqueue_script( 'anspress-tags', ANSPRESS_URL . 'assets/js/tags.js', array( 'anspress-list' ), AP_VERSION, true );
	}

	/**
	 * Add translated strings to the javascript files
	 */
	public function ap_localize_scripts() {
		$l10n_data = array(
			'deleteTag'            => __( 'Delete Tag', 'anspress-question-answer' ),
			'addTag'               => __( 'Add Tag', 'anspress-question-answer' ),
			'tagAdded'             => __( 'added to the tags list.', 'anspress-question-answer' ),
			'tagRemoved'           => __( 'removed from the tags list.', 'anspress-question-answer' ),
			'suggestionsAvailable' => __( 'Suggestions are available. Use the up and down arrow keys to read it.', 'anspress-question-answer' ),
		);

		wp_localize_script(
			'anspress-tags',
			'apTagsTranslation',
			$l10n_data
		);
	}

	/**
	 * Filter tag term link.
	 *
	 * @param  string $url      Default URL of taxonomy.
	 * @param  object $term     Term array.
	 * @param  string $taxonomy Taxonomy type.
	 * @return string           New URL for term.
	 */
	public function term_link_filter( $url, $term, $taxonomy ) {
		if ( 'question_tag' === $taxonomy ) {
			if ( get_option( 'permalink_structure' ) !== '' ) {
				$opt          = get_option( 'ap_tags_path', 'tags' );
				$default_lang = '';

				// Support polylang permalink.
				if ( function_exists( 'pll_default_language' ) ) {
					$default_lang = pll_get_term_language( $term->term_id ) ? pll_get_term_language( $term->term_id ) : pll_default_language();
				}

				return user_trailingslashit( home_url( $default_lang . '/' . $opt ) . '/' . $term->slug );
			} else {
				return add_query_arg(
					array(
						'ap_page'      => 'tag',
						'question_tag' => $term->slug,
					),
					home_url()
				);
			}
		}
		return $url;
	}

	/**
	 * Tags page title.
	 *
	 * @param  string $title Title.
	 * @return string
	 */
	public function page_title( $title ) {
		if ( is_question_tags() ) {
			$title = ap_opt( 'tags_page_title' );
		} elseif ( is_question_tag() ) {
			$tag_id = sanitize_title( get_query_var( 'q_tag' ) );
			$tag    = $tag_id ? get_term_by( 'slug', $tag_id, 'question_tag' ) : get_queried_object();

			if ( $tag ) {
				$title = $tag->name;
			}
		}

		return $title;
	}

	/**
	 * Hook into AnsPress breadcrums to show tags page.
	 *
	 * @param  array $navs Breadcrumbs navs.
	 * @return array
	 */
	public function ap_breadcrumbs( $navs ) {
		if ( is_question_tag() ) {
			$tag_id       = sanitize_title( get_query_var( 'q_tag' ) );
			$tag          = $tag_id ? get_term_by( 'slug', $tag_id, 'question_tag' ) : get_queried_object();
			$navs['page'] = array(
				'title' => __( 'Tags', 'anspress-question-answer' ),
				'link'  => ap_get_link_to( 'tags' ),
				'order' => 8,
			);

			if ( $tag ) {
				$navs['tag'] = array(
					'title' => $tag->name,
					'link'  => get_term_link( $tag, 'question_tag' ), // @codingStandardsIgnoreLine.
					'order' => 8,
				);
			}
		} elseif ( is_question_tags() ) {
			$navs['page'] = array(
				'title' => __( 'Tags', 'anspress-question-answer' ),
				'link'  => ap_get_link_to( 'tags' ),
				'order' => 8,
			);
		}

		return $navs;
	}

	/**
	 * Add category pages rewrite rule.
	 *
	 * @param array  $rules AnsPress rules.
	 * @param string $slug Slug.
	 * @param int    $base_page_id AnsPress base page id.
	 * @return array
	 */
	public function rewrite_rules( $rules, $slug, $base_page_id ) {
		$base_slug = get_page_uri( ap_opt( 'tags_page' ) );
		update_option( 'ap_tags_path', $base_slug, true );
		$lang_rule    = str_replace( ap_base_page_slug() . '/', '', $slug );
		$lang_rewrite = str_replace( ap_opt( 'base_page' ), '', $base_page_id );

		$cat_rules = array(
			$lang_rule . $base_slug . '/([^/]+)/page/?([0-9]{1,})/?$' => $lang_rewrite . 'index.php?question_tag=$matches[#]&ap_paged=$matches[#]&ap_page=tag',
			$lang_rule . $base_slug . '/([^/]+)/?$' => $lang_rewrite . 'index.php?question_tag=$matches[#]&ap_page=tag',
		);

		return $cat_rules + $rules;
	}

	/**
	 * Filter main questions query args. Modify and add tags args.
	 *
	 * @param  array $args Questions args.
	 * @return array
	 */
	public function ap_main_questions_args( $args ) {
		global $wp;
		$query = $wp->query_vars;

		$current_filter = ap_get_current_list_filters( 'qtag' );
		$tags_operator  = ! empty( $wp->query_vars['ap_tags_operator'] ) ? $wp->query_vars['ap_tags_operator'] : 'IN';

		if ( isset( $query['ap_tags'] ) && is_array( $query['ap_tags'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'question_tag',
				'field'    => 'slug',
				'terms'    => $query['ap_tags'],
				'operator' => $tags_operator,
			);
		} elseif ( ! empty( $current_filter ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'question_tag',
				'field'    => 'term_id',
				'terms'    => $current_filter,
				'operator' => 'IN',
			);
		}

		return $args;
	}

	/**
	 * Add tags sorting in list filters
	 *
	 * @param array $filters Filters.
	 * @return array
	 */
	public function ap_list_filters( $filters ) {
		global $wp;

		if ( ! isset( $wp->query_vars['ap_tags'] ) && 'tag' !== ap_current_page() ) {
			$filters['qtag'] = array(
				'title'    => __( 'Tag', 'anspress-question-answer' ),
				'search'   => true,
				'multiple' => true,
			);
		}

		if ( 'tags' === ap_current_page() ) {
			return array(
				'tags_order' => array(
					'title' => __( 'Order', 'anspress-question-answer' ),
				),
			);
		}

		return $filters;
	}

	/**
	 * Ajax callback for loading order by filter.
	 *
	 * @since 4.0.0
	 */
	public function load_filter_tag() {
		$filter = ap_sanitize_unslash( 'filter', 'r' );
		check_ajax_referer( 'filter_' . $filter, '__nonce' );
		$search = ap_sanitize_unslash( 'search', 'r', false );

		ap_ajax_json(
			array(
				'success'  => true,
				'items'    => ap_get_tag_filter( $search ),
				'multiple' => true,
				'nonce'    => wp_create_nonce( 'filter_' . $filter ),
			)
		);
	}

	/**
	 * Ajax callback for loading order by filter for tags.
	 *
	 * @since 4.0.0
	 */
	public function load_filter_tags_order() {
		$filter = ap_sanitize_unslash( 'filter', 'r' );
		check_ajax_referer( 'filter_' . $filter, '__nonce' );

		ap_ajax_json(
			array(
				'success' => true,
				'items'   => array(
					array(
						'key'   => 'tags_order',
						'value' => 'popular',
						'label' => __( 'Popular', 'anspress-question-answer' ),
					),
					array(
						'key'   => 'tags_order',
						'value' => 'new',
						'label' => __( 'New', 'anspress-question-answer' ),
					),
					array(
						'key'   => 'tags_order',
						'value' => 'name',
						'label' => __( 'Name', 'anspress-question-answer' ),
					),
				),
				'nonce'   => wp_create_nonce( 'filter_' . $filter ),
			)
		);
	}

	/**
	 * Output active tag in filter
	 *
	 * @param bool  $active Is active.
	 * @param mixed $filter Current filter.
	 * @since 4.0.0
	 */
	public function filter_active_tag( $active, $filter ) {
		$current_filters = ap_get_current_list_filters( 'qtag' );

		if ( ! empty( $current_filters ) ) {
			$args = array(
				'taxonomy'      => 'question_tag',
				'hierarchical'  => true,
				'hide_if_empty' => true,
				'number'        => 2,
				'include'       => $current_filters,
			);

			$terms = get_terms( $args );

			if ( $terms ) {
				$active_terms = array();
				foreach ( (array) $terms as $t ) {
					$active_terms[] = $t->name;
				}

				$count = count( $current_filters );

				// translators: Placeholder contains count.
				$more_label = sprintf( __( ', %d+', 'anspress-question-answer' ), $count - 2 );

				return ': <span class="ap-filter-active">' . implode( ', ', $active_terms ) . ( $count > 2 ? $more_label : '' ) . '</span>';
			}
		}
	}

	/**
	 * Output active tags_order in filter
	 *
	 * @param string $active Active filter.
	 * @param array  $filter Filter.
	 * @since 4.1.0
	 */
	public function filter_active_tags_order( $active, $filter ) {
		$tags_order = ap_get_current_list_filters( 'tags_order' );
		$tags_order = ! empty( $tags_order ) ? $tags_order : 'popular';

		$orders = array(
			'popular' => __( 'Popular', 'anspress-question-answer' ),
			'new'     => __( 'New', 'anspress-question-answer' ),
			'name'    => __( 'Name', 'anspress-question-answer' ),
		);

		$active = isset( $orders[ $tags_order ] ) ? $orders[ $tags_order ] : '';

		return ': <span class="ap-filter-active">' . $active . '</span>';
	}

	/**
	 * Modify current page to show tag archive.
	 *
	 * @param string $query_var Current page.
	 * @return string
	 * @since 4.1.0
	 */
	public function ap_current_page( $query_var ) {
		if ( 'tags' === $query_var && 'tag' === get_query_var( 'ap_page' ) ) {
			return 'tag';
		}

		return $query_var;
	}

	/**
	 * Modify main query to show tag archive.
	 *
	 * @param array|null $posts Array of objects.
	 * @param object     $query Wp_Query object.
	 *
	 * @return array|null
	 * @since 4.1.0
	 */
	public function modify_query_archive( $posts, $query ) {
		if ( $query->is_main_query() &&
			$query->is_tax( 'question_tag' ) &&
			'tag' === get_query_var( 'ap_page' ) ) {
			$query->found_posts   = 1;
			$query->max_num_pages = 1;
			$page                 = get_page( ap_opt( 'tags_page' ) );
			$page->post_title     = get_queried_object()->name;
			$posts                = array( $page );
		}

		return $posts;
	}
}
