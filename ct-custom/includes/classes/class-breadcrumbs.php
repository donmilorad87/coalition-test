<?php
/**
 * Breadcrumb trail + per-page title/parent overrides.
 *
 * @package CtCustom
 */

namespace CtCustom;

use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Builds breadcrumbs from the page hierarchy. Two page meta fields (editable in the block editor sidebar)
 * let editors change the displayed title and the breadcrumb parent independently of the real hierarchy.
 */
final class Breadcrumbs {

	public const META_TITLE  = 'ct_display_title';
	public const META_PARENT = 'ct_breadcrumb_parent';

	/**
	 * Register hooks.
	 */
	public static function start(): void {
		add_action( 'init', array( self::class, 'register_meta' ) );
	}

	/**
	 * Expose the override fields to the block editor.
	 */
	public static function register_meta(): void {
		$auth = static fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id );

		register_post_meta(
			'page',
			self::META_TITLE,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $auth,
			)
		);
		register_post_meta(
			'page',
			self::META_PARENT,
			array(
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => $auth,
			)
		);
	}

	/**
	 * Heading shown on the page: the override when set, else the post title.
	 *
	 * @param WP_Post $post Page.
	 */
	public static function page_title( WP_Post $post ): string {
		$override = (string) get_post_meta( $post->ID, self::META_TITLE, true );
		return '' !== $override ? $override : get_the_title( $post );
	}

	/**
	 * Trail items, root first. The last item (current page) has an empty url.
	 *
	 * @param WP_Post|null $post Current post.
	 * @return array<int, array{label: string, url: string}>
	 */
	public static function trail( ?WP_Post $post ): array {
		$home = array(
			'label' => __( 'Home', 'ct-custom' ),
			'url'   => home_url( '/' ),
		);
		if ( ! $post ) {
			return array( $home );
		}

		$parent_id = self::parent_override( $post );
		$is_front  = (int) get_option( 'page_on_front' ) === $post->ID;

		// The plain front page is "Home" itself.
		if ( $is_front && ! $parent_id && '' === (string) get_post_meta( $post->ID, self::META_TITLE, true ) ) {
			return array(
				array(
					'label' => $home['label'],
					'url'   => '',
				),
			);
		}

		$ancestors = $parent_id
			? array_merge( array( $parent_id ), get_post_ancestors( $parent_id ) )
			: get_post_ancestors( $post );

		$trail = array( $home );
		foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
			$trail[] = array(
				'label' => get_the_title( $ancestor_id ),
				'url'   => (string) get_permalink( $ancestor_id ),
			);
		}
		$trail[] = array(
			'label' => self::page_title( $post ),
			'url'   => '',
		);

		return $trail;
	}

	/**
	 * Valid parent override (published page, not the page itself), else 0.
	 *
	 * @param WP_Post $post Page.
	 */
	private static function parent_override( WP_Post $post ): int {
		$parent_id = (int) get_post_meta( $post->ID, self::META_PARENT, true );
		if ( $parent_id <= 0 || $parent_id === $post->ID ) {
			return 0;
		}
		$parent = get_post( $parent_id );
		return ( $parent && 'publish' === $parent->post_status ) ? $parent_id : 0;
	}
}
