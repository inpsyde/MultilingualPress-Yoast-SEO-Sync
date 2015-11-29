<?php # -*- coding: utf-8 -*-

namespace Multilingualpress\YoastSeoSync;

/**
 * Class SyncPostmeta
 *
 * @package Multilingualpress\YoastSeoSync
 */
class SyncPostmeta {

	/**
	 * Static string to identifier.
	 *
	 * @var string
	 */
	private $name_base = 'mlp_translation_data';

	/**
	 * Save translation data.
	 *
	 * @var string
	 */
	private $post_request_data;

	/**
	 * The current blog ID.
	 *
	 * @var int
	 */
	private $current_blog_id;

	/**
	 * Excluded post types.
	 *
	 * @var array
	 */
	private $excluded_post_types = array(
		'attachment',
		'revision',
	);

	/**
	 * Store blog IDs, there should be synced.
	 *
	 * @var array
	 */
	private $blogs_to_translate = array();

	/**
	 * SyncPostmeta constructor.
	 */
	public function __construct() {

		$this->current_blog_id = get_current_blog_id();

		/**
		 * Use this filter to change the post types to sync.
		 */
		$this->excluded_post_types = apply_filters( 'Mlp_Addon_Yoast_Seo_Excluded_Post_Types', $this->excluded_post_types );
	}

	/**
	 * Run the plugin in the WordPress environment.
	 *
	 * @wp-hook save_post
	 */
	public function run() {

		add_action( 'wp_insert_post', array( $this, 'wp_insert_post' ), 10, 3 );

		/**
		 * Grab the relevant translation data if it exists.
		 */
		if ( 'POST' === $_SERVER[ 'REQUEST_METHOD' ] ) {
			$this->post_request_data = $_POST;

			if ( isset( $this->post_request_data[ $this->name_base ] ) ) {
				foreach ( $this->post_request_data[ $this->name_base ] as $remote_blog_id => $post_data ) {

					$this->blogs_to_translate[] = $remote_blog_id;
				}
			}
		}

	}

	/**
	 * Contains the post saving logic to transfer post meta and categories.
	 *
	 * @wp-hook wp_insert_post
	 *
	 * @param          $post_ID
	 * @param \WP_Post $post
	 * @param          $update
	 */
	public function wp_insert_post( $post_ID, \WP_Post $post, $update ) {

		if ( in_array( $post->post_type, $this->excluded_post_types ) ) {
			return;
		}

		/**
		 * MLP switches blogs and saves remote posts. Check if we're still on the source blog (and dealing with the source post)
		 */
		if ( $this->current_blog_id !== get_current_blog_id() ) {
			return;
		}

		/**
		 * Now grab all linked posts. Bail if none are found
		 */
		$linked_posts = mlp_get_linked_elements( $post_ID, 'post', $this->current_blog_id );

		if ( empty( $linked_posts ) ) {
			return;
		}

		foreach ( $linked_posts as $blog_ID => $remote_post_ID ) {
			if ( $remote_post_ID === $this->current_blog_id ) {
				continue;
			}

			/**
			 * Was the copy source button used for this post?
			 */
			if ( ! in_array( $blog_ID, $this->blogs_to_translate ) ) {
				continue;
			}

			$this->sync_meta(
				$this->current_blog_id,
				$post_ID,
				$blog_ID,
				$remote_post_ID
			);

			$this->sync_terms(
				$this->current_blog_id,
				$post_ID,
				$blog_ID,
				$remote_post_ID
			);

			$this->sync_postmeta_ids(
				$this->current_blog_id,
				$post_ID,
				$blog_ID,
				$remote_post_ID
			);

		}

	}

	/**
	 * For post meta that only contains a single post id, this method will check for linked elements and set the remote
	 * post meta accordingly.
	 *
	 * @param $source_blog_id
	 * @param $source_post_id
	 * @param $target_blog_id
	 * @param $post_id
	 */
	private function sync_postmeta_ids( $source_blog_id, $source_post_id, $target_blog_id, $post_id ) {

		$original_blog_id = get_current_blog_id();

		if ( $original_blog_id !== $source_blog_id ) {
			switch_to_blog( $source_blog_id );
		}

		$meta = array(
			'_thumbnail_id'                   => 'post',
			'post_preview_image_thumbnail_id' => 'post',
			'_h24_primary_category'           => 'term',
		);

		foreach ( $meta as $key => $type ) {
			$val = get_post_meta( $source_post_id, $key, TRUE );

			$linked = mlp_get_linked_elements( $val, $type, $source_blog_id );

			if ( isset( $linked[ $target_blog_id ] ) ) {
				switch_to_blog( $target_blog_id );
				update_post_meta( $post_id, $key, $linked[ $target_blog_id ] );
				switch_to_blog( $source_blog_id );
			}
		}

		/**
		 * Return to original blog
		 */
		switch_to_blog( $original_blog_id );

	}

	/**
	 * Transfer meta data from one post/blog to another.
	 *
	 * @param $source_blog_id
	 * @param $source_post_id
	 * @param $target_blog_id
	 * @param $post_id
	 */
	private function sync_meta( $source_blog_id, $source_post_id, $target_blog_id, $post_id ) {

		$original_blog_id = get_current_blog_id();

		if ( $original_blog_id !== $source_blog_id ) {
			switch_to_blog( $source_blog_id );
		}

		$source_meta = get_post_meta( $source_post_id );

		/**
		 * Our current meta keys, there we sync on default.
		 *
		 * @see https://github.com/Yoast/wordpress-seo/blob/trunk/inc/class-wpseo-meta.php
		 */
		$sync_meta_keys = array(
			'_yoast_wpseo_focuskw',
			'_yoast_wpseo_title',
			'_yoast_wpseo_metadesc',
			'_yoast_wpseo_canonical',
			'_yoast_wpseo_linkdex',
			'_yoast_wpseo_meta-robots-noindex',
			'_yoast_wpseo_meta-robots-nofollow',
			'_yoast_wpseo_sitemap-include',
			'_yoast_wpseo_sitemap-prio',
			'_yoast_wpseo_redirect',
		);

		/**
		 * Use this filter to change, enhance the meta keys.
		 */
		$sync_meta_keys = apply_filters( 'Mlp_Addon_Yoast_Seo_Meta_Keys', $sync_meta_keys );

		switch_to_blog( $target_blog_id );
		/**
		 * Sync Meta
		 */
		foreach ( $source_meta as $key => $val ) {
			$val = $val[ 0 ];
			if ( ! in_array( $key, $sync_meta_keys ) ) {
				continue;
			}
			update_post_meta( $post_id, $key, $val );
		}
		/**
		 * Return to original blog
		 */
		switch_to_blog( $original_blog_id );
	}

	/**
	 * @todo Move to extra class, separate from postmeta sync.
	 * Collects all linked terms for a given taxonomy. The resulting array has the following structure:
	 *
	 * array(
	 *       $term_taxonomy_id => array( $blog_id => $remote_term_taxonomy_id )
	 * )
	 *
	 * @param $post_id
	 * @param $taxonomy
	 *
	 * @return array
	 */
	private function get_linked_terms( $post_id, $taxonomy ) {

		$result     = array();
		$categories = wp_get_post_terms( $post_id, $taxonomy );

		foreach ( $categories as $cat ) {
			$temp = array();

			$temp[ $this->current_blog_id ] = $cat->term_taxonomy_id;

			$linked_terms = mlp_get_linked_elements( $cat->term_taxonomy_id, 'term' );
			unset( $linked_terms[ $this->current_blog_id ] );

			foreach ( $linked_terms as $blog_id => $tt_id ) {
				$temp[ $blog_id ] = $tt_id;

			}

			$result[ $cat->term_taxonomy_id ] = $temp;
		}

		return $result;
	}

	/**
	 * @todo Move to extra class, separate from postmeta sync.
	 * Transfer terms from one post/blog to another
	 *
	 * @param $source_blog_id
	 * @param $source_post_id
	 * @param $target_blog_id
	 * @param $target_post_id
	 */
	private function sync_terms( $source_blog_id, $source_post_id, $target_blog_id, $target_post_id ) {

		$original_blog_id = get_current_blog_id();

		if ( $original_blog_id !== $source_blog_id ) {
			switch_to_blog( $source_blog_id );
		}

		$allowed_taxonomies = array( 'category' );
		/**
		 * Use this filter to change, enhance the taxonomies for synchronisation.
		 */
		$allowed_taxonomies = apply_filters( 'Mlp_Addon_Yoast_Seo_Taxonomies', $allowed_taxonomies );

		$tax_and_terms = array();
		foreach ( $allowed_taxonomies as $tax ) {
			$tax_and_terms[ $tax ] = $this->get_linked_terms( $source_post_id, $tax );
		}

		switch_to_blog( $target_blog_id );
		foreach ( $tax_and_terms as $tax => $linked_terms ) {

			if ( empty( $linked_terms ) ) {
				continue;
			}
			$remote_terms = array();
			foreach ( $linked_terms as $source_TTID => $remote_TTIDs ) {
				if ( empty( $remote_TTIDs[ $target_blog_id ] ) ) {
					continue;
				}
				$remote_terms[] = $remote_TTIDs[ $target_blog_id ];
			}
			wp_set_object_terms( $target_post_id, $remote_terms, $tax );
		}

		switch_to_blog( $original_blog_id );

	}

}
