<?php
/**
 * Single comment template.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Router;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check if $attributes is set.
if ( ! isset( $attributes ) ) {
	throw new InvalidArgumentException( 'Attributes argument is required.' );
}

if ( ! isset( $args['comment'] ) ) {
	return;
}

$postComment = $args['comment'];
?>
<anspress-comment-item data-anspress-id="comment-<?php echo (int) $postComment->comment_ID; ?>" class="anspress-comments-item" id="anspress-comment-<?php echo esc_attr( $postComment->comment_ID ); ?>" data-anspressel="comment" data-id="<?php echo (int) $postComment->comment_ID; ?>">
	<a class="anspress-comments-avatar anspress-avatar-link" href="<?php echo esc_url( get_comment_author_url( $postComment ) ); ?>">
		<?php echo get_avatar( $postComment->user_id || $postComment->comment_author_email, $attributes['commentAvatarSize'] ?? 30 ); ?>
	</a>
	<div class="anspress-comments-inner anspress-comments-form-container anspress-card">
		<div class="anspress-comments-meta">
			<div>
				<a href="<?php echo esc_url( ap_user_link( $postComment->user_id ) ); ?>" class="anspress-comments-author"><?php echo esc_html( get_comment_author( $postComment ) ); ?></a>
				<?php esc_html_e( 'commented', 'anspress-question-answer' ); ?>
				<a href="<?php echo esc_url( get_comment_link( $postComment ) ); ?>" class="anspress-apq-item-comment-posted">
					<?php
					$posted = 'future' === get_post_status( $postComment->comment_post_ID ) ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

					$time = ap_get_time( $postComment->comment_ID, 'U' );

					if ( 'future' !== get_post_status( $postComment->comment_post_ID ) ) {
						$time = ap_human_time( $time );
					}
					?>
					<time itemprop="datePublished" datetime="<?php echo esc_attr( comment_date( 'c', $postComment ) ); ?>">
						<?php
							echo esc_attr(
								sprintf(
									/* translators: %s: human-readable time difference */
									_x( '%s ago', '%s = human-readable time difference', 'anspress-question-answer' ),
									human_time_diff(
										get_comment_time( 'U', false, true, $postComment ),
										current_datetime()->getTimestamp()
									)
								)
							);
							?>
					</time>
				</a>
			</div>
			<div class="anspress-comments-actions">
				<?php if ( ap_user_can_delete_comment( $postComment->comment_ID ) ) : ?>
					<?php
						$deleteHref = Router::route(
							'v1.comments.actions',
							array(
								'comment_id' => $postComment->comment_ID,
								'action'     => 'delete-comment',
							)
						)
					?>
					<anspress-link data-href="<?php echo esc_attr( $deleteHref ); ?>" data-method="POST" class="anspress-comments-delete"><?php esc_html_e( 'Delete', 'anspress-question-answer' ); ?></anspress-link>
				<?php endif; ?>
				<?php if ( ap_user_can_edit_comment( $postComment->comment_ID ) ) : ?>
					<?php
						$editHref = Router::route(
							'v1.posts.loadCommentEditForm',
							array(
								'post_id'    => $postComment->comment_post_ID,
								'comment_id' => $postComment->comment_ID,
							)
						)
					?>
					<anspress-link data-href="<?php echo esc_attr( $editHref ); ?>" data-method="POST" href="#" class="anspress-comments-edit"><?php esc_html_e( 'Edit', 'anspress-question-answer' ); ?></anspress-link>
				<?php endif; ?>
			</div>
		</div>
		<div class="anspress-comments-content">
			<?php echo wp_kses_post( $postComment->comment_content ); ?>
		</div>

	</div>
</anspress-comment-item>
