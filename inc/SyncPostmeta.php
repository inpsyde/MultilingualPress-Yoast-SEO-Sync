<?php # -*- coding: utf-8 -*-

namespace Home24\SyncPostmeta;

class SyncPostmeta {

	/**
	 * @var Store MLP Source Data
	 */
	private $source_data;

	private $name_base = 'mlp_translation_data';

	/**
	 * @var string
	 */
	private $flag_option_key = 'copy_source_used';

	private $post_request_data;

	private $current_blog_id;

	/**
	 * @var array
	 */
	private $excluded_post_types = array(
		'attachment',
		'revision'
	);

	/**
	 * @var array
	 */
	private $blogs_to_translate = array();

	public function __construct() {

		$this->current_blog_id = get_current_blog_id();

	}

	/**
	 * @wp-hook save_post
	 */
	public function run() {

		/**
		 * Add a fallback 'copy source button' flag if the translate attachment plugin is not running
		 */
		if ( ! \is_plugin_active( 'home24-translate-attachments/home24-translate-attachments.php' ) ) {
			add_action( 'mlp_translation_meta_box_top', array( $this, 'add_button_flag' ), 10, 3 );
			add_action( 'admin_footer', array( $this, 'add_copy_button_js' ) );
		}

		add_action( 'wp_insert_post', array( $this, 'wp_insert_post' ), 10, 3 );

		/**
		 * Grab the relevant translation data if it exists
		 */
		if ( 'POST' === $_SERVER[ 'REQUEST_METHOD' ] ) {
			$this->post_request_data = $_POST;

			if ( isset( $this->post_request_data[ $this->name_base ] ) ) {
				foreach ( $this->post_request_data[ $this->name_base ] as $remote_blog_id => $post_data ) {

					if ( $post_data[ $this->flag_option_key ] == '1' ) {
						$this->blogs_to_translate[] = $remote_blog_id;
					}
				}
			}
		}

	}

	/**
	 * Contains the post saving logic to transfer post meta and categories
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
			if ( $remote_post_ID == $this->current_blog_id ) {
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
	 * post meta accordingly
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
			'_h24_primary_category'           => 'term'
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
	 * Transfer meta data from one post/blog to another
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
		 * Should we decide to reuse this code, this would have to be filterable
		 */
		$sync_meta_keys = array(
			'_h24_vertical_sku_list',
			'_h24_horizontal_sku_list',
			'_h24_vertical_list_number',
			'_h24_horizontal_list_number',
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
		$tax_and_terms      = array();
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

	/**
	 * Adds a hidden input that we can use to determine if the "Copy Source" button has been pressed
	 *
	 * @param $post
	 * @param $remote_blog_id
	 * @param $remote_post
	 */
	public function add_button_flag( $post, $remote_blog_id, $remote_post ) {

		?>
		<input
			class="h24_copy_source_flag"
			name="<?php echo $this->name_base ?>[<?php echo $remote_blog_id ?>][<?php echo $this->flag_option_key ?>]"
			type="hidden"
			value="0"
			>
		<?php
	}

	public function add_copy_button_js() {

		?>
		<script>
			jQuery( document ).ready( function( $ ) {

				$( '.mlp_copy_button' ).on( 'click', function( e ) {
					var $this = $( this );
					var blogId = $this.data( 'blog_id' );
					$( 'input[name="mlp_translation_data[' + blogId + '][copy_source_used]"]' ).val( '1' );
				} );

			} );
		</script>
		<?php
	}

}
