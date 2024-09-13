<?php
/**
 * Created by PhpStorm.
 * User: radu
 * Date: 05.08.2014
 * Time: 14:35
 */

if ( ! class_exists( 'Thrive_Leads_State_Lightbox_Action' ) ) {
	if ( ! class_exists( 'TCB_Thrive_Lightbox' ) ) {
		require_once TVE_TCB_ROOT_PATH . 'event-manager/classes/actions/TCB_Thrive_Lightbox.php';
	}

	/**
	 *
	 * handles the server-side logic for the Thrive Lightbox action = opens a lightbox on an Event Trigger
	 *
	 * Class TCB_Thrive_Lightbox
	 */
	class Thrive_Leads_State_Lightbox_Action extends TCB_Thrive_Lightbox {

		protected $key = 'tl_state_lightbox';

		public static $loaded_states = array();

		/**
		 * name differs when we are in a lightbox state (child) and we need to change to another lightbox state
		 * the names turns into "Switch Lightbox State"
		 *
		 * @var string
		 */
		public static $action_name = '';

		/**
		 * scope differs when we are in a lightbox state (child) and we need to change to another lightbox state
		 *
		 * @var string
		 */
		public static $action_scope = 'open_lightbox';

		/**
		 * holds all .state-root <div>s for each variation
		 *
		 * @var array
		 */
		private static $footer_html = array();

		/**
		 * only load the "Open State Lightbox" Action if the parent form type is != lightbox
		 *
		 * @return boolean
		 */
		public function is_available() {
			global $current_variation;
			if ( empty( $current_variation ) ) {
				return false;
			}

			$parent_form_type = get_post_meta( $current_variation['post_parent'], 'tve_form_type', true );
			$parent_form_type = Thrive_Leads_Template_Manager::tpl_type_map( $parent_form_type );

			return $parent_form_type != 'lightbox' && $parent_form_type != 'screen_filler';
		}

		/**
		 * Should return the user-friendly name for this Action
		 *
		 * @return string
		 */
		public function getName() {
			return self::$action_name ? self::$action_name : __( 'Lightbox state', 'thrive-leads' );
		}

		/**
		 *
		 * @return array
		 */
		public static function states() {
			global $current_variation;

			$all = tve_leads_get_form_related_states( $current_variation );

			$states = array();
			foreach ( $all as $state ) {
				if ( $state['key'] == $current_variation['key'] || $state['form_state'] !== 'lightbox' ) {
					continue;
				}
				$states [] = array(
					'id'    => intval( $state['key'] ),
					'title' => $state['state_name'],
				);
			}

			return $states;
		}

		/**
		 * this will just trigger a click on the container that holds the 2-step trigger
		 *
		 * @return string
		 */
		public function getJsActionCallback() {
			return 'function (t, a, c) {
                var $this = ThriveGlobal.$j(this);
                if ($this.parents(".tve_post_lightbox").length) {
                    var current = ThriveGlobal.$j(this).parents(".tl-style").first();
                    var root = current.parents(".tl-states-root").first()
                    var container = root.find("[data-state=" + c.s + "]");
                    if (!container.length) {
                        return false;
                    }
                    current.hide();
                    container.show();
                    root.trigger("switchstate", [container, current]);
                    return false;
                }
                TL_Front.parent_state = $this.parents(".tl-style").first();
                var evtData = {form_type: "lightbox", $target: ThriveGlobal.$j(".tl-style[data-state=" + c.s + "] .tve_p_lb_content").parent(),$parentStateEl: TL_Front.parent_state};TL_Front.$document.trigger("switchstate",[evtData.$target]);ThriveGlobal.$j(TL_Front).trigger("showform.thriveleads", evtData);return false;
            }';
		}

		/**
		 * output the main options for this lightbox (in the editor events list)
		 *
		 * @return string
		 */
		public function getSummary() {
			$config = $this->config;
			if ( empty( $config ) ) {
				return '';
			}

			$animation = ! empty( $config['a'] ) && in_array( $config['a'], TVE_Leads_Animation_Abstract::$available ) ? $config['a'] : TVE_Leads_Animation_Abstract::ANIM_INSTANT;

			return '; Animation: ' . TVE_Leads_Animation_Abstract::factory( $animation )->get_display_name();
		}

		/**
		 * output edit links for the lightbox
		 */
		public function getRowActions() {
			if ( empty( $this->config ) ) {
				return '';
			}

			return sprintf(
				'<br><a href="javascript:void(0)" data-ctrl="function:ext.tve_leads.state.state_click" data-id="%s" class="tve_click tve_link_no_warning">%s</a>',
				$this->config['s'],
				__( 'Edit Lightbox State', 'thrive-leads' )
			);
		}

		/**
		 * check if the associated lightbox exists and it's not trashed
		 *
		 * @return bool
		 */
		public function validateConfig() {
			if ( empty( $this->config ) || empty( $this->config['s'] ) ) {
				return false;
			}

			global $variation;

			$state = tve_leads_get_form_variation( null, $this->config['s'] );

			if ( empty( $state ) || $state['form_state'] != 'lightbox' || $state['post_parent'] != $variation['post_parent'] ) {
				return false;
			}

			return true;
		}

		/**
		 * called inside the_content filter
		 * make sure that if custom icons are used, the CSS for that is included in the main page
		 * the same with Custom Fonts
		 *
		 * @param array $data configuration data
		 *
		 * @return string
		 */
		public function mainPostCallback( $data ) {
			$data = $data['config'];
			if ( empty( $data['s'] ) ) {
				return '';
			}
			if ( isset( self::$loaded_states[ $data['s'] ] ) ) {
				return '';
			}

			$state = tve_leads_get_form_variation( null, $data['s'] );
			if ( empty( $state ) ) {
				return '';
			}

			if ( empty( $GLOBALS['tl_event_parse_variation'] ) ) {
				return '';
			}

			self::$loaded_states[ $data['s'] ] = $state;
			tve_leads_enqueue_variation_scripts( $state );

			if ( ! empty( $data['a'] ) ) {
				$state['display_animation'] = $data['a'];
			}

			$params            = array(
				'wrap' => false,
			);
			/* Changed from end() to [0] related to SUPP-3910 because it should take the first one which is the variation, not it's children */
			$current_variation =  $GLOBALS['tl_event_parse_variation'][0];
			$current_form_type = tve_leads_get_form_type_from_variation( $current_variation );
			/**
			 * if current variation is a lightbox, then this action will be changed to "switch state"
			 */
			if ( $current_form_type == 'lightbox' ) {
				$params = array(
					'wrap'       => false,
					'hide'       => true,
					'hide_inner' => false,
					'animation'  => false,
				);
			}

			$animation = empty( $data['anim'] ) ? $state['display_animation'] : $data['anim'];
			if ( empty( self::$footer_html[ $state['parent_id'] ] ) ) {
				self::$footer_html[ $state['parent_id'] ]['root']   = '<div class="tl-states-root tl-anim-' . $animation . '">';
				self::$footer_html[ $state['parent_id'] ]['states'] = array();
			}
			/**
			 * if the lightbox is opened from a non-lightbox state we need to wrap the lightbox in a div
			 */
			if ( $current_form_type != 'lightbox' ) {
				$params['wrap_tl_style']                               = true;
				$params['animation']                                   = true;
				$params['state_animation']                             = $animation;
				self::$footer_html[ $state['parent_id'] ]['states'] [] = tve_leads_display_form_lightbox( '__return_content', tve_editor_custom_content( $state ), $state, null, null, $params );
			} else {
				array_unshift( self::$footer_html[ $state['parent_id'] ]['states'], tve_leads_display_form_lightbox( '__return_content', tve_editor_custom_content( $state ), $state, null, null, $params ) );
			}

			remove_filter( 'tve_leads_append_states_ajax', array( 'Thrive_Leads_State_Lightbox_Action', 'ajax_output_states' ), 10 );
			add_filter( 'tve_leads_append_states_ajax', array( 'Thrive_Leads_State_Lightbox_Action', 'ajax_output_states' ), 10, 2 );

		}

		/**
		 * we just display a hidden element that holds the lightbox
		 *
		 * @param $data
		 *
		 * @return string
		 */
		public function applyContentFilter( $data ) {
			$out = '';
			foreach ( self::$footer_html as $parts ) {
				$out .= $parts['root'] . implode( '', $parts['states'] ) . '</div>';
			}
			/**
			 * output it just once
			 */
			self::$footer_html = array();

			return $out;
		}

		/**
		 * @param array $output_variations
		 *
		 * @return array
		 *
		 * @see tve_leads_ajax_load_forms
		 */
		public static function ajax_output_states( $output_variations ) {
			foreach ( self::$loaded_states as $id => $v ) {
				if ( is_array( $v ) ) {
					$output_variations [] = $v;
				}
			}

			return $output_variations;
		}

		public function get_editor_js_view() {
			return 'TVE.leads.LightboxStateAction';
		}

		public function get_options() {
			return array(
				'labels'     => $this->getName(),
				'options'    => self::states(),
				'animations' => tve_leads_get_available_animations( true ),
			);
		}

		public function render_editor_settings() {
			include dirname( dirname( __FILE__ ) ) . '/views/item-list.php';
		}
	}
}
