<?php
/**
 * Created by PhpStorm.
 * User: radu
 * Date: 05.08.2014
 * Time: 14:35
 */

if ( ! class_exists( 'Thrive_Leads_State_Switch_Action' ) ) {

	/**
	 *
	 * handles the server-side logic for the Thrive Lightbox action = opens a lightbox on an Event Trigger
	 *
	 * Class TCB_Thrive_Lightbox
	 */
	class Thrive_Leads_State_Switch_Action extends TCB_Event_Action_Abstract {

		protected $key = 'tl_state_switch';

		public static $loaded_states = array();

		protected static $saved_content = array();

		/**
		 * only load the Change State action if the variation is not a "Lightbox State"
		 *
		 * @return boolean
		 */
		public function is_available() {
			global $current_variation;
			if ( empty( $current_variation ) || $current_variation['form_state'] == 'lightbox' ) {
				return false;
			}

			return true;
		}

		/**
		 * Should return the user-friendly name for this Action
		 *
		 * @return string
		 */
		public function getName() {
			return __( 'Switch state', 'thrive-leads' );
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
				if ( $state['key'] == $current_variation['key'] ) {
					continue;
				}
				if ( $state['form_state'] == 'default' || empty( $state['form_state'] ) ) {
					$states [] = array(
						'id'    => intval( $state['key'] ),
						'title' => $state['state_name'],
					);
				}
			}

			return $states;
		}

		/**
		 * Should output the settings needed for this Action when a user selects it from the list
		 *
		 * @param mixed $data
		 *
		 * @return string the full html for the settings view
		 */
		public function renderSettings( $data ) {
			$all = tve_leads_get_form_related_states( $_POST['_key'] );

			$states = array();
			foreach ( $all as $state ) {
				if ( $state['key'] == $_POST['_key'] ) {
					continue;
				}
				if ( $state['form_state'] == 'default' || empty( $state['form_state'] ) ) {
					$states[ $state['key'] ] = $state;
				}
			}

			$data['states'] = $states;
			$this->data     = $data;
			ob_start();
			include dirname( dirname( __FILE__ ) ) . '/views/state-settings.php';
			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		}

		/**
		 * this will just trigger a click on the container that holds the 2-step trigger
		 *
		 * @return string
		 */
		public function getJsActionCallback() {
			$jquery = 'ThriveGlobal.$j';

			return "function(t,a,c){
				var current={$jquery}(this).parents('.tl-style').first();
				var container=current.parent().find('[data-state=\"'+c.s+'\"]');
				if(!container.length && current.data('parentStateEl'))
				{
					var parent = current.data('parentStateEl').parent();
					parent.children().hide();
					container = parent.find('[data-state=\"'+c.s+'\"]');
				}
				if(!container.length){return false;}
				current.hide();
				container.show().parent().trigger('switchstate',[container,current]);
				return false;
				};";
		}

		/**
		 * we just display a hidden element that acts as the trigger for the lightbox - it will be automatically triggered from javascript
		 *
		 * @param $data
		 *
		 * @return string
		 */
		public function applyContentFilter( $data ) {
			return '';
		}

		/**
		 * output edit link for the state - this will trigger the state change
		 */
		public function getRowActions() {
			if ( empty( $this->config ) ) {
				return '';
			}

			return sprintf(
				'<br><a href="javascript:void(0)" data-ctrl="function:ext.tve_leads.state.state_click" data-id="%s" class="tve_click tve_link_no_warning">%s</a>',
				$this->config['s'],
				__( 'Edit State', 'thrive-leads' )
			);
		}

		/**
		 * check if the associated state exists
		 *
		 * @return bool
		 */
		public function validateConfig() {
			if ( empty( $this->config ) || empty( $this->config['s'] ) ) {
				return false;
			}

			global $variation;

			$state = tve_leads_get_form_variation( null, $this->config['s'] );

			if ( empty( $state ) || $state['post_parent'] != $variation['post_parent'] ) {
				return false;
			}

			return true;
		}

		/**
		 * called inside the_content filter
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

			/* Use case: already subscribed state which links back to the first state - this caused the Already Subscribed state to be displayed twice */
			/* Same happened when a lightbox was used*/
			$current_variation = isset( $GLOBALS['tl_event_parse_variation'] ) ? end( $GLOBALS['tl_event_parse_variation'] ) : array();
			if ( ! empty( $current_variation ) && ( $current_variation['form_state'] === 'already_subscribed' || $current_variation['form_state'] === 'lightbox' ) ) {
				/**
				 * we also need to check the following:
				 * if the subscribed state links back to the default state, we do not need to render that anymore, because that's already included in the page
				 */
				if ( empty( $state['parent_id'] ) ) {
					self::$loaded_states[ $state['parent_id'] ] = true;

					return '';
				}
			}

			self::$loaded_states[ $data['s'] ]          = $state;
			self::$loaded_states[ $state['parent_id'] ] = true;
			tve_leads_enqueue_variation_scripts( $state );

			$_type = tve_leads_get_form_type_from_variation( $state );

			if ( strpos( $state['tpl'], 'screen_filler|' ) === 0 ) {
				$_type = 'screen_filler';
			} elseif ( strpos( $state['tpl'], 'lightbox|' ) === 0 ) {
				$_type = 'lightbox';
			}

			$display_fn = 'tve_leads_display_form_' . $_type;

			$form_content = tve_editor_custom_content( $state );

			$parent_id = ! empty( $state['parent_id'] ) ? $state['parent_id'] : $state['key'];

			if ( empty( self::$saved_content[ $parent_id ] ) ) {
				self::$saved_content[ $parent_id ] = '';
			}
			/**
			 * In some instances there is no $_type so default will call an undefined fn
			 * idk why, stupid user, better safe than sorry
			 */
			if ( function_exists( $display_fn ) ) {
				switch ( $_type ) {
					case 'lightbox':
						self::$saved_content[ $parent_id ] .= tve_leads_display_form_lightbox( '__return_content', $form_content, $state, null, null, array(
							'wrap'       => false,
							'hide'       => true,
							'hide_inner' => false,
						) );
						break;
					case 'screen_filler':
						self::$saved_content[ $parent_id ] .= tve_leads_display_form_screen_filler( '__return_content', $form_content, $state, array(
							'wrap'       => false,
							'hide'       => true,
							'hide_inner' => false,
							'animation'  => false,
						) );
						break;
					default:
						/**
						 * this includes all other forms:
						 * ribbon, in_content, screen_filler, post_footer, shortcode
						 */
						self::$saved_content[ $parent_id ] .= $display_fn( '__return_content', $form_content, $state, array(
							'wrap' => false,
							'hide' => true,
						) );
				}
			}
			remove_filter( 'tve_leads_variation_append_states', array( 'Thrive_Leads_State_Switch_Action', 'append_state_html' ), 10 );
			add_filter( 'tve_leads_variation_append_states', array( 'Thrive_Leads_State_Switch_Action', 'append_state_html' ), 10, 2 );

			remove_filter( 'tve_leads_append_states_ajax', array( 'Thrive_Leads_State_Switch_Action', 'ajax_output_states' ), 10 );
			add_filter( 'tve_leads_append_states_ajax', array( 'Thrive_Leads_State_Switch_Action', 'ajax_output_states' ), 10, 2 );
		}

		public static function append_state_html( $current_html, $variation ) {
			if ( empty( self::$saved_content[ $variation['key'] ] ) ) {
				return $current_html;
			}

			return $current_html . self::$saved_content[ $variation['key'] ];
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
			return 'TVE.leads.StateSwitchAction';
		}

		public function get_options() {
			return array(
				'labels'  => $this->getName(),
				'options' => self::states(),
			);
		}

		public function render_editor_settings() {
			include dirname( __DIR__ ) . '/views/item-list.php';
		}
	}
}
