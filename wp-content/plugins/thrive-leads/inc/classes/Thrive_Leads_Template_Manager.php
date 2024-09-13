<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 28.01.2015
 * Time: 14:39
 */
class Thrive_Leads_Template_Manager extends Thrive_Leads_Request_Handler {

	const OPTION_TPL_META    = 'tve_leads_saved_tpl_meta';
	const OPTION_TPL_CONTENT = 'tve_leads_saved_tpl';
	public static $file_name = null;
	/**
	 * two_step_lightboxes use the same templates as lightboxes
	 *
	 * @var array
	 */
	protected static $type_maps
		= array(
			'two_step_lightbox' => 'lightbox',
			'php_insert'        => 'in_content',
			'shortcode'         => 'in_content',
			'post_footer'       => 'in_content',
		);

	/**
	 * @var array
	 */
	protected $variation;

	/**
	 * @var Thrive_Leads_Template_Manager
	 */
	protected static $instance = null;

	/**
	 *
	 * singleton implementation
	 *
	 * @param array $variation the variation being edited
	 *
	 * @return Thrive_Leads_Template_Manager
	 */
	public static function instance( $variation ) {
		return new self( $variation );
	}

	/**
	 * get all templates available for a form variation
	 */
	public static function for_variation( $variation ) {
		return self::instance( $variation )->get_all();
	}

	/**
	 * get all templates available for a form type
	 *
	 * @param mixed $form_type
	 * @param bool  $get_multi_steps whether or not to include templates designed for the multi-step forms
	 *
	 * @return array the list of templates
	 */
	public static function for_form_type( $form_type, $get_multi_steps = true ) {
		if ( $form_type instanceof WP_Post ) {
			$form_type = get_post_meta( $form_type->ID, 'tve_form_type', true );
		}

		return self::instance( array() )->get_all( $form_type, $get_multi_steps );
	}

	/**
	 * V2.0
	 *
	 * @param      $form_type
	 * @param bool $get_multi_steps
	 *
	 * @return array
	 */
	public static function get_templates( $form_type, $get_multi_steps = true ) {

		if ( $form_type instanceof WP_Post ) {
			$form_type = get_post_meta( $form_type->ID, 'tve_form_type', true );
		}

		$parent_form_type = $form_type;
		$form_type        = self::tpl_type_map( $form_type );

		/**
		 * local templates, usually only blank template
		 */
		$local_templates = array(
			'tcb2_blank' => tve_leads_get_v2_blank_config( $form_type ),
		);

		/**
		 * get templates from cloud
		 */
		if ( $parent_form_type === 'two_step_lightbox' ) {
			$cloud_templates = self::from_cloud( $parent_form_type, $get_multi_steps );
		} else {
			$cloud_templates = self::from_cloud( $form_type, $get_multi_steps );
		}

		/**
		 * set all templates in one array
		 */
		$error_templates = [];
		if ( empty( $cloud_templates['error'] ) ) {
			$assoc_templates = array_merge( $local_templates, $cloud_templates );
		}else {
			$assoc_templates = $local_templates;
			$error_templates = $cloud_templates;
		}
		$templates = array();

		if ( ! empty( $assoc_templates ) ) {
			foreach ( $assoc_templates as $key => &$tpl ) {
				$tpl['id']   = $key;
				$templates[] = $tpl;
			}
		}

		return [ 'templates'=> $templates, 'error'=> $error_templates ];
	}

	/**
	 * @param $form_type      string
	 * @param $get_multi_step bool
	 *
	 * @return array
	 */
	public static function from_cloud( $form_type, $get_multi_step ) {

		if ( $form_type == 'two_step_lightbox' ) {

			$lightbox_templates      = tve_leads_get_cloud_templates( 'lightbox', $get_multi_step );
			$screen_filler_templates = tve_leads_get_cloud_templates( 'screen_filler', $get_multi_step );

			$templates = array_merge( $lightbox_templates, $screen_filler_templates );

		} else {

			$templates = tve_leads_get_cloud_templates( $form_type, $get_multi_step );
		}

		return $templates;
	}

	/**
	 * get all templates available for a multi-state form
	 *
	 * @param mixed $form_type
	 *
	 * @return array the list of templates
	 */
	public static function for_multi_step( $form_type ) {
		if ( $form_type instanceof WP_Post ) {
			$form_type = get_post_meta( $form_type->ID, 'tve_form_type', true );
		}

		return self::instance( array() )->get_multi_step_templates( $form_type );
	}

	/**
	 * get a template type map for the $form_type
	 * used when editing form types that use templates from different categories (e.g. two_step_lightbox - lightbox)
	 *
	 * @param string $form_type one of: ribbon, lightbox, two_step_lightbox, shortcode etc
	 *
	 * @return string the corresponding form_type
	 */
	public static function tpl_type_map( $form_type ) {
		return isset( self::$type_maps[ $form_type ] ) ? self::$type_maps[ $form_type ] : $form_type;
	}

	/**
	 *
	 * @param array $variation
	 */
	private function __construct( $variation ) {
		$this->variation = $variation;
	}

	/**
	 * forward the call based on the $action parameter
	 * API entry-point for the template chooser lightbox (from the editor)
	 *
	 * @param string $action
	 */
	public function api( $action ) {
		$method = 'api_' . $action;

		$result = call_user_func( array( $this, $method ) );

		if ( is_array( $result ) ) {
			$result = json_encode( $result );
		}

		exit( $result );
	}

	/**
	 * get the current template type (ribbon, post_footer etc)
	 *
	 * @param string|array $template_key optional array with TVE_LEADS_FIELD_TEMPLATE or directly the string key
	 *
	 * @return string
	 */
	public function type( $template_key = null ) {
		if ( is_array( $template_key ) ) {
			$template_key = $template_key[ TVE_LEADS_FIELD_TEMPLATE ];
		}

		$variation_template = isset( $this->variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ? $this->variation[ TVE_LEADS_FIELD_TEMPLATE ] : '|';
		list( $type, $key ) = explode( '|', $template_key ? $template_key : $variation_template );

		return $type;
	}

	/**
	 * get the current template key (one_set, two_set etc)
	 *
	 * @param string|array $template_key optional array with TVE_LEADS_FIELD_TEMPLATE or directly the string key
	 *
	 * @return string
	 */
	public function key( $template_key = null ) {
		if ( is_array( $template_key ) ) {
			$template_key = $template_key[ TVE_LEADS_FIELD_TEMPLATE ];
		}

		list( $type, $key ) = explode( '|', $template_key ? $template_key : $this->variation[ TVE_LEADS_FIELD_TEMPLATE ] );

		return $key;
	}

	/**
	 * exchange data from $template to this->variation or vice-versa
	 *
	 * @param array  $template
	 * @param string $dir can either be left-right or right-left
	 *
	 * @return array
	 */
	protected function interchange_data( $template, $dir = 'left -> right' ) {
		$fields = array(
			TVE_LEADS_FIELD_SAVED_CONTENT,
			TVE_LEADS_FIELD_INLINE_CSS,
			TVE_LEADS_FIELD_USER_CSS,
			TVE_LEADS_FIELD_GLOBALS,
			TVE_LEADS_FIELD_CUSTOM_FONTS,
			TVE_LEADS_FIELD_ICON_PACK,
			TVE_LEADS_FIELD_HAS_MASONRY,
			TVE_LEADS_FIELD_HAS_TYPEFOCUS,
		);

		foreach ( $fields as $field ) {
			if ( strpos( $dir, 'left' ) === 0 ) {
				$this->variation[ $field ] = $template[ $field ];
			} else {
				$template[ $field ] = $this->variation[ $field ];
			}
		}

		return $template;
	}

	protected function config() {
		$config = include dirname( dirname( dirname( __FILE__ ) ) ) . '/editor-templates/_config.php';

		$cloud = tve_leads_get_downloaded_templates( $this->type() );
		$type  = isset( $config[ $this->type() ] ) && is_array( $config[ $this->type() ] ) ? $config[ $this->type() ] : array();

		$config[ $this->type() ] = array_merge( $type, $cloud );

		return $config;
	}

	/**
	 * get all templates available for a Form
	 *
	 * @param string $str_form_type  the form type
	 * @param bool   $get_multi_step whether or not to return also templates designed (included) for the multi-step / state forms
	 *
	 * @return array
	 */
	public function get_all( $str_form_type = '', $get_multi_step = true ) {
		$config = $this->config();

		$type = $str_form_type ? $str_form_type : $this->type();

		$type = self::tpl_type_map( $type );

		if ( ! isset( $config[ $type ] ) ) {
			return array();
		}

		foreach ( $config[ $type ] as $key => $template ) {

			if ( ! $get_multi_step && ! empty( $template['multi_step'] ) ) {
				unset( $config[ $type ][ $key ] );
				continue;
			}

			$config[ $type ][ $key ]['key'] = $type . '|' . $key;

			/** if is NOT from cloud */
			if ( ! isset( $config[ $type ][ $key ]['API_VERSION'] ) ) {
				$config[ $type ][ $key ]['thumbnail'] = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'editor-templates/' . $type . '/thumbnails/' . $key . '.png';
			}
		}

		return $config[ $type ];
	}

	/**
	 * get all multi-step templates available for a Form
	 *
	 * @param string $str_form_type the form type
	 *
	 * @return array
	 */
	public function get_multi_step_templates( $str_form_type = '' ) {
		$config = $this->config();

		$type = $str_form_type ? $str_form_type : $this->type();

		$type = self::tpl_type_map( $type );

		$config = $config['multi_step'];

		if ( ! isset( $config[ $type ] ) ) {
			return array();
		}

		foreach ( $config[ $type ] as $key => $template ) {
			$config[ $type ][ $key ]['key']       = 'multi_step|' . $type . '|' . $key;
			$config[ $type ][ $key ]['thumbnail'] = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'editor-templates/_multi_step/thumbnails/' . $type . '/' . $key . '.png';
		}

		return $config[ $type ];
	}

	/**
	 * remove and re-create all variation states based on the ones contained in the multi-step template
	 *
	 * @param string $template in the form of multi_step|{type}|{tpl}
	 */
	public function set_multi_step_template( $template ) {

		$config = $this->config();
		$config = $config['multi_step'];

		list( $ms, $type, $key ) = explode( '|', $template );

		/** append the cloud config too */
		$cloud_config = tve_leads_get_downloaded_templates( $type );
		if ( ! empty( $cloud_config['multi_step'] ) ) {
			$config = array_merge_recursive( $config, $cloud_config['multi_step'] );
		}

		if ( ! isset( $config[ $type ][ $key ] ) ) {
			return;
		}

		/**
		 * remove all variation child states
		 */
		global $tvedb;
		$tvedb->variation_delete_states( $this->variation['key'] );

		$data = $config[ $type ][ $key ];

		$to_save = array();

		$this->variation[ TVE_LEADS_FIELD_SELECTED_TEMPLATE ] = $key;

		/**
		 * step 1 - save each state and save the ID <=> index map
		 * hold references from state's index in the states array to the actual variation IDs (to be used in event manager when switching between states)
		 */
		$state_order = - 1;
		foreach ( $data['states'] as $index => $state_config ) {
			$state_order ++;
			if ( $state_order == 0 ) {
				// parent variation - this will always be the first state
				$this->variation[ TVE_LEADS_FIELD_TEMPLATE ]    = $state_config['tpl'];
				$this->variation[ TVE_LEADS_FIELD_STATE_INDEX ] = $index;
				$data['states'][ $index ]['state_key']          = $this->variation['key'];
				$data['states'][ $index ]['state_type']         = $this->type( $state_config['tpl'] );
				continue;
			}

			$child_state = $this->variation;
			unset( $child_state['key'] );

			$child_state['state_order']                   = $state_order;
			$child_state['parent_id']                     = $this->variation['key'];
			$child_state['form_state']                    = $state_config['state'];
			$child_state[ TVE_LEADS_FIELD_TEMPLATE ]      = $state_config['tpl'];
			$child_state[ TVE_LEADS_FIELD_SAVED_CONTENT ] = ''; // for now, no content
			$child_state[ TVE_LEADS_FIELD_STATE_INDEX ]   = $index;

			$child_state = tve_leads_save_form_variation( $child_state );

			$data['states'][ $index ]['state_key']  = $child_state['key'];
			$data['states'][ $index ]['state_type'] = $this->type( $state_config['tpl'] );

			$to_save [] = $child_state;

		}

		/**
		 * step 2 prepare Event Manager configuration related to the transition between states
		 */
		foreach ( $to_save as $variation ) {
			$state_type                                 = $this->type( $variation[ TVE_LEADS_FIELD_TEMPLATE ] );
			$content                                    = tve_leads_get_editor_template_content( $variation );
			$variation[ TVE_LEADS_FIELD_SAVED_CONTENT ] = $this->parse_state_events( $content, $state_type, $data['states'] );
			tve_leads_save_form_variation( $variation );
		}

		$content                                          = tve_leads_get_editor_template_content( $this->variation );
		$this->variation[ TVE_LEADS_FIELD_SAVED_CONTENT ] = $this->parse_state_events( $content, $type, $data['states'] );
	}

	/**
	 * parse and prepare event manager configuration for the 3 state-related actions : close lightbox, switch state, open lightbox state
	 *
	 * @param string $content      variation template content
	 * @param string $state_type   the current type of variation
	 * @param array  $state_config array of all states containing
	 *
	 * @return string the parsed content
	 */
	protected function parse_state_events( $content, $state_type, $state_config ) {
		$close_screen_filler = '__TCB_EVENT_[{"t":"click","a":"tl_state_sf_close","config":{}}]_TNEVE_BCT__';
		$close_lightbox      = '__TCB_EVENT_[{"t":"click","a":"tl_state_lb_close","config":{}}]_TNEVE_BCT__';
		$close_form          = '__TCB_EVENT_[{"t":"click","a":"thrive_leads_form_close","config":{}}]_TNEVE_BCT__';
		$switch_state        = '__TCB_EVENT_[{"t":"click","a":"tl_state_switch","config":{"s":"%s"}}]_TNEVE_BCT__';
		$open_lightbox       = '__TCB_EVENT_[{"t":"click","a":"tl_state_lightbox","config":{"s":"%s","a":"zoom_in"}}]_TNEVE_BCT__';

		$content = str_replace( '|close_screen_filler|', htmlspecialchars( $close_screen_filler ), $content );
		$content = str_replace( '|close_lightbox|', htmlspecialchars( $close_lightbox ), $content );
		$content = str_replace( '|close_form|', htmlspecialchars( $close_form ), $content );

		foreach ( $state_config as $state_index => $data ) {
			/**
			 * on Lightboxes and all other non-lightbox states - the action should be 'switch_state'
			 */
			if ( $state_type == 'lightbox' || $data['state_type'] != 'lightbox' ) {
				$action = $switch_state;
			} else {
				$action = $open_lightbox;
			}
			$content = str_replace(
				'|open_state_' . $state_index . '|',
				htmlspecialchars( sprintf( $action, $data['state_key'] ) ),
				$content
			);
		}

		return $content;
	}


	/**
	 * API-calls after this point
	 * --------------------------------------------------------------------
	 */
	/**
	 * choose a new template
	 */
	public function api_choose() {
		if ( ! ( $template = $this->param( 'tpl' ) ) ) {
			exit();
		}

		$parent_variation = empty( $this->variation['parent_id'] ) ? $this->variation : tve_leads_get_form_variation( null, $this->variation['parent_id'] );

		/**
		 * This is a user-saved template
		 */
		if ( strpos( $template, 'user-saved-template-' ) === 0 ) {
			/* at this point, the template is one of the previously saved templates (saved by the user) -
				it holds the index from the option array which needs to be loaded */
			$contents = get_option( self::OPTION_TPL_CONTENT );
			$meta     = get_option( self::OPTION_TPL_META );

			$template_index = intval( str_replace( 'user-saved-template-', '', $template ) );

			/* make sure we don't mess anything up */
			if ( empty( $meta ) ) {
				return;
			}

			$tpl_data = array();

			/*Backwards compatibility: the old way. The content was stored in OPTION_TPL_CONTENT meta*/
			if ( ! empty( $contents ) && ! empty( $contents[ $template_index ] ) ) {
				$tpl_data = $contents[ $template_index ];
			}

			$template = $meta[ $template_index ][ TVE_LEADS_FIELD_TEMPLATE ];

			/*New Way: the content is stored in OPTION_TPL_META meta*/
			if ( ! empty( $meta[ $template_index ] ) && ! empty( $meta[ $template_index ][ TVE_LEADS_FIELD_SAVED_CONTENT ] ) ) {
				$tpl_data = $meta[ $template_index ];
			}

//			$tpl_data = $meta[ $template_index ];
//			$template = $meta[ $template_index ][ TVE_LEADS_FIELD_TEMPLATE ];
//			if ( ! empty( $contents ) && ! empty( $contents[ $template_index ] ) ) {
//				$tpl_data = $contents[ $template_index ];
//			}
			$this->interchange_data( $tpl_data, 'left -> right' );

			$this->variation[ TVE_LEADS_FIELD_TEMPLATE ] = $template;
		} elseif ( strpos( $template, 'multi_step|' ) === 0 ) {
			/**
			 * use has selected a multi-step template, we need to:
			 *      - delete all existing variation states
			 *      - create each of the variation states that are included in the template
			 */
			$this->variation = $parent_variation;
			$this->set_multi_step_template( $template );

		} else {
			$chunks                                               = explode( '|', $template );
			$this->variation[ TVE_LEADS_FIELD_TEMPLATE ]          = $template;
			$this->variation[ TVE_LEADS_FIELD_SELECTED_TEMPLATE ] = end( $chunks );
			$this->variation[ TVE_LEADS_FIELD_SAVED_CONTENT ]     = tve_leads_get_editor_template_content( $this->variation, $template );
			$this->variation[ TVE_LEADS_FIELD_SAVED_CONTENT ]     = $this->parse_state_events( $this->variation[ TVE_LEADS_FIELD_SAVED_CONTENT ], self::type( $template ), array() );
		}

		tve_leads_save_form_variation( $this->variation );

		$state_manager = Thrive_Leads_State_Manager::instance( $parent_variation );

		return $state_manager->state_data( $this->variation );
	}

	/**
	 * reset to default content
	 */
	public function api_reset() {
		$state_type       = $this->type( $this->variation[ TVE_LEADS_FIELD_TEMPLATE ] );
		$variation_states = tve_leads_get_form_related_states( $this->variation );
		$state_config     = array();

		foreach ( $variation_states as $state ) {
			if ( empty( $state[ TVE_LEADS_FIELD_STATE_INDEX ] ) ) {
				continue;
			}
			$state_config[ $state[ TVE_LEADS_FIELD_STATE_INDEX ] ] = array(
				'state_type' => $this->type( $state[ TVE_LEADS_FIELD_TEMPLATE ] ),
				'state_key'  => $state['key'],
			);
		}

		$content                                          = tve_leads_get_editor_template_content( $this->variation );
		$this->variation[ TVE_LEADS_FIELD_SAVED_CONTENT ] = $this->parse_state_events( $content, $state_type, $state_config );
		tve_leads_save_form_variation( $this->variation );

		$variation = empty( $this->variation['parent_id'] ) ? $this->variation : tve_leads_get_form_variation( null, $this->variation['parent_id'] );

		$stateManager = Thrive_Leads_State_Manager::instance( $variation );

		return $stateManager->state_data( $this->variation );
	}

	/**
	 * get user-saved templates
	 */
	public function api_get_saved() {
		$only_current_template = (int) $this->param( 'current_template' );
		$form_type             = get_post_meta( (int) $this->param( 'post_id' ), 'tve_form_type', true );
		$config                = $this->config();
		$variation_key         = (int) $this->param( '_key' );
		$variation             = tve_leads_get_form_variation( null, $variation_key );

		//for two step lightbox we have two types of forms: lightbox and screenfiller
		if ( $form_type == 'two_step_lightbox' ) {
			//if the current variation is the default one, then we display both screen filler and lightbox saved templates
			if ( $variation['parent_id'] == 0 ) {
				$form_type = array( 'screen_filler', 'lightbox' );
			} else {
				//if this is a secondary state, we display only the templates that are the same type as the parrent
				$parent_variation = tve_leads_get_form_variation( null, $variation['parent_id'] );
				$form_type        = array( $this->type( $parent_variation ) );
			}
		} else {
			/* if the user is editing a multi-step form (e.g. a shortcode) and the current state is a lightbox state, we need to return the saved lightbox templates */
			if ( ! empty( $variation['parent_id'] ) && $variation['form_state'] == 'lightbox' ) {
				$form_type = array( 'lightbox' );
			} else {
				$form_type = array( self::tpl_type_map( $form_type ) );
			}
		}

		$templates      = get_option( self::OPTION_TPL_META );
		$templates      = empty( $templates ) ? array() : array_reverse( $templates, true ); // order by date DESC
		$json_templates = array();

		$upload_url = wp_upload_dir();
		$upload_url = $upload_url['baseurl'];

		foreach ( $templates as $index => &$template ) {
			$type = $this->type( $template );
			/* make sure we only load the same type, e.g. ribbon */
			if ( ! in_array( self::tpl_type_map( $type ), $form_type ) ) {
				continue;
			}

			if ( ! empty( $only_current_template ) && $this->variation[ TVE_LEADS_FIELD_TEMPLATE ] != $template[ TVE_LEADS_FIELD_TEMPLATE ] ) {
				continue;
			}

			$is_multi_step = ! empty( $config[ $this->type( $template ) ][ $this->key( $template ) ]['multi_step'] )
				? $config[ $this->type( $template ) ][ $this->key( $template ) ]['multi_step']
				: false;

			if ( empty( $template['thumbnail'] ) ) {
				$file = $upload_url . '/tve_leads_templates/' . $this->type( $template ) . '/' . $this->key( $template ) . '/thumbnails/' . $this->key( $template ) . '.png';
				if ( file_exists( $file ) ) {
					$template['thumbnail'] = $file;
				}
			} else {
				$template['thumbnail'] = tl_get_design_thumbnail( $template['thumbnail'] );
			}
			if ( ! empty( $template['thumbnail'] ) ) {
				$template['thumb_sizes']['w'] = getimagesize( $template['thumbnail'] )[0];
				$template['thumb_sizes']['h'] = getimagesize( $template['thumbnail'] )[1];
			}
			$template['multi_step'] = $is_multi_step;
			$template['key']        = $template['id'] = 'user-saved-template-' . $index;

			unset( $template[ TVE_LEADS_FIELD_SAVED_CONTENT ] );

			$json_templates[] = $template;
		}

		return array(
			'templates' => $json_templates,
			'success'   => true,
		);
	}

	/**
	 * Save the current variation config and content as a template so that it can later be applied to other variation
	 */
	public function api_save() {
		/**
		 * we keep the template content separately from the template meta data (name and date)
		 */
		if ( empty( $this->variation[ TVE_LEADS_FIELD_GLOBALS ] ) ) {
			$this->variation[ TVE_LEADS_FIELD_GLOBALS ] = array( 'e' => 1 );
		}
		$template_content = $this->interchange_data( array(), 'right -> left' );

		$template_meta = array(
			'name'                   => $this->param( 'name', '' ),
			TVE_LEADS_FIELD_TEMPLATE => $this->variation[ TVE_LEADS_FIELD_TEMPLATE ],
			'date'                   => date( 'Y-m-d' ),
			'thumbnail'              => $this->param( 'thumbnail', '' ),
		);
		/**
		 * We now save the content in the meta array.
		 * To avoid 2 places in the option meta where user saves templates
		 */
		$template_meta = array_merge( $template_meta, $template_content );

		$templates_meta    = get_option( self::OPTION_TPL_META ); // this should get unserialized automatically
		$templates_meta [] = $template_meta;

		update_option( self::OPTION_TPL_META, $templates_meta, 'no' );

		return array(
			'message' => __( 'Template saved.', 'thrive_leads' ),
			'list'    => $this->api_get_saved(),
			'success' => true,
		);
	}

	/**
	 * delete a previously - saved template
	 */
	public function api_delete() {
		$tpl_index = (int) str_replace( 'user-saved-template-', '', $this->param( 'tpl' ) );

		$meta = get_option( self::OPTION_TPL_META );

		if ( ! isset( $meta[ $tpl_index ] ) ) {
			return $this->api_get_saved();
		}

		array_splice( $meta, $tpl_index, 1 );

		update_option( self::OPTION_TPL_META, array_values( $meta ), 'no' );

		return $this->api_get_saved();
	}

	/**
	 * Set upload dir
	 *
	 * @param $upload
	 *
	 * @return mixed
	 */
	public static function upload_dir( $upload ) {

		$sub_dir = '/thrive-leads/thumbnails';

		$upload['path']   = $upload['basedir'] . $sub_dir;
		$upload['url']    = $upload['baseurl'] . $sub_dir;
		$upload['subdir'] = $sub_dir;

		return $upload;
	}

	/**
	 * Set upload file name
	 *
	 * @return string
	 */
	public static function get_preview_filename() {
		return self::$file_name . '.png';
	}

	/**
	 * Save custom generated thumbnail
	 *
	 * @return bool[]|false[]
	 */
	public function api_save_thumbnail() {
		$allowed_extension = array( 'png', 'jpg' );
		if ( ! isset( $_FILES['preview_file'] ) || ! in_array( pathinfo( $_FILES['preview_file']['name'], PATHINFO_EXTENSION ), $allowed_extension ) ) {
			return array(
				'success' => false,
			);
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		self::$file_name = sanitize_file_name( $this->param( 'file_name' ) );
		add_filter( 'upload_dir', array( __CLASS__, 'upload_dir' ) );

		$moved_file = wp_handle_upload( $_FILES['preview_file'], array(
			'action'                   => TVE_LEADS_ACTION_TEMPLATE,
			'unique_filename_callback' => array( __CLASS__, 'get_preview_filename' ),
		) );

		remove_filter( 'upload_dir', array( __CLASS__, 'upload_dir' ) );

		return array(
			'success' => ! empty( $moved_file['url'] ),
		);
	}
}
