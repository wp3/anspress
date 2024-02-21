<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldTags extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Tags' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'sanitize_cb_args' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'get_options' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'unsafe_value' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::sanitize_cb_args
	 */
	public function testSanitizeCbArgs() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test', $field->args ], $result );

		// Test 2.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'label'     => 'Test Label',
			'array_max' => 5,
			'array_min' => 2,
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test_value', $field->args ], $result );

		// Test 3.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'array_max' => 5,
			'array_min' => 2,
			'options'   => 'terms',
			'js_options' => [],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test_value', $field->args ], $result );

		// Test 4.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'array_max' => 5,
			'array_min' => 2,
			'options'   => 'terms',
			'js_options' => [
				'maxItems' => 5,
				'form'     => 'Sample Form',
				'id'       => 'sample-form',
				'field'    => 'sample-form',
				'nonce'    => wp_create_nonce( 'tags_Sample Formsample-form' ),
				'create'   => false,
				'labelAdd' => 'Add',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, [ 'new_value' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( [ [ 'new_value' ], $field->args ], $result );
	}
}
