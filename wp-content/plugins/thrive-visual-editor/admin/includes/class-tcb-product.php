<?php

/**
 * Created by PhpStorm.
 * User: Danut
 * Date: 12/9/2015
 * Time: 12:21 PM
 */
class TCB_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'tcb';

	protected $version = TVE_VERSION;

	protected $slug = 'thrive-visual-editor';

	protected $title = 'Thrive Architect';

	protected $productIds = [];

	protected $type = 'plugin';

	protected $needs_architect = true;

	/**
	 * Whether or not the current user can open the architect editor based on the current request
	 * e.g.
	 * editing a TL form and having TL access
	 * etc
	 *
	 * @param int $post_id if want to check if the current user can edit the current post
	 *
	 * @return  bool
	 */
	public static function has_external_access( $post_id = null ) {
		$has_external_access = true;

		if ( $post_id ) {
			$has_external_access = current_user_can( 'edit_post', $post_id );

			if ( $has_external_access && isset( $_REQUEST['tar_editor_page'] ) && (int) $_REQUEST['tar_editor_page'] === 1 ) {
				/* other plugins ( TL, TA, TU ) check post-related info on the 'tcb_user_has_plugin_edit_cap' hook, so we should setup the global post for them */
				global $post;

				$post = get_post( $post_id );
				setup_postdata( $post );
			}
		}

		/**
		 * If Architect and plugin or just the plugin can't be used the post isn't available to edit
		 */
		return $has_external_access && apply_filters( 'tcb_user_has_plugin_edit_cap', static::has_access() );
	}

	/**
	 * Whether or not the current user can edit current post and has TAr access
	 *
	 * @param int $post_id current post id
	 *
	 * @return bool has access or not
	 */
	public static function has_post_access( $post_id ) {
		return current_user_can( 'edit_post', $post_id ) && apply_filters( 'tcb_user_has_post_access', static::has_access() );
	}

	public function __construct( $data = [] ) {
		parent::__construct( $data );

		$this->logoUrl      = tve_editor_css( 'images/thrive-architect-logo.png' );
		$this->logoUrlWhite = tve_editor_css( 'images/thrive-architect-logo-white.png' );

		$this->description = __( 'Create beautiful content & conversion optimized landing pages.', 'thrive-cb' );

		$this->button = array(
			'label'   => __( 'View Video Tutorial', 'thrive-cb' ),
			'data-source' => 'NgZO13Am6XA',
			'active'  => true,
			'classes' => 'tvd-open-video',
		);

		$this->moreLinks = array(
			'tutorials' => array(
				'class'      => 'tve-leads-tutorials',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-architect-tutorials/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', 'thrive-cb' ),
			),
			'support'   => array(
				'class'      => 'tve-leads-tutorials',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/support/',
				'target'     => '_blank',
				'text'       => __( 'Support', 'thrive-cb' ),
			),
		);
	}

	/**
	 * Reset all TCB data
	 *
	 * @return bool|void
	 */
	public static function reset_plugin() {

		$query = new WP_Query( array(
				'post_type'      => array(
					'tcb_lightbox',
					TCB_CT_POST_TYPE,
					TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
					\TCB\inc\helpers\FormSettings::POST_TYPE,
					\TCB\UserTemplates\Template::get_post_type_name(),
					\TCB\SavedLandingPages\Saved_Lp::get_post_type_name(),
				),
				'fields'         => 'ids',
				'posts_per_page' => '-1',
			)
		);

		$post_ids = $query->posts;
		foreach ( $post_ids as $id ) {
			wp_delete_post( $id, true );
		}

		$options = [
			'tve_display_save_notification',
			'tve_social_fb_app_id',
			'tve_comments_disqus_shortname',
			'tve_comments_facebook_admins',
			'tve_fa_kit',
			TCB\UserTemplates\Template::OPTION_KEY,
		];

		foreach ( $options as $option ) {
			delete_option( $option );
		}
		delete_user_option( get_current_user_id(), 'tcb_pinned_elements' );

	}
}
