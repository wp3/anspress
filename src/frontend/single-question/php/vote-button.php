<?php
/**
 * Vote button for single question.
 *
 * @since 5.0.0
 * @package AnsPress
 */

use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\GeneralException;
use AnsPress\Modules\Vote\VoteService;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

if ( ! isset( $args['ID'] ) ) {
	throw new GeneralException( 'Post ID is required.' );
}

$voteData = Plugin::get( VoteService::class )->getPostVoteData( $args['ID'] );
$_post    = ap_get_post( $args['ID'] );
?>

<anspress-vote-button data-anspress-id="vote:<?php echo (int) $args['ID']; ?>" data-post-id="<?php echo (int) $args['ID']; ?>" data-anspressel="vote" class="anspress-apq-item-vote" data-anspress="<?php echo esc_attr( wp_json_encode( $voteData ) ); ?>">
	<button
		data-anspressel="vote-up"
		class="apicon-thumb-up anspress-apq-item-vote-up"
		<?php echo 'votedown' === $voteData['currentUserVoted'] ? 'disabled' : ''; ?>
		title="Up vote this question"
	></button>
	<span data-anspressel="votes-net-count" class="anspress-apq-item-vcount">
		<?php echo (int) $_post->votes_net; ?>
	</span>
	<?php if ( ! $disableVoteDown ?? true ) : ?>
		<button
			data-anspressel="vote-down"
			class="apicon-thumb-down anspress-apq-item-vote-down"
			<?php echo 'voteup' === $voteData['currentUserVoted'] ? 'disabled' : ''; ?>
			title="Down vote this question"
		></button>
	<?php endif; ?>
</anspress-vote-button>