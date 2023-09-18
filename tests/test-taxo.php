<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTaxo extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	public function set_up() {
		parent::set_up();
		register_taxonomy( 'question_category', array( 'question' ) );
	}

	public function tear_down() {
		unregister_taxonomy( 'question_category' );
		parent::tear_down();
	}

	/**
	 * @covers ::ap_question_have_category
	 */
	public function testAPQuestionHaveCategory() {
		$cid = $this->factory->category->create(
			array(
				'taxonomy' => 'question_category',
			)
		);
		$qid = $this->factory->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		wp_set_object_terms( $qid, array( $cid ), 'question_category' );
		$this->assertTrue( ap_question_have_category( $qid ) );
		$qid = $this->factory->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		$this->assertFalse( ap_question_have_category( $qid ) );
	}

}
