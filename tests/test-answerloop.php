<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnswerLoop extends TestCase {

	use TestCases\Common;

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Answers_Query' );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Answers_Query', '__construct' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'get_answers' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'next_answer' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'reset_next' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'the_answer' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'have_answers' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'rewind_answers' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'is_main_query' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'reset_answers_data' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'get_ids' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'pre_fetch' ) );
	}

	/**
	 * @covers ::ap_answer_status
	 */
	public function testAPAnswerStatus() {
		// Test on publish post status.
		$id = $this->insert_answer();
		$this->assertNull( ap_answer_status( $id->a ) );

		// Test on other post statuses.
		$q_id = $this->insert_question();

		// Moderate post status.
		$a1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_parent'  => $q_id,
			)
		);
		ob_start();
		ap_answer_status( $a1_id );
		$moderate_post_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status moderate">Moderate</span>', $moderate_post_status );

		// Private post post status.
		$a2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $q_id,
			)
		);
		ob_start();
		ap_answer_status( $a2_id );
		$private_post_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status private_post">Private</span>', $private_post_status );

		// Future post status.
		$a3_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'future',
				'post_parent'  => $q_id,
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		ob_start();
		ap_answer_status( $a3_id );
		$future_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status future">Scheduled</span>', $future_status );

		// Draft post status.
		$a4_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'draft',
				'post_parent'  => $q_id,
			)
		);
		ob_start();
		ap_answer_status( $a4_id );
		$draft_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status draft">Draft</span>', $draft_status );

		// Pending review post status.
		$a5_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'pending',
				'post_parent'  => $q_id,
			)
		);
		ob_start();
		ap_answer_status( $a5_id );
		$pending_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status pending">Pending</span>', $pending_status );
	}

	/**
	 * @covers ::ap_count_published_answers
	 */
	public function testAPCountPublishedAnswers() {
		// Test for empty answers.
		$id = $this->insert_question();
		$this->assertEquals( 0, ap_count_published_answers( $id ) );

		// Test for only 1 answer published.
		$id = $this->insert_answer();
		$this->assertEquals( 1, ap_count_published_answers( $id->q ) );

		// Test for many answers published.
		$id = $this->insert_answers( [], [], 5 );
		$this->assertEquals( 5, ap_count_published_answers( $id['question'] ) );

		// Test for additional answers published.
		$a1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 6, ap_count_published_answers( $id['question'] ) );
		$a2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 7, ap_count_published_answers( $id['question'] ) );

		// Test on all post status.
		$q_id = $this->insert_question();
		$this->assertEquals( 0, ap_count_published_answers( $q_id ) );
		$a1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
			)
		);
		$this->assertEquals( 1, ap_count_published_answers( $q_id ) );
		$a2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'private_post',
			)
		);
		$this->assertEquals( 1, ap_count_published_answers( $q_id ) );
		$a3_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'trash',
			)
		);
		$this->assertEquals( 1, ap_count_published_answers( $q_id ) );
		$a4_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'draft',
			)
		);
		$this->assertEquals( 1, ap_count_published_answers( $q_id ) );
		$a5_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'publish',
			)
		);
		$this->assertEquals( 2, ap_count_published_answers( $q_id ) );
		$a6_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertEquals( 2, ap_count_published_answers( $q_id ) );
		$a7_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'pending',
			)
		);
		$this->assertEquals( 2, ap_count_published_answers( $q_id ) );
		$a8_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$this->assertEquals( 2, ap_count_published_answers( $q_id ) );
		$a9_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'publish',
			)
		);
		$this->assertEquals( 3, ap_count_published_answers( $q_id ) );
		$a10_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
			)
		);
		$this->assertEquals( 4, ap_count_published_answers( $q_id ) );
	}

	/**
	 * @covers ::ap_count_other_answer
	 */
	public function testAPCountOtherAnswer() {
		// Test for empty answers.
		$id = $this->insert_question();
		$this->assertEquals( 0, ap_count_other_answer( $id ) );

		// Test for only 1 answer on the question.
		$id = $this->insert_answer();
		$this->assertEquals( 1, ap_count_other_answer( $id->q ) );

		// Test for many answers.
		$id = $this->insert_answers( [], [], 8 );
		$this->assertEquals( 8, ap_count_other_answer( $id['question'] ) );

		// Test for additional answers.
		$a1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 9, ap_count_other_answer( $id['question'] ) );
		$a2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 10, ap_count_other_answer( $id['question'] ) );

		// Test on all post status.
		$q_id = $this->insert_question();
		$this->assertEquals( 0, ap_count_other_answer( $q_id ) );
		$a1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
			)
		);
		$this->assertEquals( 1, ap_count_other_answer( $q_id ) );
		$a2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'private_post',
			)
		);
		$this->assertEquals( 1, ap_count_other_answer( $q_id ) );
		$a3_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertEquals( 1, ap_count_other_answer( $q_id ) );
		$a4_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'publish',
			)
		);
		$this->assertEquals( 2, ap_count_other_answer( $q_id ) );

		// Test after the question have a selected answer.
		$id = $this->insert_answer();
		$this->assertEquals( 1, ap_count_other_answer( $id->q ) );
		$a1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id->q,
			)
		);
		$a2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id->q,
			)
		);
		$this->assertEquals( 3, ap_count_other_answer( $id->q ) );
		ap_set_selected_answer( $id->q, $a1_id );
		$this->assertNotEquals( 3, ap_count_other_answer( $id->q ) );
		$this->assertEquals( 2, ap_count_other_answer( $q_id ) );
	}
}
