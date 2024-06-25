<?php
/**
 * Answer policy class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractPolicy;
use WP_User;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Answer policy class.
 */
class AnswerPolicy extends AbstractPolicy {
	public const POLICY_NAME = 'answer';

	/**
	 * Ability list.
	 *
	 * @var array
	 */
	protected array $abilities = array(
		'list'   => array(
			'question',
		),
		'view'   => array(
			'answer',
		),
		'create' => array(
			'question',
		),
		'update' => array(
			'answer',
		),
		'delete' => array(
			'answer',
		),
	);

	/**
	 * Perform pre-authorization checks before any specific policy method.
	 *
	 * This method can be used to implement global checks that apply to all actions.
	 * Returning a non-null value will bypass the specific policy checks.
	 *
	 * @param string       $ability The ability being checked (e.g., 'view', 'create').
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The context of the ability.
	 * @return bool|null Null to proceed to specific policy method, or a boolean to override.
	 */
	public function before( string $ability, ?WP_User $user, array $context = array() ): ?bool {
		if ( $user && $user->has_cap( 'manage_options' ) ) {
			return true;
		}

		return null;
	}

	/**
	 * Determine if the given user can view the specified model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being viewed.
	 * @return bool True if the user is authorized to view the model, false otherwise.
	 */
	public function view( ?WP_User $user, array $context = array() ): bool {
		if ( $user && ! empty( $context['answer'] ) && is_object( $context['answer'] ) && $context['answer']->user_id === $user->user_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can create a new model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The context of the ability.
	 * @return bool True if the user is authorized to create the model, false otherwise.
	 */
	public function create( ?WP_User $user, array $context = array() ): bool {
		if ( ! $user ) {
			return false;
		}

		if ( empty( $context['question'] ) || empty( $context['question']?->ID ) ) {
			return false;
		}

		// Allow author to answer the question.
		if ( $context['question']->post_author === $user->ID ) {
			return true;
		}

		// Allow user to answer the question if it is not closed.
		if ( 'closed' !== get_post_status( $context['question']->ID ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can update the specified model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being updated.
	 * @return bool True if the user is authorized to update the model, false otherwise.
	 */
	public function update( ?WP_User $user, array $context ): bool {
		if ( $user && ! empty( $context['comment'] ) && is_object( $context['comment'] ) && $context['comment']->user_id === $user->user_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can delete the specified model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being deleted.
	 * @return bool True if the user is authorized to delete the model, false otherwise.
	 */
	public function delete( ?WP_User $user, array $context ): bool {
		if ( $user && ! empty( $context['comment'] ) && is_object( $context['comment'] ) && $context['comment']->user_id === $user->user_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can list the commenta.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being listed.
	 * @return bool True if the user is authorized to list the model, false otherwise.
	 */
	public function list( ?WP_User $user, array $context ): bool {
		if ( empty( $context['post'] ) ) {
			return false;
		}

		if ( $user && $context['post']->post_author === $user->ID ) {
			return true;
		}

		// If post not published then only author can view the post.
		if ( 'publish' === $context['post']->post_status ) {
			return true;
		}

		return false;
	}
}