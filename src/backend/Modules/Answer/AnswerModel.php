<?php
/**
 * A wrapper class for answer model.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractModel;
use AnsPress\Classes\AbstractSchema;
use AnsPress\Classes\Plugin;
use InvalidArgumentException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Answer model class.
 */
class AnswerModel extends AbstractModel {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	public const POST_TYPE = 'answer';

	/**
	 * Create the model's schema.
	 *
	 * @return AbstractSchema
	 */
	protected static function createSchema(): AbstractSchema {
		return Plugin::get( AnswerSchema::class );
	}

	/**
	 * Get the post type.
	 *
	 * @return string
	 */
	public static function postTypeSlug(): string {
		return apply_filters( 'anspress/answer/post_type', self::POST_TYPE );
	}

	/**
	 * Check if answer type.
	 *
	 * @param int|WP_Post $postIdOrObject Post ID or object.
	 * @return bool
	 */
	public static function isAnswer( $postIdOrObject ): bool {
		$post = get_post( $postIdOrObject );

		return self::POST_TYPE === $post->post_type;
	}

	/**
	 * Get the post type.
	 *
	 * @param int|WP_Post $postIdOrObject Post ID or object.
	 * @return bool
	 * @throws InvalidArgumentException If the given post is not a question.
	 */
	public static function isModerate( $postIdOrObject ): bool {
		$post = get_post( $postIdOrObject );

		// Check if question.
		if ( ! self::isAnswer( $post ) ) {
			throw new InvalidArgumentException( esc_attr__( 'Given post is not an answer.', 'anspress-question-answer' ) );
		}

		return 'moderate' === $post->post_status;
	}
}
