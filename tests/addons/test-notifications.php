<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonNotifications extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'notifications.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'notifications.php' );
	}

	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Notifications' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'load_options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'ap_menu_object' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'register_verbs' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'ap_user_pages' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'notification_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'trash_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'new_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'trash_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'select_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'unselect_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'new_comment' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'delete_comment' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'vote_up' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'vote_down' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'undo_vote_up' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'undo_vote_down' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'insert_reputation' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'delete_reputation' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'mark_notifications_seen' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'load_more_notifications' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'get_notifications' ) );
	}

	public function testInit() {
		$instance1 = \Anspress\Addons\Notifications::init();
		$this->assertInstanceOf( 'Anspress\Addons\Notifications', $instance1 );
		$instance2 = \Anspress\Addons\Notifications::init();
		$this->assertSame( $instance1, $instance2 );
	}

	public function testHooksFilters() {
		$instance = \Anspress\Addons\Notifications::init();
		anspress()->setup_hooks();

		// Tests.
		$this->assertEquals( 10, has_filter( 'ap_settings_menu_features_groups', [ $instance, 'add_to_settings_page' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_options_features_notification', [ $instance, 'load_options' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_menu_object', [ $instance, 'ap_menu_object' ] ) );
		$this->assertEquals( 10, has_action( 'ap_notification_verbs', [ $instance, 'register_verbs' ] ) );
		$this->assertEquals( 10, has_action( 'ap_user_pages', [ $instance, 'ap_user_pages' ] ) );
		$this->assertEquals( 10, has_action( 'ap_after_new_answer', [ $instance, 'new_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_trash_question', [ $instance, 'trash_question' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_question', [ $instance, 'trash_question' ] ) );
		$this->assertEquals( 10, has_action( 'ap_trash_answer', [ $instance, 'trash_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_answer', [ $instance, 'trash_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_untrash_answer', [ $instance, 'new_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_select_answer', [ $instance, 'select_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_unselect_answer', [ $instance, 'unselect_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_publish_comment', [ $instance, 'new_comment' ] ) );
		$this->assertEquals( 10, has_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] ) );
		$this->assertEquals( 10, has_action( 'ap_vote_up', [ $instance, 'vote_up' ] ) );
		$this->assertEquals( 10, has_action( 'ap_vote_down', [ $instance, 'vote_down' ] ) );
		$this->assertEquals( 10, has_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] ) );
		$this->assertEquals( 10, has_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] ) );
		$this->assertEquals( 10, has_action( 'ap_insert_reputation', [ $instance, 'insert_reputation' ] ) );
		$this->assertEquals( 10, has_action( 'ap_delete_reputation', [ $instance, 'delete_reputation' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_mark_notifications_seen', [ $instance, 'mark_notifications_seen' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_load_more_notifications', [ $instance, 'load_more_notifications' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_get_notifications', [ $instance, 'get_notifications' ] ) );
	}

	/**
	 * @covers Anspress\Addons\Notifications::add_to_settings_page
	 */
	public function testAddToSettingsPage() {
		$instance = \Anspress\Addons\Notifications::init();

		// Call the method.
		$groups = $instance->add_to_settings_page( [] );

		// Test if the Notification group is added to the settings page.
		$this->assertArrayHasKey( 'notification', $groups );
		$this->assertArrayHasKey( 'label', $groups['notification'] );
		$this->assertEquals( 'Notification', $groups['notification']['label'] );

		// Test by adding new group.
		$groups = $instance->add_to_settings_page( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertArrayHasKey( 'label', $groups['some_other_group'] );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'notification', $groups );
		$this->assertArrayHasKey( 'label', $groups['notification'] );
		$this->assertEquals( 'Notification', $groups['notification']['label'] );
	}

	/**
	 * @covers Anspress\Addons\Notifications::load_options
	 */
	public function testLoadOptions() {
		$instance = \Anspress\Addons\Notifications::init();

		// Add user_page_title_notifications and user_page_slug_notifications options.
		ap_add_default_options(
			array(
				'user_page_title_notifications' => __( 'Notifications', 'anspress-question-answer' ),
				'user_page_slug_notifications'  => 'notifications',
			)
		);

		// Call the method.
		$form = $instance->load_options();

		// Test begins.
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'user_page_title_notifications', $form['fields'] );
		$this->assertArrayHasKey( 'user_page_slug_notifications', $form['fields'] );

		// Test for user_page_title_notifications field.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_title_notifications'] );
		$this->assertEquals( 'Notifications page title', $form['fields']['user_page_title_notifications']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_title_notifications'] );
		$this->assertEquals( 'Custom title for user profile notifications page', $form['fields']['user_page_title_notifications']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_title_notifications'] );
		$this->assertEquals( ap_opt( 'user_page_title_notifications' ), $form['fields']['user_page_title_notifications']['value'] );

		// Test for user_page_slug_notifications field.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_slug_notifications'] );
		$this->assertEquals( 'Notifications page slug', $form['fields']['user_page_slug_notifications']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_slug_notifications'] );
		$this->assertEquals( 'Custom slug for user profile notifications page', $form['fields']['user_page_slug_notifications']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_slug_notifications'] );
		$this->assertEquals( ap_opt( 'user_page_slug_notifications' ), $form['fields']['user_page_slug_notifications']['value'] );
	}

	public function testAPNotificationAddonActivation() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ap_notifications';

		// Call the function.
		\AnsPress\Addons\ap_notification_addon_activation();

		// Test if the table is created with the correct columns.
		$columns = $wpdb->get_col( "DESCRIBE $table_name" );
		$expected_columns = [ 'noti_id', 'noti_user_id', 'noti_actor', 'noti_parent', 'noti_ref_id', 'noti_ref_type', 'noti_verb', 'noti_date', 'noti_seen' ];
		foreach ( $expected_columns as $column ) {
			$this->assertContains( $column, $columns );
		}

		// Test if the table has the expected primary key.
		$primary_key = null;
		$columns_info = $wpdb->get_results( "DESCRIBE $table_name" );
		foreach ( $columns_info as $column ) {
			if ( 'PRI' === $column->Key ) {
				$primary_key = $column->Field;
				break;
			}
		}
		$this->assertEquals( 'noti_id', $primary_key );
	}

	/**
	 * @covers Anspress\Addons\Notifications::ap_menu_object
	 */
	public function testAPMenuObject() {
		$instance = \Anspress\Addons\Notifications::init();

		// Add menu item arg.
		$menu_item = [
			(object) [
				'object' => 'notifications',
			]
		];

		// Test begins.
		// For invalid menu item passing empty array.
		$result = $instance->ap_menu_object( [] );
		$this->assertEmpty( $result );

		// For invalid menu item passing invalid values.
		$result = $instance->ap_menu_object( [ (object) [ 'object' => 'some_menu_item' ] ] );
		$this->assertNotEmpty( $result );
		foreach ( $result as $item ) {
			if ( 'some_menu_item' === $item->object ) {
				$this->assertEquals( 'some_menu_item', $item->object );
				$this->assertNotEquals( 'notifications', $item->object );
			}
		}

		// For valid menu item.
		$result = $instance->ap_menu_object( $menu_item );
		$this->assertNotEmpty( $result );
		foreach ( $result as $item ) {
			if ( 'notifications' === $item->object ) {
				$this->assertEquals( 'notifications', $item->object );
				$this->assertEquals( '#apNotifications', $item->url );
				$this->assertEquals( 'custom', $item->type );
			}
		}
	}

	/**
	 * @covers Anspress\Addons\Notifications::register_verbs
	 */
	public function testRegisterVerbs() {
		$instance = \Anspress\Addons\Notifications::init();

		// Test before calling the method.
		$verbs = ap_notification_verbs();
		$this->assertEmpty( $verbs );

		// Test after calling the method.
		$instance->register_verbs();
		$verbs = ap_notification_verbs();
		$this->assertNotEmpty( $verbs );

		// Test for new_answer verb.
		$this->assertArrayHasKey( 'new_answer', $verbs );
		$expected_array = [
			'ref_type'   => 'post',
			'label'      => 'posted an answer on your question',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected_array, $verbs['new_answer'] );

		// Test for new_comment verb.
		$this->assertArrayHasKey( 'new_comment', $verbs );
		$expected_array = [
			'ref_type'   => 'comment',
			'label'      => 'commented on your %cpt%',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected_array, $verbs['new_comment'] );

		// Test for vote_up verb.
		$this->assertArrayHasKey( 'vote_up', $verbs );
		$expected_array = [
			'ref_type'   => 'post',
			'label'      => 'up voted your %cpt%',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected_array, $verbs['vote_up'] );

		// Test for vote_down verb.
		$this->assertArrayHasKey( 'vote_down', $verbs );
		$expected_array = [
			'ref_type'   => 'post',
			'label'      => 'down voted your %cpt%',
			'hide_actor' => true,
			'icon'       => 'apicon-thumb-down',
		];
		$this->assertEquals( $expected_array, $verbs['vote_down'] );

		// Test for best_answer verb.
		$this->assertArrayHasKey( 'best_answer', $verbs );
		$expected_array = [
			'ref_type'   => 'post',
			'label'      => 'selected your answer',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected_array, $verbs['best_answer'] );

		// Test for new_points verb.
		$this->assertArrayHasKey( 'new_points', $verbs );
		$expected_array = [
			'ref_type'   => 'reputation',
			'label'      => 'You have earned %points% points',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected_array, $verbs['new_points'] );

		// Test for lost_points verb.
		$this->assertArrayHasKey( 'lost_points', $verbs );
		$expected_array = [
			'ref_type'   => 'reputation',
			'label'      => 'You lose %points% points',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected_array, $verbs['lost_points'] );
	}

	/**
	 * @covers Anspress\Addons\Notifications::ap_user_pages
	 */
	public function testAPUserPages() {
		$instance = \Anspress\Addons\Notifications::init();

		// Test begins.
		// Before calling the method.
		anspress()->user_pages = null;
		$user_pages = anspress()->user_pages;
		$this->assertNull( $user_pages );

		// After calling the method.
		$instance->ap_user_pages();
		$user_pages = anspress()->user_pages;
		$this->assertNotNull( $user_pages );
		$expected = [
			[
				'slug'    => 'notifications',
				'label'   => 'Notifications',
				'count'   => ap_count_unseen_notifications(),
				'icon'    => 'apicon-globe',
				'cb'      => array( $instance, 'notification_page' ),
				'private' => true,
			]
		];
		$this->assertEquals( $expected, $user_pages );
	}
}
