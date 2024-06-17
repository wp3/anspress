<?php
/**
 * Answers content.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check if answer is set or not.
if ( ! isset( $args['post'] ) ) {
	throw new GeneralException( 'Post not set.' );
}

$_post = $args['post'];

$isQuestion = 'question' === $_post->post_type;

?>
<div class="anspress-apq-item">
	<div class="anspress-apq-item-avatar">
		<a href="<?php ap_profile_link(); ?>">
			<?php ap_author_avatar( ap_opt( 'avatar_size_qquestion' ) ); ?>
		</a>
	</div>
	<div class="anspress-apq-item-content">
		<div class="anspress-apq-item-qbody">
			<div class="anspress-apq-item-metas">
				<div class="anspress-apq-item-author">
					<?php
					ap_user_display_name(
						array(
							'html' => true,
							'echo' => true,
						)
					);
					?>
				</div>
				<a href="<?php the_permalink(); ?>" class="anspress-apq-item-posted">
					<?php
					$posted = 'future' === get_post_status() ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

					$time = ap_get_time( get_the_ID(), 'U' );

					if ( 'future' !== get_post_status() ) {
						$time = ap_human_time( $time );
					}
					?>
					<time itemprop="datePublished" datetime="<?php echo esc_attr( ap_get_time( get_the_ID(), 'c' ) ); ?>"><?php echo esc_attr( $time ); ?></time>
				</a>
				<span class="anspress-apq-item-ccount">
					<?php $comment_count = get_comments_number(); ?>
					<?php
						// translators: %s comments count.
						echo wp_kses_post( sprintf( _n( '%s Comment', '%s Comments', $comment_count, 'anspress-question-answer' ), '<span itemprop="commentCount">' . (int) $comment_count . '</span>' ) );
					?>
				</span>
			</div>
			<div class="anspress-apq-item-inner">
				<?php
					/**
					 * Action triggered before question content.
					 *
					 * @since   5.0.0
					 */
					do_action( 'anspress/single_question/before_content' );
				?>

				<div class="question-content" itemprop="text">
					<?php the_content(); ?>
				</div>

				<?php
					/**
					 * Action triggered after question content.
					 *
					 * @since   5.0.0
					 */
					do_action( 'anspress/single_question/after_content' );
				?>

			</div>

			<div class="anspress-apq-item-footer">
				<?php
					Plugin::loadView( 'src/frontend/single-question/vote-button.php', array( 'ID' => get_the_ID() ) );
				?>

				<?php do_action( 'ap_post_footer' ); ?>
			</div>
		</div>

		<?php
			Plugin::loadView( 'src/frontend/common/comments/render.php', array( 'post' => get_post() ) );
		?>
	</div>
</div>
