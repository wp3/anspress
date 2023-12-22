<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressForm extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form' );
		$this->assertTrue( $class->hasProperty( 'form_name' ) && $class->getProperty( 'form_name' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'fields' ) && $class->getProperty( 'fields' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'prepared' ) && $class->getProperty( 'prepared' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'errors' ) && $class->getProperty( 'errors' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'values' ) && $class->getProperty( 'values' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'editing' ) && $class->getProperty( 'editing' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'editing_id' ) && $class->getProperty( 'editing_id' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'submitted' ) && $class->getProperty( 'submitted' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'after_form' ) && $class->getProperty( 'after_form' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'generate_fields' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'generate' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'is_submitted' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'find' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'add_error' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'have_errors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'get' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'add_field' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'sanitize_validate' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'get_fields_errors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'field_values' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'get_values' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'after_save' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'set_values' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'save_values_session' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'delete_values_session' ) );
	}
}
