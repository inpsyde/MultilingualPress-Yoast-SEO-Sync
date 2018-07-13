<?php # -*- coding: utf-8 -*-

namespace MultilingualPressYoastSEOSync;

/**
 * Synchronizes Yoast SEO post metadata.
 *
 * @package MultilingualPressYoastSEOSync
 */
class Synchronizer {

	/**
	 * The current site ID.
	 *
	 * @var int
	 */
	private $current_site_id;

	/**
	 * Post types that are NOT to be synchronized.
	 *
	 * @var string[]
	 */
	private $excluded_post_types;

	/**
	 * Meta keys that are to be synchronized.
	 *
	 * @var string[]
	 */
	private $meta_keys;

	/**
	 * The IDs of sites that are to be synchronized.
	 *
	 * @var int[]
	 */
	private $site_ids;

	/**
	 * Constructor. Sets up the properties.
	 */
	public function __construct() {

		$this->current_site_id = get_current_blog_id();

		/**
		 * Filters the post types tha are NOT to be synchronized.
		 *
		 * @param string[] $excluded_post_types Post types tha are NOT to be synchronized.
		 */
		$this->excluded_post_types = (array) apply_filters( 'mlp_yoast_seo_sync_excluded_post_types', array(
			'attachment',
			'revision',
		) );

		/**
		 * Filters the Yoast SEO meta keys that are to be synchronized.
		 *
		 * @see https://github.com/Yoast/wordpress-seo/blob/trunk/inc/class-wpseo-meta.php
		 *
		 * @param string[] $meta_keys The Yoast SEO meta keys that are to be synchronized.
		 */
		$this->meta_keys = (array) apply_filters( 'mlp_yoast_seo_sync_meta_keys', array(
			'_yoast_wpseo_canonical',
			'_yoast_wpseo_focuskw',
			'_yoast_wpseo_linkdex',
			'_yoast_wpseo_meta-robots-nofollow',
			'_yoast_wpseo_meta-robots-noindex',
			'_yoast_wpseo_metadesc',
			'_yoast_wpseo_redirect',
			'_yoast_wpseo_sitemap-include',
			'_yoast_wpseo_sitemap-prio',
			'_yoast_wpseo_title',
		) );

		$this->site_ids = $this->get_site_ids();
	}

	/**
	 * Synchronizes the given post with all existing translations.
	 *
	 * @wp-hook wp_insert_post
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return bool
	 */
	public function synchronize( $post_id, \WP_Post $post ) {

		if ( in_array( $post->post_type, $this->excluded_post_types ) ) {
			return false;
		}

		// MultilingualPress switches sites and saves remote posts. Check if we're still on the source site.
		if ( $this->current_site_id !== get_current_blog_id() ) {
			return false;
		}

		$linked_posts = mlp_get_linked_elements( $post_id, 'post', $this->current_site_id );
		if ( ! $linked_posts ) {
			return false;
		}

		$source_metadata = $this->get_metadata_for_synchronization( $post_id );
		if ( ! $source_metadata ) {
			return false;
		}

		foreach ( $linked_posts as $remote_site_id => $remote_post_id ) {
			if ( $remote_post_id === $this->current_site_id ) {
				continue;
			}

			if ( ! in_array( $remote_site_id, $this->site_ids ) ) {
				continue;
			}

			$this->copy_metadata(
				$source_metadata,
				$remote_site_id,
				$remote_post_id
			);
		}

		return true;
	}

	/**
	 * Copies the given source post metadata to the given target post.
	 *
	 * @param array $source_metadata Source post metadata array.
	 * @param int   $target_site_id  Target site ID.
	 * @param int   $target_post_id  Target post ID.
	 *
	 * @return void
	 */
	public function copy_metadata( $source_metadata, $target_site_id, $target_post_id ) {

		switch_to_blog( $target_site_id );

		foreach ( $source_metadata as $meta_key => $meta_value ) {
			update_post_meta( $target_post_id, $meta_key, $meta_value );
		}

		restore_current_blog();
	}

	/**
	 * Returns the site IDs included in the $_POST superglobal.
	 *
	 * @return int[]
	 */
	private function get_site_ids() {

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return array();
		}

		$name_base = 'mlp_translation_data';

		if ( empty( $_POST[ $name_base ] ) ) {
			return array();
		}

		$site_ids = array_keys( (array) $_POST[ $name_base ] );
		$site_ids = array_map( 'intval', $site_ids );

		return $site_ids;
	}

	/**
	 * Returns the metadata of the post with the given ID that is to be synchronized.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array Key value pairs array where key is meta_key and value is meta_value.
	 */
	private function get_metadata_for_synchronization( $post_id ) {

		$metadata = get_post_meta( $post_id, '', true );
		$metadata = array_intersect_key( $metadata, array_flip($this->meta_keys) );

		$keyValueMeta = [];
        foreach ($metadata as $meta => $value) {
            if( in_array( $meta, $this->meta_keys) ) {
                $keyValueMeta[$meta] = $value[0];
            }
        }

		return $keyValueMeta;
	}
}
