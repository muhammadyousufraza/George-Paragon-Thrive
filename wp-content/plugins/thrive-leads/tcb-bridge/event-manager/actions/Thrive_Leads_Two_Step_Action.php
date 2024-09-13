<?php
/**
 * Created by PhpStorm.
 * User: radu
 * Date: 05.08.2014
 * Time: 14:35
 */

if ( ! class_exists( 'Thrive_Leads_Two_Step_Action' ) ) {
	if ( ! class_exists( 'TCB_Thrive_Lightbox' ) ) {
		require_once TVE_TCB_ROOT_PATH . 'event-manager/classes/actions/TCB_Thrive_Lightbox.php';
	}

	/**
	 *
	 * handles the server-side logic for the Thrive Lightbox action = opens a lightbox on an Event Trigger
	 *
	 * Class TCB_Thrive_Lightbox
	 */
	class Thrive_Leads_Two_Step_Action extends TCB_Thrive_Lightbox {

		protected $key = 'thrive_leads_2_step';

		public static $trigger_id = '2st';

		/**
		 * holds all lightbox ids that have been parsed for events configuration - this is to not create an infinite loop in case of
		 * lightboxes used within lightboxes
		 *
		 * @var array
		 */
		private static $lightboxes_events_parsed = array();
		/**
		 * holds all lightbox content that have been parsed for events configuration
		 *
		 * @var array
		 */
		private static $lightboxes_content = array();

		/**
		 * Should return the user-friendly name for this Action
		 *
		 * @return string
		 */
		public function getName() {
			return __( 'Open Thrive Leads ThriveBox', 'thrive-leads' );
		}

		/**
		 * Should output the settings needed for this Action when a user selects it from the list
		 *
		 * @param mixed $data
		 *
		 * @return string the full html for the settings view
		 */
		public function renderSettings( $data ) {
			$two_steps = tve_leads_get_two_step_lightboxes( array( 'active_test' => true ) );

			$lightboxes = array();
			foreach ( $two_steps as $l ) {
				$lightboxes[ $l->ID ] = $l;
			}

			$data['lightboxes'] = $lightboxes;
			$this->data         = $data;
			ob_start();
			include dirname( __DIR__ ) . '/views/lightbox-settings.php';
			$content = ob_get_clean();

			return $content;
		}

		/**
		 * output edit links for the lightbox
		 */
		public function getRowActions() {
			if ( empty( $this->config ) ) {
				return '';
			}
			$two_step = tve_leads_get_form_type( $this->config['l_id'], array( 'active_test' => true ) );
			if ( $two_step->active_test ) {
				return sprintf(
					'<br>%s - <a href="%s" target="_blank" class="tve_link_no_warning">%s</a>',
					__( 'A/B test in progress', 'thrive-leads' ),
					admin_url( 'admin.php?page=thrive_leads_dashboard' ) . '#test/' . $two_step->active_test->id,
					__( 'View test', 'thrive-leads' )
				);
			}

			return sprintf(
				'<br><a href="%s" target="_blank" class="tve_link_no_warning">%2$s</a>',
				admin_url( 'admin.php?page=thrive_leads_dashboard' ) . '#2step-lightbox/' . $this->config['l_id'],
				__( 'Edit ThriveBox', 'thrive-leads' )
			);
		}

		/**
		 * check if the associated lightbox exists and it's not trashed
		 *
		 * @return bool
		 */
		public function validateConfig() {
			$two_step_id = $this->config['l_id'];
			if ( empty( $two_step_id ) ) {
				return false;
			}

			$two_step = tve_leads_get_form_type( $two_step_id, array( 'get_variations' => false ) );
			if ( empty( $two_step ) || $two_step->post_status === 'trash' || $two_step->post_type != TVE_LEADS_POST_TWO_STEP_LIGHTBOX ) {
				return false;
			}

			return true;
		}

		/**
		 * this will just trigger a click on the container that holds the 2-step trigger
		 *
		 * @return string
		 */
		public function getJsActionCallback() {
			if ( ! self::$trigger_id ) {
				self::$trigger_id = uniqid( 'tl-' );
			}

			return 'function(t,a,c){var evt=ThriveGlobal.$j.Event("click"), $target=ThriveGlobal.$j("#tcb-evt-' . self::$trigger_id . '-"+c.l_id+" .tve-leads-two-step-trigger");if(t==="exit" && $target.data("shown-on-exit")){ return;}$target.data("shown-on-"+t, true);evt.tve_trigger=t;evt.tve_action=a;evt.tve_config=c;$target.first().trigger(evt);return false;}';
		}

		/**
		 * we just display a hidden element that acts as the trigger for the lightbox - it will be automatically triggered from javascript
		 *
		 * @param $data
		 *
		 * @return string
		 */
		public function applyContentFilter( $data ) {
			$two_step_id = empty( $data['config']['l_id'] ) ? 0 : $data['config']['l_id'];

			if ( isset( self::$lightboxes_content[ $two_step_id ] ) ) {
				$content = self::$lightboxes_content[ $two_step_id ];
			} else {
				$content = tve_leads_two_step_render( array( 'id' => $two_step_id ), '' );
			}

			if ( ! empty( $GLOBALS['tve_leads_form_config']['forms']["two_step_$two_step_id"]['_key'] ) && tve_leads_has_lightspeed() && TCB\Lightspeed\Main::is_enabled() ) {
				$content = TCB\Lightspeed\Css::get_instance( $two_step_id )->get_optimized_styles( 'inline', 'base_' . $GLOBALS['tve_leads_form_config']['forms']["two_step_$two_step_id"]['_key'] ) . $content;
			}

			return '<span id="tcb-evt-' . self::$trigger_id . '-' . $two_step_id . '" style="display:none">' . $content . '</span>';
		}

		/**
		 * called inside the_content filter
		 * make sure that if custom icons are used, the CSS for that is included in the main page
		 * the same with Custom Fonts
		 *
		 * @param array $data configuration data
		 */
		public function mainPostCallback( $data ) {
			$two_step_id = empty( $data['config']['l_id'] ) ? 0 : $data['config']['l_id'];
			if ( isset( self::$lightboxes_events_parsed[ $two_step_id ] ) ) {
				return;
			}
			self::$lightboxes_events_parsed[ $two_step_id ] = true;
			if ( ! isset( self::$lightboxes_content[ $two_step_id ] ) ) {
				self::$lightboxes_content[ $two_step_id ] = tve_leads_two_step_render( array( 'id' => $two_step_id ), '' );
			}
		}

		/**
		 * Get javascript options for the editor page
		 *
		 * @return array
		 */
		public function get_options() {
			return array(
				'labels'  => $this->getName(),
				'options' => self::thriveboxes(),
			);
		}

		/**
		 * return a list of all currently defined thrive boxes
		 *
		 * @return array
		 */
		public static function thriveboxes() {
			$data      = array();
			$two_steps = tve_leads_get_two_step_lightboxes( array( 'active_test' => true ) );

			foreach ( $two_steps as $l ) {
				$data[] = array(
					'id'       => (int) $l->ID,
					'title'    => $l->post_title,
					'edit_url' => admin_url( 'admin.php?page=thrive_leads_dashboard#2step-lightbox/' . $l->ID ),
				);
			}

			return $data;
		}

		/**
		 * The Backbone view constructor
		 *
		 * @return string
		 */
		public function get_editor_js_view() {
			return 'TL_Editor.views.ThriveBoxAction';
		}

		public function render_editor_settings() {
			include dirname( __DIR__ ) . '/views/item-list.php';
		}
	}
}
