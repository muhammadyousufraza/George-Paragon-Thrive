<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Theme_Comments
 */
class Thrive_Theme_Comments {

	const COMMENTS_CONTAINER_ID = 'comments';

	const COMMENTS_CONTAINER_CLASS = 'comments-area';

	const COMMENT_SUBMIT_CLASS = 'comment-form-submit';

	const DEFAULT_COMMENTS_STATE = 'logged';

	/**
	 * @var Thrive_Theme_Comments instance
	 */
	protected static $_instance = null;

	/**
	 * @return Thrive_Theme_Comments
	 */
	public static function get_instance() {

		if ( null === static::$_instance ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/**
	 * @var array Data attributes for the comments shortcode
	 */
	private $data = [];

	private function __construct() {
		if ( Thrive_Theme::is_active() ) {
			$this->hooks();
		}
	}

	/**
	 * Returns the closed comments markup for front-end
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function get_closed_comments_content_frontend( $content ) {
		if ( ! empty( $this->data['labels']['no_comments'] ) ) {
			$content = $this->data['labels']['no_comments'];
		}

		return $content;
	}

	private function hooks() {
		add_filter( 'comment_form_defaults', [ $this, 'comment_form_args' ] );

		add_filter( 'comment_form_closed_comments', [ $this, 'get_closed_comments_content_frontend' ] );

		add_filter( 'comment_reply_link', [ $this, 'comment_reply_link' ], 10, 4 );

		/* Remove inline styling for comment avatars, and add a user website URL if it exists. */
		add_filter( 'get_avatar', function ( $avatar, $comment_data ) {
			/* this filter is also called for the regular Post Author Picture, so we always check the instance type */
			if ( ! empty( $comment_data ) && is_object( $comment_data ) && $comment_data instanceof WP_Comment ) {
				$avatar = preg_replace( '/(width|height)=["\'\d%\s]+/ims', '', $avatar );

				$url   = get_comment_author_url();
				$class = 'thrive-comment-author-picture';

				if ( empty( $url ) ) {
					$avatar = TCB_Utils::wrap_content( $avatar, 'div', '', $class );
				} else {
					$avatar = TCB_Utils::wrap_content( $avatar, 'a', '', $class, [
						'href' => $url,
						'rel'  => 'external nofollow',
					] );
				}
			}

			return $avatar;
		}, 10, 2 );

		/* Open comments for posts when in the editor, so they can be edited. */
		add_filter( 'comments_open', function ( $open ) {
			return Thrive_Utils::is_inner_frame() ? true : $open;
		} );
	}

	/**
	 * Get the comments meta from the section or from the template.
	 *
	 * @return array|mixed
	 */
	public static function get_comments_meta() {
		$context = thrive_shortcodes()->get_editing_context();

		/* if we're in the section context, look for the comments data inside the section meta, else look for it inside the template meta */
		if ( $context !== null && $context['name'] === 'section' ) {
			$section = $context['args']['instance'];

			if ( $section instanceof Thrive_Section ) {
				$meta = $section->get_meta( 'comments' );
			}
		}

		if ( empty( $meta ) ) {
			$meta = apply_filters( 'comment_form_get_comments_meta', thrive_template()->comments_meta() );
		}

		/* if nothing exists, use empty arrays */
		$labels = empty( $meta['labels'] ) ? [] : $meta['labels'];
		$icons  = empty( $meta['icons'] ) ? [] : $meta['icons'];

		/* the default state must always have this value at page refresh */
		$labels['state'] = static::DEFAULT_COMMENTS_STATE;

		return [
			'labels' => $labels,
			'icons'  => $icons,
		];
	}

	/**
	 * Return comment shortcode html
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public function get_comments_template( $data ) {
		$classes = [ static::COMMENTS_CONTAINER_CLASS ];
		/**
		 * Allow other plugins to modify the post the comments are registered to
		 *
		 * Used in ThriveApprentice - Course overview page
		 */
		$modify_comments_post = apply_filters( 'thrive_theme_comments_modify_post', null );

		global $post;

		if ( $modify_comments_post instanceof WP_Post ) {
			global $thrive_theme_comments_post;
			$thrive_theme_comments_post = $post;

			$post = $modify_comments_post;
		}

		if (
			empty( $modify_comments_post ) &&
			(
				! thrive_template()->is_singular() || /* only display the comments on singular templates */
				! thrive_post()->is_element_visible( 'comments', $classes ) /* don't render / add hide classes if this isn't visible */
			)
		) {
			return '';
		}

		$this->data = static::get_comments_meta();

		$comments_content = Thrive_Shortcodes::shortcode_function_content( 'comments_template' );
		$comments_content = str_replace(
			[ 'comment-respond', 'ol class="children"' ],
			[ 'comment-respond logged state ', 'ol class="children" data-selector="#comments.comments-area ol.children"' ],
			$comments_content
		);

		if ( Thrive_Utils::is_inner_frame() || Thrive_Utils::during_ajax() ) {
			$comments_content = $this->comments_content_from_inner_frame( $comments_content );
		}

		$comments_content = $this->get_comments_error_msg( $comments_content );

		$comments_content = TCB_Utils::wrap_content(
			$comments_content,
			'div',
			static::COMMENTS_CONTAINER_ID,
			$classes,
			Thrive_Utils::create_attributes( $data )
		);

		if ( $modify_comments_post instanceof WP_Post ) {
			$post = $thrive_theme_comments_post;
		}


		/**
		 * Change comments content from the theme
		 *
		 * @param string $comments_content
		 */
		return apply_filters( 'thrive_theme_comments_content', $comments_content, $data );
	}

	/**
	 * Computes the form error messages
	 *
	 * @param $comments_content
	 *
	 * @return string
	 */
	private function get_comments_error_msg( $comments_content ) {
		$labels = empty( $this->data['labels'] ) ? [] : $this->data['labels'];

		if ( empty( $labels['error_msg'] ) ) {
			$labels['error_msg'] = json_encode( static::get_comment_form_error_labels() );
		}

		$comments_content .= sprintf( '<div class="thrive-theme-comments-error-msg" style="display: none !important;">%s</div>', $labels['error_msg'] );

		return $comments_content;
	}

	/**
	 * Return all the types of comments content(closed,visitor,logged in) for the inner frame, so they can all be customized.
	 *
	 * @param $comments_content
	 *
	 * @return string
	 */
	private function comments_content_from_inner_frame( $comments_content ) {
		/* remove wp scripts */
		$comments_content = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $comments_content );

		/* add data-selector to #respond */
		$comments_content = str_replace( 'id="respond"', 'id="respond" data-selector=".comment-respond" ', $comments_content );
		$visitor_content  = $this->get_visitor_content();
		$closed_content   = $this->get_closed_comments_content_backend();

		return $visitor_content . $closed_content . $comments_content;
	}

	/**
	 * Get the visitor state html
	 *
	 * @return mixed|string
	 */
	private function get_visitor_content() {
		/* temporarily change current user in order to get content for visitors */
		$user_id = get_current_user_id();

		wp_set_current_user( 0 );
		$visitor_content = Thrive_Shortcodes::shortcode_function_content( 'comment_form', [ '', url_to_postid( thrive_template()->url() ) ] );
		wp_set_current_user( $user_id );

		/* add some classes and selectors to the visitor content html */
		$visitor_content = str_replace(
			[
				'comment-respond',
				'id="respond"',
			],
			[
				'comment-respond visitor state ',
				'id="respond" hidden data-selector=".comment-respond" ',
			],
			$visitor_content
		);

		return $visitor_content;
	}

	/**
	 * Get closed comments state html
	 *
	 * @return string
	 */
	private function get_closed_comments_content_backend() {
		/* Get comments closed text label */
		$content = sprintf( '<div class="thrv_wrapper thrv_text_element comment-no-comment"><p>%s</p></div>', __( 'Comments are closed.', 'thrive-theme' ) );
		if ( ! empty( $this->data['labels']['no_comments'] ) ) {
			$content = $this->data['labels']['no_comments'];
		}

		$closed_content = TCB_Utils::wrap_content(
			$content,
			'div',
			'respond',
			'closed state comment-respond no-comments ',
			[
				'hidden'        => '',
				'data-selector' => '.comment-respond',
			]
		);

		return $closed_content;
	}

	/**
	 * Change default arguments for the Comments Form.
	 *
	 * @param $defaults
	 *
	 * @return array
	 */
	public function comment_form_args( $defaults ) {
		$labels = empty( $this->data['labels'] ) ? [] : $this->data['labels'];

		/* change reply title html - only opening and closing tags */
		$defaults['title_reply_before'] = '<div class="thrv_wrapper thrv_text_element tve_no_icons comment-form-reply-title-wrapper theme-comments-label" data-comments-label="replytitle" data-selector=".comment-form-reply-title-wrapper">';
		$defaults['title_reply_after']  = '</div>';

		/* Customize the reply labels */
		if ( ! empty( $labels['replytitle'] ) ) {
			$defaults['title_reply']    = $labels['replytitle'];
			$defaults['title_reply_to'] = $labels['replytitle'] . ' %s';
		} else {
			$defaults['title_reply_before'] .= '<p class="comment-form-reply-title" data-selector=".comment-form-reply-title">';
			$defaults['title_reply_after']  = '</p>' . $defaults['title_reply_after'];
		}

		/* replace the html for the submit button */
		$defaults['submit_button'] = $this->get_submit_button_html( $this->data );
		/* overwrite to remove some unused html from the submit button */
		$defaults['submit_field'] = '%1$s %2$s';

		$defaults['comment_field'] = $this->get_comment_textarea_html( $labels );
		$defaults['logged_in_as']  = $this->get_logged_in_text( $labels );

		$comment_notes                    = empty( $labels['comment_notes'] ) ? '' : $labels['comment_notes'];
		$defaults['comment_notes_before'] = $this->get_comment_notes_before( $comment_notes, $defaults['comment_notes_before'] );

		/* get the defaults for the comment input fields (email, author, etc) */
		$defaults = $this->get_comments_fields_args( $defaults, $labels );

		return $defaults;
	}

	/**
	 * Generates the comment notes label
	 *
	 * @param $label
	 * @param $comment_notes_before
	 *
	 * @return string
	 */
	private function get_comment_notes_before( $label, $comment_notes_before ) {

		if ( ! empty( $label ) ) {
			$comment_notes_before = $label;
		} else {
			$comment_notes_before = str_replace( 'p class="', 'p data-selector=".comment-form-comment-notes" class="comment-form-comment-notes ', $comment_notes_before );
		}

		return sprintf( '<div class="thrv_wrapper thrv_text_element tve_no_icons theme-comments-label" data-comments-label="comment_notes">%s</div>', $comment_notes_before );
	}

	/**
	 * Build the html for the textarea from the comment form.
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private function get_comment_textarea_html( $data ) {
		$html = TCB_Utils::wrap_content(
			'',
			'textarea',
			Thrive_Utils::is_inner_frame() ? '' : 'comment',
			'',
			[
				'placeholder'   => isset( $data['placeholder_comment'] ) ? $data['placeholder_comment'] : __( 'Comment', 'thrive-theme' ),
				'name'          => 'comment',
				'required'      => 'required',
				'data-selector' => '.comment-form-text textarea',
			]
		);

		return TCB_Utils::wrap_content( $html, 'div', '', 'comment-form-text' );
	}

	/**
	 * Generate the HTML for the submit button, including custom label, icon, and layout.
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private function get_submit_button_html( $data ) {
		$labels = empty( $data['labels'] ) ? [] : $data['labels'];
		$icons  = empty( $data['icons'] ) ? [] : $data['icons'];

		$submit_content = TCB_Utils::wrap_content(
			empty( $labels['submit_button'] ) ? '%4$s' : $labels['submit_button'],
			'button',
			Thrive_Utils::is_inner_frame() ? '' : '%2$s', //For inner frame we do not set the ID of the submit button to avoid duplicate ID warning in the console
			'%3$s tve_btn_txt theme-comments-label',
			[
				'name'                => '%1$s',
				'type'                => 'submit',
				'data-selector'       => '.' . static::COMMENT_SUBMIT_CLASS . ' .submit ',
				'data-comments-label' => 'submit_button',
			]
		);

		/**
		 * Add the button icon if exists
		 */
		$submit_content = ( empty( $icons['submit_button'] ) ? '' : $icons['submit_button'] ) . $submit_content;

		/* add the required classes and the dataset for the submit button */
		$classes = [ 'tve_no_icons', static::COMMENT_SUBMIT_CLASS ];

		if ( ! empty( $icons['submit_button'] ) ) {
			$classes[] = 'tcb-icon-display';
		}

		$attr = [
			'selector' => '.' . static::COMMENT_SUBMIT_CLASS,
		];

		$submit_content = TCB_Utils::wrap_content( $submit_content, 'div', '', implode( ' ', $classes ), Thrive_Utils::create_attributes( $attr ) );

		return $submit_content;
	}

	/**
	 * Replace parts of the default logged in text ( default is 'Logged in as X. Log out?' )
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	private function get_logged_in_text( $data ) {

		if ( empty( $data['logged_in_as'] ) ) {
			$username_shortcode = sprintf( '<span class="thrive-inline-shortcode" contenteditable="false"><span class="thrive-shortcode-content" contenteditable="false" data-attr-link_to_profile="0" data-attr-text_not_logged="Username" data-extra_key="" data-option-inline="1" data-shortcode="tcb_username_field" data-shortcode-name="Username">%s</span></span>', wp_get_current_user()->user_login );
			$logout_shortcode   = sprintf( '<a href="%s" data-shortcode-id="4" data-dynamic-link="thrive_global_shortcode_url" class="tve-dynamic-link" contenteditable="true">Logout</a>', wp_logout_url() );

			$text = sprintf( '<p>Logged in as %s. %s?</p>', $username_shortcode, $logout_shortcode );
		} else {
			$text = $data['logged_in_as'];
		}

		return sprintf( '<div class="thrv_wrapper thrv_text_element tve_no_icons theme-comments-label theme-comments-logged-in-as-wrapper" data-comments-label="logged_in_as" data-selector=".theme-comments-logged-in-as-wrapper">%s</div>', $text );
	}

	/**
	 * Modify the defaults for the fields - cookies, author, email, url
	 *
	 * @param $defaults
	 * @param $data
	 *
	 * @return array
	 */
	private function get_comments_fields_args( $defaults, $data ) {

		/* add some classes to the comment input fields (email, url, website) */
		foreach ( $defaults['fields'] as $key => $field_html ) {
			if ( 'cookies' !== $key ) {
				$field_html = str_replace(
					[
						'<input',
						'<label',
						'<p class="',
					],
					[
						'<input class="comment-form-input"',
						'<label data-comments-label="' . $key . '_field" class="comment-form-label theme-comments-label theme-c-form-field-label"',
						'<p class="comment-form-item ',
					],
					$field_html
				);

				$placeholder = '';
				if ( ! empty( $data[ 'placeholder_' . $key ] ) ) {
					$placeholder = $data[ 'placeholder_' . $key ];
				}
				$field_html = str_replace( '<input', '<input placeholder="' . $placeholder . '"', $field_html );

				if ( ! empty( $data[ $key ] ) ) {
					$field_html = str_replace( '<p class="', '<p data-css="' . $data[ $key ] . '"class="', $field_html );
				}
				/* save the modifications */
				$defaults['fields'][ $key ] = $field_html;
			}
		}

		/* add extra classes and data to the cookies comment input field */
		if ( isset( $defaults['fields']['cookies'] ) ) {
			$defaults['fields']['cookies'] = str_replace( '<label', '<label data-comments-label="cookiesconsentlabel" class="theme-comments-label comment-form-label "', $defaults['fields']['cookies'] );
		}

		/* Replace the default labels if they were changed in the editor. */
		if ( ! empty( $data['author_field'] ) ) {
			$defaults['fields']['author'] = str_replace( 'Name', $data['author_field'], $defaults['fields']['author'] );
		}

		if ( ! empty( $data['email_field'] ) ) {
			$defaults['fields']['email'] = str_replace( 'Email', $data['email_field'], $defaults['fields']['email'] );
		}

		if ( ! empty( $data['url_field'] ) ) {
			$defaults['fields']['url'] = str_replace( 'Website', $data['url_field'], $defaults['fields']['url'] );
		}

		if ( ! empty( $data['cookiesconsentlabel'] ) && ! empty( $defaults['fields']['cookies'] ) ) {
			$defaults['fields']['cookies'] = str_replace( 'Save my name, email, and website in this browser for the next time I comment.', __( $data['cookiesconsentlabel'], 'thrive-theme' ), $defaults['fields']['cookies'] );
		}

		return $defaults;
	}

	/**
	 * Change structure of comment reply button for theme editing.
	 *
	 * @param $link
	 * @param $args
	 * @param $comment
	 * @param $post
	 *
	 * @return mixed
	 */
	public function comment_reply_link( $link, $args, $comment, $post ) {
		$labels = empty( $this->data['labels'] ) ? [] : $this->data['labels'];
		$icons  = empty( $this->data['icons'] ) ? [] : $this->data['icons'];

		$layout    = isset( $labels['reply-button_layout'] ) ? $labels['reply-button_layout'] : THRIVE_THEME_BUTTON_LAYOUT_TEXT_AND_ICON;
		$icon_html = isset( $icons['reply_button'] ) ? $icons['reply_button'] : Thrive_Shortcodes::get_icon_by_name( 'icon-reply-light' );

		if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
			$reply_link = TCB_Utils::wrap_content( $args['login_text'], 'a', '', 'comment-reply-login tcb-button-link', [
				'rel'  => 'nofollow',
				'href' => esc_url( wp_login_url( get_permalink() ) ),
			] );
		} else {
			$content = $this->get_comment_reply_html( $layout, $icon_html, $labels );

			$onclick = sprintf( 'return addComment.moveForm( "%1$s-%2$s", "%2$s", "%3$s", "%4$s" )',
				$args['add_below'], $comment->comment_ID, $args['respond_id'], $post->ID
			);

			$attr = [
				'rel'        => 'nofollow',
				'href'       => esc_url( add_query_arg( 'replytocom', $comment->comment_ID, get_permalink( $post->ID ) ) ) . '#' . $args['respond_id'],
				'onclick'    => Thrive_Utils::is_inner_frame() ? '' : str_replace( '"', "'", $onclick ),
				'aria-label' => esc_attr( sprintf( $args['reply_to_text'], $comment->comment_author ) ),
			];

			$reply_link = TCB_Utils::wrap_content( $content, 'a', '', 'comment-reply-link tcb-button-link', $attr );
		}

		/* generate the classes */
		$classes = [ 'reply', THRIVE_THEME_BUTTON_CLASS ];

		/* Add global style class */
		if ( ! empty( $labels['reply-button-style'] ) ) {
			$classes[] = $labels['reply-button-style'];
		}

		/* classes for icon display logic  */
		if ( ! empty( $icon_html ) && $layout !== THRIVE_THEME_BUTTON_LAYOUT_TEXT_ONLY ) {
			$classes[] = 'tcb-with-icon';
		}

		if ( ! empty( $labels['reply_icon_flip'] ) ) {
			$classes[] = 'tcb-flip';
		}

		/* add the dataset from the shortcode attributes */
		$attr = [
			'button_layout' => $layout,
			'button-style'  => isset( $labels['reply-button-style'] ) ? $labels['reply-button-style'] : '',
			'button-size'   => isset( $labels['reply-button-size'] ) ? $labels['reply-button-size'] : '',
		];

		$classes[] = 'tcb-plain-text';

		/* wrap the link and add the classes and attributes  */
		$reply_html = TCB_Utils::wrap_content( $reply_link, 'div', '', implode( ' ', $classes ), Thrive_Utils::create_attributes( $attr ) );

		return $reply_html;
	}

	/**
	 * Generate the inner HTML for the reply button - icon + text
	 *
	 * @param $layout
	 * @param $icon
	 * @param $labels
	 *
	 * @return string
	 */
	private function get_comment_reply_html( $layout, $icon, $labels ) {
		$icon_html = '';

		if ( ! empty( $icon ) && $layout !== THRIVE_THEME_BUTTON_LAYOUT_TEXT_ONLY ) {
			$icon_html = Thrive_Utils::get_element( 'tcb-icon', [ 'icon' => $icon ], false );
		}

		$label = empty( $labels['reply_label'] ) ? __( 'Reply', 'thrive-theme' ) : $labels['reply_label'];

		if ( ! empty( $labels['reply_secondary_label'] ) ) {
			/* Build secondary text html for the reply button */
			$reply_secondary_text_html = TCB_Utils::wrap_content( $labels['reply_secondary_label'], 'span', '', 'tcb-secondary-text thrv-inline-text' );

			/* Add secondary text to the button html */
			$label = rtrim( $label, '</span>' ) . $reply_secondary_text_html . '</span>';
		}

		$text_html = Thrive_Utils::get_element( 'tcb-text', [ 'label' => $label ], false );

		return $icon_html . $text_html;
	}

	/**
	 * Get the comment form error default labels (note: they also serve as default fields).
	 *
	 * @return array
	 */
	public static function get_comment_form_error_labels() {
		return [
			'email'    => __( 'Email address invalid', 'thrive-theme' ),
			'url'      => __( 'Website address invalid', 'thrive-theme' ),
			'required' => __( 'Required field missing', 'thrive-theme' ),
		];
	}
}

/**
 * @return Thrive_Theme_Comments
 */
function thrive_theme_comments() {
	return Thrive_Theme_Comments::get_instance();
}

thrive_theme_comments();
