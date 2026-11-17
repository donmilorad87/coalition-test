<?php
/**
 * Breadcrumb trail tests.
 *
 * @package CtCustom
 */

use CtCustom\Breadcrumbs;

/**
 * @covers \CtCustom\Breadcrumbs
 */
class BreadcrumbsTest extends WP_UnitTestCase {

	public function test_trail_for_child_page_includes_ancestors(): void {
		$parent = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Who we are' ) );
		$child  = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Team', 'post_parent' => $parent ) );

		$trail = Breadcrumbs::trail( get_post( $child ) );

		$this->assertSame( array( 'Home', 'Who we are', 'Team' ), array_column( $trail, 'label' ) );
		$this->assertSame( home_url( '/' ), $trail[0]['url'] );
		$this->assertSame( get_permalink( $parent ), $trail[1]['url'] );
		$this->assertSame( '', $trail[2]['url'], 'Current item is not linked.' );
	}

	public function test_display_title_override_is_used_for_current_item(): void {
		$page = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Homepage' ) );
		update_post_meta( $page, Breadcrumbs::META_TITLE, 'Contact' );

		$this->assertSame( array( 'Home', 'Contact' ), array_column( Breadcrumbs::trail( get_post( $page ) ), 'label' ) );
	}

	public function test_parent_override_replaces_real_hierarchy(): void {
		$who  = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Who we are' ) );
		$page = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Homepage' ) );
		update_post_meta( $page, Breadcrumbs::META_PARENT, $who );
		update_post_meta( $page, Breadcrumbs::META_TITLE, 'Contact' );

		$this->assertSame( array( 'Home', 'Who we are', 'Contact' ), array_column( Breadcrumbs::trail( get_post( $page ) ), 'label' ) );
	}

	public function test_parent_override_pointing_to_itself_is_ignored(): void {
		$page = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Solo' ) );
		update_post_meta( $page, Breadcrumbs::META_PARENT, $page );

		$this->assertSame( array( 'Home', 'Solo' ), array_column( Breadcrumbs::trail( get_post( $page ) ), 'label' ) );
	}

	public function test_trail_without_post_is_home_only(): void {
		$this->assertSame( array( 'Home' ), array_column( Breadcrumbs::trail( null ), 'label' ) );
	}

	public function test_page_title_prefers_override(): void {
		$page = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Homepage' ) );
		$this->assertSame( 'Homepage', Breadcrumbs::page_title( get_post( $page ) ) );
		update_post_meta( $page, Breadcrumbs::META_TITLE, 'Contact' );
		$this->assertSame( 'Contact', Breadcrumbs::page_title( get_post( $page ) ) );
	}

	public function test_plain_front_page_is_home_only_and_unlinked(): void {
		$page = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Homepage' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page );

		$this->assertSame( array( array( 'label' => 'Home', 'url' => '' ) ), Breadcrumbs::trail( get_post( $page ) ) );
	}

	public function test_unpublished_parent_override_is_ignored(): void {
		$draft = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Draft', 'post_status' => 'draft' ) );
		$page  = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'Page' ) );
		update_post_meta( $page, Breadcrumbs::META_PARENT, $draft );

		$this->assertSame( array( 'Home', 'Page' ), array_column( Breadcrumbs::trail( get_post( $page ) ), 'label' ) );
	}
}
