<?php
/**
 * The Reputation module.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Reputation;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\Plugin;
use AnsPress\Classes\PostHelper;
use AnsPress\Modules\Vote\VoteModel;

/**
 * Reputation module class.
 *
 * @since 5.0.0
 */
class ReputationModule extends AbstractModule {
	/**
	 * Register hooks.
	 *
	 * @since 5.0.0
	 */
	public function register_hooks() {
		$this->register_default_events();

		ap_add_default_options(
			array(
				'user_page_title_reputations' => __( 'Reputations', 'anspress-question-answer' ),
				'user_page_slug_reputations'  => 'reputations',
			)
		);

		add_action( 'ap_settings_menu_features_groups', array( $this, 'add_to_settings_page' ) );
		add_action( 'ap_form_options_features_reputation', array( $this, 'load_options' ), 20 );
		add_action( 'wp_ajax_ap_save_events', array( $this, 'ap_save_events' ) );
		add_action( 'save_post_question', array( $this, 'newQuestion' ), 10, 3 );
		add_action( 'save_post_answer', array( $this, 'newAnswer' ), 10, 3 );
		add_action( 'trashed_post', array( $this, 'trashQuestion' ), 10, 2 );
		add_action( 'untrashed_post', array( $this, 'untrashedPost' ), 10 );
		add_action( 'trashed_post', array( $this, 'trashAnswer' ) );
		add_action( 'ap_select_answer', array( $this, 'select_answer' ) );
		add_action( 'ap_unselect_answer', array( $this, 'unselect_answer' ) );
		add_action( 'anspress/model/after_insert/ap_votes', array( $this, 'receivedVote' ) );
		add_action( 'anspress/model/after_delete/ap_votes', array( $this, 'undoVote' ) );
		add_action( 'comment_post', array( $this, 'commentStatusChange' ), 10, 2 );
		add_action( 'wp_set_comment_status', array( $this, 'commentStatusChange' ), 10, 2 );
		add_action( 'delete_comment', array( $this, 'deleteComment' ) );
		add_action( 'trash_comment', array( $this, 'deleteComment' ) );
		add_filter( 'user_register', array( $this, 'user_register' ) );
		add_action( 'delete_user', array( $this, 'delete_user' ) );
		add_filter( 'ap_user_display_name', array( $this, 'display_name' ), 10, 2 );
		add_filter( 'bp_before_member_header_meta', array( $this, 'bp_profile_header_meta' ) );
		add_filter( 'ap_user_pages', array( $this, 'ap_user_pages' ) );
		add_filter( 'ap_ajax_load_more_reputation', array( $this, 'load_more_reputation' ) );
		add_filter( 'ap_bp_nav', array( $this, 'ap_bp_nav' ) );
		add_filter( 'ap_bp_page', array( $this, 'ap_bp_page' ), 10, 2 );
		add_filter( 'ap_all_options', array( $this, 'ap_all_options' ), 10, 2 );
	}

	/**
	 * Add tags settings to features settings page.
	 *
	 * @param array $groups Features settings group.
	 * @return array
	 * @since 4.2.0
	 */
	public function add_to_settings_page( $groups ) {
		$groups['reputation'] = array(
			'label' => __( 'Reputation', 'anspress-question-answer' ),
			'info'  => __( 'Reputation event points can be adjusted here :', 'anspress-question-answer' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=anspress_options&active_tab=reputations' ) ) . '">' . __( 'Reputation Points', 'anspress-question-answer' ) . '</a>',
		);

		return $groups;
	}

	/**
	 * Register reputation options
	 */
	public function load_options() {
		$opt  = ap_opt();
		$form = array(
			'fields' => array(
				'user_page_title_reputations' => array(
					'label' => __( 'Reputations page title', 'anspress-question-answer' ),
					'desc'  => __( 'Custom title for user profile reputations page', 'anspress-question-answer' ),
					'value' => $opt['user_page_title_reputations'],
				),
				'user_page_slug_reputations'  => array(
					'label' => __( 'Reputations page slug', 'anspress-question-answer' ),
					'desc'  => __( 'Custom slug for user profile reputations page', 'anspress-question-answer' ),
					'value' => $opt['user_page_slug_reputations'],
				),
			),
		);

		return $form;
	}

	/**
	 * Register default reputation events.
	 */
	public function register_default_events() {
		$events_db = wp_cache_get( 'all', 'ap_get_all_reputation_events' );

		if ( false === $events_db ) {
			$events_db = ap_get_all_reputation_events();
		}

		// If already in DB return from here.
		if ( ! $events_db ) {
			$events = array(
				array(
					'slug'        => 'register',
					'label'       => __( 'Registration', 'anspress-question-answer' ),
					'description' => __( 'Points awarded when user account is created', 'anspress-question-answer' ),
					'icon'        => 'apicon-question',
					'activity'    => __( 'Registered', 'anspress-question-answer' ),
					'points'      => 10,
				),
				array(
					'slug'        => 'ask',
					'points'      => 2,
					'label'       => __( 'Asking', 'anspress-question-answer' ),
					'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
					'icon'        => 'apicon-question',
					'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
					'parent'      => 'question',
				),
				array(
					'slug'        => 'answer',
					'points'      => 5,
					'label'       => __( 'Answering', 'anspress-question-answer' ),
					'description' => __( 'Points awarded when user answers a question', 'anspress-question-answer' ),
					'icon'        => 'apicon-answer',
					'activity'    => __( 'Posted an answer', 'anspress-question-answer' ),
					'parent'      => 'answer',
				),
				array(
					'slug'        => 'comment',
					'points'      => 2,
					'label'       => __( 'Commenting', 'anspress-question-answer' ),
					'description' => __( 'Points awarded when user comments on question or answer', 'anspress-question-answer' ),
					'icon'        => 'apicon-comments',
					'activity'    => __( 'Commented on a post', 'anspress-question-answer' ),
					'parent'      => 'comment',
				),
				array(
					'slug'        => 'select_answer',
					'points'      => 2,
					'label'       => __( 'Selecting an Answer', 'anspress-question-answer' ),
					'description' => __( 'Points awarded when user selects an answer for their question', 'anspress-question-answer' ),
					'icon'        => 'apicon-check',
					'activity'    => __( 'Selected an answer as best', 'anspress-question-answer' ),
					'parent'      => 'question',
				),
				array(
					'slug'        => 'best_answer',
					'points'      => 10,
					'label'       => __( 'Answer selected as best', 'anspress-question-answer' ),
					'description' => __( 'Points awarded when user\'s answer is selected as best', 'anspress-question-answer' ),
					'icon'        => 'apicon-check',
					'activity'    => __( 'Answer was selected as best', 'anspress-question-answer' ),
					'parent'      => 'answer',
				),
				array(
					'slug'        => 'received_vote_up',
					'points'      => 10,
					'label'       => __( 'Received up vote', 'anspress-question-answer' ),
					'description' => __( 'Points awarded when user receives an upvote', 'anspress-question-answer' ),
					'icon'        => 'apicon-thumb-up',
					'activity'    => __( 'Received an upvote', 'anspress-question-answer' ),
				),
				array(
					'slug'        => 'received_vote_down',
					'points'      => -2,
					'label'       => __( 'Received down vote', 'anspress-question-answer' ),
					'description' => __( 'Points awarded when user receives a down vote', 'anspress-question-answer' ),
					'icon'        => 'apicon-thumb-down',
					'activity'    => __( 'Received a down vote', 'anspress-question-answer' ),
				),
				array(
					'slug'        => 'given_vote_up',
					'points'      => 0,
					'label'       => __( 'Gives an up vote', 'anspress-question-answer' ),
					'description' => __( 'Points taken from user when they give an up vote', 'anspress-question-answer' ),
					'icon'        => 'apicon-thumb-up',
					'activity'    => __( 'Given an up vote', 'anspress-question-answer' ),
				),
				array(
					'slug'        => 'given_vote_down',
					'points'      => 0,
					'label'       => __( 'Gives down vote', 'anspress-question-answer' ),
					'description' => __( 'Points taken from user when they give a down vote', 'anspress-question-answer' ),
					'icon'        => 'apicon-thumb-down',
					'activity'    => __( 'Given a down vote', 'anspress-question-answer' ),
				),
			);

			$custom_points = get_option( 'anspress_reputation_events', array() );

			foreach ( $events as $event ) {
				$points = isset( $custom_points[ $event['slug'] ] ) ? (int) $custom_points[ $event['slug'] ] : (int) $event['points'];

				ap_insert_reputation_event(
					$event['slug'],
					$event['label'],
					$event['description'],
					$points,
					! empty( $event['activity'] ) ? $event['activity'] : '',
					! empty( $event['parent'] ) ? $event['parent'] : '',
					$event['icon']
				);
			}

			$events_db = ap_get_all_reputation_events();

			wp_cache_set( 'all', $events_db, 'ap_get_all_reputation_events' );
		}

		if ( empty( $events_db ) ) {
			return;
		}

		// Fallback for register events.
		foreach ( $events_db as $event ) {
			$args = (array) $event;

			unset( $args['slug'] );

			ap_register_reputation_event( $event->slug, $args );
		}
	}

	/**
	 * Save reputation events.
	 */
	public function ap_save_events() {
		check_ajax_referer( 'ap-save-events', '__nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$events_point = ap_isset_post_value( 'events', 'r' );
		$points       = array();

		foreach ( ap_get_reputation_events() as $slug => $event ) {
			if ( isset( $events_point[ $slug ] ) ) {
				$points[ sanitize_text_field( $slug ) ] = (int) $events_point[ $slug ];
			}
		}

		if ( ! empty( $points ) ) {
			update_option( 'anspress_reputation_events', $points );
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_attr__( 'Successfully updated reputation points!', 'anspress-question-answer' ) . '</p></div>';

		wp_die();
	}

	/**
	 * Add reputation for user for new question.
	 *
	 * @param integer  $post_id Post ID.
	 * @param \WP_Post $_post Post object.
	 * @param boolean  $updated Whether post is updated or not.
	 */
	public function newQuestion( $post_id, $_post, $updated ): void {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || $updated ) {
			return;
		}

		ap_insert_reputation( 'ask', $post_id, $_post->post_author );
	}

	/**
	 * Add reputation for new answer.
	 *
	 * @param integer  $post_id Post ID.
	 * @param \WP_Post $_post Post object.
	 * @param boolean  $updated Whether post is updated or not.
	 */
	public function newAnswer( $post_id, $_post, $updated ): void {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || $updated ) {
			return;
		}

		ap_insert_reputation( 'answer', $post_id, $_post->post_author );
	}

	/**
	 * Update reputation when a question is deleted.
	 *
	 * @param integer $post_id Post ID.
	 */
	public function trashQuestion( $post_id ) {
		if ( ! PostHelper::isQuestion( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		ap_delete_reputation( 'ask', $post_id, $post->post_author );
	}

	/**
	 * Update reputation when a question is untrashed.
	 *
	 * @param integer $postid Post ID.
	 */
	public function untrashedPost( $postid ) {
		$post = get_post( $postid );

		if ( PostHelper::isQuestion( $postid ) ) {
			ap_insert_reputation( 'ask', $postid, $post->post_author );
		} elseif ( PostHelper::isAnswer( $postid ) ) {
			ap_insert_reputation( 'answer', $postid, $post->post_author );
		}
	}

	/**
	 * Update reputation when a answer is deleted.
	 *
	 * @param integer $post_id Post ID.
	 */
	public function trashAnswer( $post_id ) {
		if ( ! PostHelper::isAnswer( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		ap_delete_reputation( 'answer', $post_id, $post->post_author );
	}

	/**
	 * Award reputation when best answer selected.
	 *
	 * @param object $_post Post object.
	 */
	public function select_answer( $_post ) {
		ap_insert_reputation( 'best_answer', $_post->ID, $_post->post_author );
		$question = get_post( $_post->post_parent );

		// Award select answer points to question author only.
		if ( get_current_user_id() === (int) $question->post_author ) {
			ap_insert_reputation( 'select_answer', $_post->ID );
		}
	}

	/**
	 * Award reputation when user get an upvote.
	 *
	 * @param object $_post Post object.
	 */
	public function unselect_answer( $_post ) {
		ap_delete_reputation( 'best_answer', $_post->ID, $_post->post_author );
		$question = get_post( $_post->post_parent );
		ap_delete_reputation( 'select_answer', $_post->ID, $question->post_author );
	}

	/**
	 * Award reputation when user receive a vote.
	 *
	 * @param VoteModel $vote Vote object.
	 */
	public function receivedVote( VoteModel $vote ) {
		if ( VoteModel::VOTE !== $vote->vote_type ) {
			return;
		}

		$_post = get_post( $vote->vote_post_id );

		// Also ignore if user is voting on their own post.
		if ( (int) $vote->vote_user_id === (int) $_post->post_author ) {
			return;
		}

		if ( 1 === $vote->vote_value ) {
			ap_insert_reputation( 'received_vote_up', $_post->ID, $_post->post_author );
			ap_insert_reputation( 'given_vote_up', $_post->ID );
		} else {
			ap_insert_reputation( 'received_vote_down', $_post->ID, $_post->post_author );
			ap_insert_reputation( 'given_vote_down', $_post->ID );
		}
	}

	/**
	 * Award reputation when user recive an up vote.
	 *
	 * @param VoteModel $vote Vote object.
	 */
	public function undoVote( VoteModel $vote ) {
		if ( VoteModel::VOTE !== $vote->vote_type ) {
			return;
		}

		$_post = get_post( $vote->vote_post_id );

		// Also ignore if user is voting on their own post.
		if ( (int) $vote->vote_user_id === (int) $_post->post_author ) {
			return;
		}

		if ( 1 === $vote->vote_value ) {
			ap_delete_reputation( 'received_vote_up', $_post->ID, $_post->post_author );
			ap_delete_reputation( 'given_vote_up', $_post->ID );
		} else {
			ap_delete_reputation( 'received_vote_down', $_post->ID, $_post->post_author );
			ap_delete_reputation( 'given_vote_down', $_post->ID );
		}
	}

	/**
	 * Award reputation on new comment.
	 *
	 * @param  int    $commentid WordPress comment id.
	 * @param  string $status  Comment status.
	 */
	public function commentStatusChange( $commentid, $status ) {
		$comment = get_comment( $commentid );

		if ( 'anspress' !== $comment->comment_type ) {
			return;
		}

		if ( in_array( $status, array( '1', 'approve' ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			ap_insert_reputation( 'comment', $comment->comment_ID, $comment->user_id );
		} else {
			ap_delete_reputation( 'comment', $comment->comment_ID, $comment->user_id );
		}
	}

	/**
	 * Delete reputation when comment is deleted.
	 *
	 * @param integer $comment Comment ID.
	 */
	public function deleteComment( $comment ) {
		$comment = get_comment( $comment );

		if ( 'anspress' !== $comment->comment_type ) {
			return;
		}

		ap_delete_reputation( 'comment', $comment->comment_ID, $comment->user_id );
	}

	/**
	 * Award reputation when user register.
	 *
	 * @param integer $user_id User Id.
	 */
	public function user_register( $user_id ) {
		ap_insert_reputation( 'register', $user_id, $user_id );
	}

	/**
	 * Delete all reputation of user when user get deleted.
	 *
	 * @param integer $user_id User ID.
	 */
	public function delete_user( $user_id ) {
		global $wpdb;
		$delete = $wpdb->delete( $wpdb->ap_reputations, array( 'rep_user_id' => $user_id ), array( '%d' ) ); // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( false !== $delete ) {
			do_action( 'ap_bulk_delete_reputations_of_user', $user_id );
		}
	}

	/**
	 * Append user reputations in display name.
	 *
	 * @param string $name User display name.
	 * @param array  $args Arguments.
	 * @return string
	 */
	public function display_name( $name, $args ) {
		if ( $args['user_id'] > 0 ) {
			if ( $args['html'] ) {
				$reputation = ap_get_user_reputation_meta( $args['user_id'] );

				if ( function_exists( 'bp_core_get_userlink' ) ) {
					return $name . '<a href="' . ap_user_link( $args['user_id'] ) . 'reputations/" class="ap-user-reputation" title="' . __( 'Reputation', 'anspress-question-answer' ) . '">' . $reputation . '</a>';
				} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
					return $name . '<a href="' . ap_user_link( $args['user_id'] ) . 'reputations/" class="ap-user-reputation" title="' . __( 'Reputation', 'anspress-question-answer' ) . '">' . $reputation . '</a>';
				}
			}
		}

		return $name;
	}

	/**
	 * Show reputation points of user in BuddyPress profile meta.
	 */
	public function bp_profile_header_meta() {
		echo wp_kses_post(
			'<span class="ap-user-meta ap-user-meta-reputation">' .
			sprintf(
				// translators: Placeholder contains reputation points.
				__( '%s Reputation', 'anspress-question-answer' ),
				ap_get_user_reputation_meta( bp_displayed_user_id() )
			) .
			'</span>'
		);
	}

	/**
	 * Adds reputations tab in AnsPress authors page.
	 */
	public function ap_user_pages() {
		anspress()->user_pages[] = array(
			'slug'  => 'reputations',
			'label' => __( 'Reputations', 'anspress-question-answer' ),
			'icon'  => 'apicon-reputation',
			'cb'    => array( $this, 'reputation_page' ),
			'order' => 5,
		);
	}

	/**
	 * Display reputation tab content in AnsPress author page.
	 */
	public function reputation_page() {
		$user_id = get_queried_object_id();

		$reputations = new \AnsPress_Reputation_Query( array( 'user_id' => $user_id ) );
		include ap_get_theme_location( 'addons/reputation/index.php' );
	}

	/**
	 * Ajax callback for loading more reputations.
	 */
	public function load_more_reputation() {
		check_admin_referer( 'load_more_reputation', '__nonce' );

		$user_id = ap_sanitize_unslash( 'user_id', 'r' );
		$paged   = ap_sanitize_unslash( 'current', 'r', 1 ) + 1;

		ob_start();
		$reputations = new \AnsPress_Reputation_Query(
			array(
				'user_id' => $user_id,
				'paged'   => $paged,
			)
		);
		while ( $reputations->have() ) :
			$reputations->the_reputation();
			include ap_get_theme_location( 'addons/reputation/item.php' );
		endwhile;
		$html = ob_get_clean();

		$paged = $reputations->total_pages > $paged ? $paged : 0;

		ap_ajax_json(
			array(
				'success' => true,
				'args'    => array(
					'ap_ajax_action' => 'load_more_reputation',
					'__nonce'        => wp_create_nonce( 'load_more_reputation' ),
					'current'        => (int) $paged,
					'user_id'        => $user_id,
				),
				'html'    => $html,
				'element' => '.ap-reputations tbody',
			)
		);
	}

	/**
	 * Add reputations nav link in BuddyPress profile.
	 *
	 * @param array $nav Nav menu.
	 * @return array
	 */
	public function ap_bp_nav( $nav ) {
		$nav[] = array(
			'name' => __( 'Reputations', 'anspress-question-answer' ),
			'slug' => 'reputations',
		);
		return $nav;
	}

	/**
	 * Add BuddyPress reputation page callback.
	 *
	 * @param array  $cb       Callback function.
	 * @param string $template Template.
	 * @return array
	 */
	public function ap_bp_page( $cb, $template ) {
		if ( 'reputations' === $template ) {
			return array( $this, 'bp_reputation_page' );
		}
		return $cb;
	}

	/**
	 * Display reputation on buddypress page.
	 *
	 * @since unknown
	 */
	public function bp_reputation_page() {
		$user_id = bp_displayed_user_id();

		$reputations = new \AnsPress_Reputation_Query( array( 'user_id' => $user_id ) );
		include ap_get_theme_location( 'addons/reputation/index.php' );
	}

	/**
	 * Add reputation events option in AnsPress options.
	 *
	 * @param array $all_options Options.
	 * @return array
	 * @since 4.1.0
	 */
	public function ap_all_options( $all_options ) {
		$all_options['reputations'] = array(
			'label'    => __( '⚙ Reputations', 'anspress-question-answer' ),
			'template' => 'reputation-events.php',
		);

		return $all_options;
	}
}
