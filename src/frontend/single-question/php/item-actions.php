<?php
/**
 * Item actions.
 *
 * @package AnsPress
 * @subpackage SingleQuestionBlock
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check for required variable $post.
if ( ! isset( $post ) ) {
	throw new InvalidArgumentException( 'Post argument is required.', 'anspress-question-answer' );
}

// Check for required variable $attributes.
if ( ! isset( $attributes ) ) {
	throw new InvalidArgumentException( 'Attributes argument is required.', 'anspress-question-answer' );
}


?>
<div class="anspress-apq-item-actions">
	<?php
	/**
	 * Action triggered before item actions.
	 *
	 * @since   5.0.0
	 */

	do_action( 'anspress/single_question/before_item_actions' );

	$actions = array(
		'close',
		'feature',
		'delete',
		'edit',
		'report',
		'moderate',
		'private',
		'publish',
		'link',
	);

	foreach ( $actions as $actionKey ) {
		Plugin::loadView(
			"src/frontend/single-question/php/button-{$actionKey}.php",
			array(
				'post'       => $post,
				'attributes' => $attributes,
			)
		);
	}
	?>


	<?php

	/**
	 * Action triggered after item actions.
	 *
	 * @since   5.0.0
	 */
	do_action( 'anspress/single_question/after_item_actions' );
	?>
</div>