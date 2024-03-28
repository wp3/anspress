<?php

namespace AnsPress\Tests\Testcases;

trait Common {

	/**
	 * Switches between user roles.
	 *
	 * E.g. administrator, editor, author, contributor, subscriber.
	 *
	 * @param string $role The role to set.
	 */
	public function setRole( $role, $muSuperAdmin = false ) {
		$post    = $_POST;
		$user_id = $this->factory()->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );

		if ( $role === 'administrator' && $muSuperAdmin ) {
			grant_super_admin( $user_id );
		}
	}

	/**
	 * Clears login cookies, unsets the current user.
	 */
	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array( AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE );
		foreach ( $cookies as $c ) {
			unset( $_COOKIE[ $c ] );
		}
	}

	public function insert_question( $title = '', $content = '', $author = 0 ) {
		$title   = empty( $title ) ? 'Question title' : $title;
		$content = empty( $content ) ? 'Question content' : $content;

		return $this->factory()->post->create(
			array(
				'post_title'    => $title,
				'post_type'     => 'question',
				'post_status'   => 'publish',
				'post_content'  => $content,
				'post_author'   => $author,
				'post_date_gmt' => '2024-03-25 10:08:11',
			)
		);
	}

	public function insert_answer( $title = '', $content = '', $author = 0 ) {
		$title   = empty( $title ) ? 'Question title' : $title;
		$content = empty( $content ) ? 'Question content' : $content;

		$ids      = [];
		$ids['q'] = $this->insert_question();
		$ids['a'] = $this->factory()->post->create(
			array(
				'post_title'   => $title,
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => $content,
				'post_author'  => $author,
				'post_parent'  => $ids['q'],
			)
		);

		return (object) $ids;
	}

	/**
	 * Insert question along with 10 answers.
	 *
	 * @param array $q_args
	 * @param array $a_args
	 * @return array
	 */
	public function insert_answers( $q_args = [], $a_args = [], $answer_num = 1 ) {
		$ids = [
			'question' => 0,
			'answers'  => [],
		];

		$q_args = wp_parse_args(
			$q_args, array(
				'post_type'   => 'question',
				'post_status' => 'publish',
				'post_author' => 0,
			)
		);

		$ids['question'] = $this->factory()->post->create( $q_args );

		$a_args = wp_parse_args(
			$a_args, array(
				'post_type'   => 'answer',
				'post_status' => 'publish',
				'post_parent' => $ids['question'],
				'post_author' => 0,
			)
		);

		$ids['answers'] = $this->factory()->post->create_many( $answer_num, $a_args );

		return $ids;
	}
}
