<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden..
}

/**
 * Class TCM_Constants
 */
class Thrive_Comments_Constants {

	/**
	 * Translate domain string to be used in translation functions
	 */
	const T = 'thrive-comments';

	/**
	 * TCM plugin version
	 */
	const PLUGIN_VERSION = '2.28';

	/**
	 * Thrive Comments Min Required WordPress Version
	 */
	const TCM_MIN_REQUIRED_WP_VERSION = '4.7.0';

	/**
	 * Thrive Comments plugin basename
	 */
	const PLUGIN_BASENAME = 'thrive-comments/thrive-comments.php';
	/**
	 * Database version for current TCM version
	 */
	const DB_VERSION = '1.0.1';

	/**
	 * Database prefix for all TCM tables
	 */
	const DB_PREFIX = 'tcm_';

	/**
	 * Define namespace for the rest endpoints
	 */
	const TCM_REST_NAMESPACE = 'tcm/v1';

	/**
	 * Define notification toast timeout
	 */
	const TCM_TOAST_TIMEOUT = 4000;

	/**
	 * Define if the plugin is ready
	 */
	const TCM_PLUGIN_READY_OPTION = 'tcm_plugin_ready';

	/**
	 * Thrive Comments admin dashboard hook
	 */
	const TCM_ADMIN_DASHBOARD_HOOK = 'thrive-dashboard_page_tcm_admin_dashboard';

	/**
	 * Thrive Comments admin moderation hook
	 */
	const TCM_ADMIN_MODERATION_HOOK = 'comments_page_tcm_comment_moderation';

	/**
	 * Comment avatar size
	 */
	const AVATAR_SIZE = 96;

	/**
	 * Inner frame flag
	 */
	const TCM_FRAME = 'tcm_preview';

	/**
	 * Default sorting for comments
	 */
	const DEFAULT_SORT = 'top_rated';

	/**
	 * Default avatar picture
	 */
	const TCM_DEFAULT_PICTURE = 'placeholder_avatar_icon.svg';

	/**
	 * Define the option name from options table
	 */
	const TCM_DEFAULT_PICTURE_OPTION = 'tcm_default_picture';

	/**
	 * Default design constant to be used throughout the code
	 */
	const TCM_DESIGN_DEFAULT = 1;

	/**
	 * Dark design constant to be used throughout the code
	 */
	const TCM_DESIGN_DARK = 'tcm_design_dark';

	/**
	 * Light design constant to be used throughout the code
	 */
	const TCM_DESIGN_LIGHT = 'tcm_design_light';

	/**
	 * Show realtive date for comments
	 */
	const TCM_RELATIVE_DATE = 'tcm_relative_date';

	/**
	 * Show absoulte date for comments
	 */
	const TCM_ABSOLUTE_DATE = '1';

	/**
	 * Hide date for comments
	 */
	const TCM_HIDE_DATE = 'tcm_hide_date';

	/**
	 * Comments nesting level
	 */
	const TCM_MAX_NESTING_LEVEL = 3;

	/**
	 * Do the general ajax from dashboard
	 */
	const TCM_AJAX_DASH = 'tcm_ajax_dash';

	/**
	 * Conversion live update setting
	 */
	const TCM_LIVE_UPDATE = 'tcm_live_update';

	/**
	 * Conversion social share setting
	 */
	const TCM_SOCIAL_SHARE = 'tcm_social_share';

	/**
	 * Conversion related posts setting
	 */
	const TCM_RELATED_POSTS = 'tcm_related_posts';

	/**
	 * Conversion redirect setting
	 */
	const TCM_REDIRECT = 'tcm_redirect';

	/**
	 * Conversion thrivebox setting
	 */
	const TCM_THRIVEBOX = 'tcm_thrivebox';

	/**
	 * Number of related posts to be show at comment conversion
	 */
	const TCM_NO_RELATED_POSTS = 4;

	/**
	 * Thrive Comments Featured meta
	 */
	const TCM_FEATURED = 'tcm_featured';

	/**
	 * Thrive Comments Delegate meta
	 */
	const TCM_DELEGATE = 'tcm_delegate';

	/**
	 * Thrive Comments Delegate meta
	 */
	const TCM_DELEGATE_AUTHOR = 'tcm_delegate_author';

	/**
	 * Thrive Comments Needs Reply meta
	 */
	const TCM_NEEDS_REPLY = 'tcm_needs_reply';

	/**
	 * Thrive Comments Unreplied
	 */
	const TCM_UNREPLIED = 'tcm_unreplied';

	/**
	 * Thrive Comments status approve
	 */
	const TCM_APPROVE = 'approved';

	/**
	 * Thrive Comments status spam
	 */
	const TCM_SPAM = 'spam';

	/**
	 * Thrive Comments status unapproved / on hold
	 */
	const TCM_UNAPPROVE = 'hold';

	/**
	 * Thrive Comment unspam a comment
	 */
	const TCM_UNSPAM = 'unspam';

	/**
	 * Thrive Comments comment trash status
	 */
	const TCM_TRASH = 'trash';

	/**
	 * Thrive Comments comment untrash status
	 */
	const TCM_UNTRASH = 'untrash';

	/**
	 * Thrive Comments value for featured comment
	 */
	const TCM_FEATURE_VALUE = 1;

	/**
	 * Thrive Comments value for NOT featured comment
	 */
	const TCM_NOT_FEATURE_VALUE = 0;

	/**
	 * Thrive Comments keyboard tooltip visibility
	 */
	const TCM_KEYBOARD_TOOLTIP = 'tcm_display_keyboard_notification_tooltip';

	/**
	 * Thrive Comments labels option for customize and translate from admin ( Advanced Settings )
	 */
	const TCM_LABELS_KEY = 'tcm_labels_option';

	/**
	 * Thrive Comments accent color db name
	 */
	const TCM_ACCENT_COLOR = 'tcm_color_picker_value';


	/**
	 * Default labels from the plugin
	 *
	 * @var array
	 */
	public static $tcm_default_labels = array(
		'number_of_comments'  => '{number_of_comments} comments',
		'show_comments_first' => '{dropdown_option} comments first',
		'newest'              => 'Newest',
		'oldest'              => 'Oldest',
		'top_rated'           => 'Top rated',
		'enter_comment'       => 'Enter your comment...',
		'load_comments'       => 'Load more comments',
		'add_comment'         => 'Add your comment...',
		'reply_to_user'       => 'Reply to {username}',

		'commenting_as'  => 'Commenting as {username}',
		'social_account' => 'Log in with:',
		'guest_comment'  => 'Comment as a guest:',
		'name'           => 'Name',
		'email'          => 'Email (not displayed publicly)',
		'website'        => 'Website',
		'submit_comment' => 'Submit comment',

		'vote'     => 'Vote:',
		'share'    => 'Share',
		'copy_url' => 'Copy link to comment',

		'close_comments' => 'Comments are closed',
		'email_address'  => 'Email address',
		'subscribe'      => 'Subscribe to comments',
		'unsubscribe'    => 'Unsubscribe',

		'logout_change'    => 'Logout/Change',
		'login_on_website' => 'Login on website',
		'signin_facebook'  => 'Sign in with Facebook',
		'signin_google'    => 'Sign in with Google',

		'comment_content_missing'   => 'Please add your comment text in the field below',
		'author_name_required'      => 'Please add your name before submitting the comment',
		'isRequired'                => 'Please enter a valid email address',
		'need_register'             => 'The comment could not be saved. You must be registered in order to comment',
		'login_submit_comment'      => 'You need to be logged in to submit a comment',
		'comment_duplicate'         => 'Duplicate comment detected, it looks as though you\'ve already said that!',
		'comment_flood'             => 'You are posting comments too quickly. Slow down.',
		'spam_comment'              => 'Your comment was marked as spam',
		'rest_cookie_invalid_nonce' => 'Cookie nonce is invalid',
		'tcm_receive_notifications' => 'Notify me when someone replies to my comment',
		'remember_me'               => 'Save the details above in this browser for the next time I comment',
		'storing_consent'           => 'By using this form you agree with the storage and handling of your data by this website',
	);

	/**
	 * Additional labels from the plugin
	 *
	 * Any new label should be added here, not in the default ones.
	 *
	 * @var array
	 */
	public static $tcm_additional_labels = array();

	/**
	 * Default labels for email notification
	 *
	 * @var array
	 */
	public static $tcm_default_notification_labels = array(
		'email_subject'    => 'New reply to your comment {comment_start}',
		'content_title'    => 'A reply to your comment was posted on {site_title}',
		'comment_posted'   => 'Comment posted on {site_title}',
		'reply_to'         => 'Reply to {source_commenter_name}',
		'signed_up'        => 'You are signed up to be notified of replies to your comment on {source_page}',
		'unsubscribe'      => 'You can {unsubscribe_link} from these notifications. Please note that this will unsubscribe you only from this notification thread. If you have signed up to receive notifications to other comments or comment threads on {site_title}, you will still receive those.',
		'replied_comment'  => '{source_commenter_name} wrote this reply in response to the comment by {comment_author} on {source_page}',
		'unsubscribe_text' => 'click here to unsubscribe',

		'post_email_subject'    => 'New comment posted on {source_page}',
		'post_content_title'    => 'A new comment was posted on {site_title}',
		'post_comment_posted'   => 'Comment posted on {site_title}',
		'post_reply_to'         => 'Reply to {source_commenter_name}',
		'post_signed_up'        => 'You are signed up to be notified of replies to your comment on {source_page}',
		'post_unsubscribe'      => 'You can {unsubscribe_link} from these notifications. Please note that this will unsubscribe you only from this notification  thread. If you have signed up to receive notifications to other comments or comment threads on {site_title}, you will still receive those.',
		'post_unsubscribe_text' => 'click here to unsubscribe',
	);

	/**
	 * Default settings
	 *
	 * @var array
	 */
	public static $_defaults = array(
		'activate_comments'            => 1,
		'comment_registration'         => 1,
		'close_comments_for_old_posts' => 0,
		'close_comments_days_old'      => 14,
		'comments_per_page'            => 5,
		'page_comments'                => 1,
		'is_dynamic'                   => 0,
		'comment_order'                => '',
		'gravatar_active'              => 1,
		'tcm_default_picture'          => '',
		'powered_by'                   => 1,
		'comment_date'                 => self::TCM_ABSOLUTE_DATE,
		'share_individual_comments'    => 1,
		'comment_style_template'       => self::TCM_DESIGN_DEFAULT,
		'lazy_load'                    => 1,
		'lazy_load_avatar'             => 0,
		self::TCM_LABELS_KEY           => '',
		self::TCM_ACCENT_COLOR         => '#03a9f4',
		'tcm_notification_labels'      => '',
		'tcm_keywords'                 => '',
		'tcm_live_update'              => 0,
		'tcm_live_update_refresh_rate' => 20,
		'tcm_enable_social_signin'     => 0,
		'tcm_show_url'                 => 1,
		'login_activation'             => 0,
		'tcm_badges'                   => '',
		'tcm_badges_option'            => false,
		'tcm_badges_custom_images'     => '',
		'tcm_voting_only_register'     => false,
		'tcm_conversion'               => '',
		'tcm_roles'                    => '',
		'tcm_mod_administrator'        => 1,
		'tcm_mod_editor'               => 1,
		'tcm_mod_author'               => 1,
		'tcm_mod_contributor'          => 0,
		'tcm_mod_subscriber'           => 0,
		'tcm_exclude_moderators'       => 0,
		'tcm_vote_type'                => 'no_vote',
		'tcm_email_service'            => '',
		'tcm_api_status'               => array(
			'facebook'     => 1,
			'google'       => 1,
			'facebook_api' => 0,
			'google_api'   => 0,
		),
		'badges_to_moderators'         => 1,
		'tcm_mark_upvoted'             => 1,
		'comment_moderation'           => 1,
		'comment_max_links'            => 2,
		'moderation_keys'              => '',
		'comment_whitelist'            => 1,
		'blacklist_keys'               => '',
		'tcm_meta_tags'                => 0,
		'tcm_moderators_notifications' => 1,
		'remember_me'                  => 0,
		'storing_consent'              => 0,
	);

	/**
	 * @return mixed|null
	 */
	public static function get_defaults() {
		return apply_filters( 'tcm_default_settings', static::$_defaults );
	}

	/**
	 *  Conversion defaults
	 *
	 * @var array
	 */
	public static $_tcm_conversion_defaults = array(
		'first_time'        => array(
			'active' => 'tcm_live_update',
		),
		'second_time'       => array(
			'active' => 'tcm_live_update',
		),
		'tcm_live_update'   => array(
			'first_time'  => array(
				'custom_message' => 'Thank you, {commenter_name} ! Your comment has been submitted for this post. If at any point in time you want to make changes or delete your comment, contact us.',
			),
			'second_time' => array(
				'custom_message' => 'Thank you, {commenter_name} ! Your comment has been submitted for this post. If at any point in time you want to make changes or delete your comment, contact us.',
			),
		),
		'tcm_social_share'  => array(
			'first_time'  => array(
				'custom_message'         => 'Thank you, {commenter_name}! Your comment has been submitted for this post. If at any point in time you want to make changes or delete your comment, contact us. Would you like to share this post with your friends?',
				'social_sharing_buttons' => array(
					'fb_share' => 1,
					'tw_share' => 1,
					'lk_share' => 1,
					'pt_share' => 1,
					'gg_share' => 1,
					'xi_share' => 1,
				),
			),
			'second_time' => array(
				'custom_message'         => 'Thank you, {commenter_name}! Your comment has been submitted for this post. If at any point in time you want to make changes or delete your comment, contact us. Would you like to share this post with your friends?',
				'social_sharing_buttons' => array(
					'fb_share' => 1,
					'tw_share' => 1,
					'lk_share' => 1,
					'pt_share' => 1,
					'gg_share' => 1,
					'xi_share' => 1,
				),
			),
		),
		'tcm_related_posts' => array(
			'first_time'  => array(
				'custom_message'      => 'Thank you for your comment,{commenter_name}! If at any point in time you want to make changes or delete your comment, contact us. Here are some more posts you might be interested in:',
				'show_featured_image' => 0,
			),
			'second_time' => array(
				'custom_message'      => 'Thank you for your comment,{commenter_name}! If at any point in time you want to make changes or delete your comment, contact us. Here are some more posts you might be interested in:',
				'show_featured_image' => 0,
			),
		),
		'tcm_redirect'      => array(
			'first_time'  => array(
				'redirect_url'      => '',
				'redirect_post_id'  => '',
				'redirect_post_val' => '',
				'flag'              => '',
			),
			'second_time' => array(
				'redirect_url'      => '',
				'redirect_post_id'  => '',
				'redirect_post_val' => '',
				'flag'              => '',
			),
		),
		'tcm_thrivebox'     => array(
			'first_time'  => array(
				'thrivebox_id' => '',
			),
			'second_time' => array(
				'thrivebox_id' => '',
			),
		),
	);

	public static $_moderation_keyboard = array(
		'up'     => 'upDown',
		'down'   => 'upDown',
		'j'      => 'upDown',
		'k'      => 'upDown',
		'a'      => 'actionKey',
		'u'      => 'actionKey',
		'd'      => 'actionKey',
		'e'      => 'actionKey',
		'q'      => 'actionKey',
		'f'      => 'actionKey',
		'g'      => 'actionKey',
		't'      => 'actionKey',
		's'      => 'actionKey',
		'z'      => 'actionKey',
		'r'      => 'actionKey',
		'n'      => 'actionKey',
		'esc'    => 'actionKey',
		'enter'  => 'actionKey',
		'left'   => 'leftRight',
		'right'  => 'leftRight',
		'ctrl+k' => 'showKeyboardTooltip',
	);

	public static $_default_achievements = array(
		'featured_comments'  => 0,
		'approved_replies'   => 0,
		'approved_comments'  => 0,
		'upvotes_received'   => 0,
		'downvotes_received' => 0,
	);

	/**
	 * Settings from admin that need to be synced.
	 *
	 * @var array $_sync_settings
	 */
	public static $_sync_settings = array(
		'tcm_default_picture',
		'comment_date',
		'powered_by',
		self::TCM_LABELS_KEY,
		'gravatar_active',
		'share_individual_comments',
		'tcm_color_picker_value',
		'tcm_vote_type',
		'tcm_notification_labels',
	);

	/**
	 * Full path to the plugin folder (!includes a trailing slash if the $file argument is missing)
	 *
	 * @param string $file filename.
	 *
	 * @return string
	 */
	public static function plugin_path( $file = '' ) {
		return plugin_dir_path( dirname( __FILE__ ) ) . ltrim( $file, '\\/' );
	}

	/**
	 * Full plugin url
	 *
	 * @param string $file if sent, it will return the full URL to the file.
	 *
	 * @return string
	 */
	public static function plugin_url( $file = '' ) {
		return plugin_dir_url( dirname( __FILE__ ) ) . ltrim( $file, '\\/' );
	}

	/**
	 * Full iframe url
	 *
	 * @return string
	 */
	public static function iframe_url() {
		return site_url() . '/wp-admin/admin.php?page=tcm_admin_dashboard&' . Thrive_Comments_Constants::TCM_FRAME . '=1#livePreview';
	}

	/**
	 * Return thrive comments settings url
	 *
	 * @return string
	 */
	public function tcm_settings_url() {
		return site_url() . '/wp-admin/admin.php?page=tcm_admin_dashboard';
	}

	/**
	 * Return thrive comments moderation url
	 *
	 * @return string
	 */
	public function tcm_moderation_url() {
		return site_url() . '/wp-admin/edit-comments.php?page=tcm_comment_moderation';
	}
}
