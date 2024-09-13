<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVD_Smart_DB
 */
class TVD_Smart_DB {
	/**
	 * Groups table name
	 *
	 * @var string
	 */
	private $groups_table_name;

	/**
	 * Fields table name
	 *
	 * @var string
	 */
	private $fields_table_name;

	/**
	 * WordPress Database
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Default fields and data
	 *
	 * @var array
	 */
	private $default_groups;

	/**
	 * Icons specific to type
	 */
	public static $icons;

	/**
	 * Types of fields
	 */
	public static $types
		= array(
			'text'    => 0,
			'address' => 1,
			'phone'   => 2,
			'email'   => 3,
			'link'    => 4,
			//			'location' => 5,
		);

	/**
	 * TVD_Smart_DB constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb              = $wpdb;
		$this->groups_table_name = $this->wpdb->prefix . 'td_groups';
		$this->fields_table_name = $this->wpdb->prefix . 'td_fields';
		$this->default_groups    = $this->groups();
		static::$icons           = $this->icons();
	}

	/**
	 * Default icons
	 *
	 * return array
	 */
	public function icons() {
		return array(
			static::$types['text']    => 'text-new',
			static::$types['address'] => 'address-new',
			static::$types['phone']   => 'phone-new',
			static::$types['email']   => 'envelope-new',
			static::$types['link']    => 'link-new',
			//			TVD_Smart_DB::$types['location'] => 'map-marker-solid',
		);
	}

	/**
	 * Default types
	 *
	 * return array
	 */
	public static function field_types() {
		$types = array();
		foreach ( static::$types as $key => $type ) {
			$types[ static::$types[ $key ] ] = array(
				'name' => $key,
				'icon' => static::field_icon( static::$types[ $key ] ),
				'key'  => static::$types[ $key ],
			);
		}

		return $types;
	}

	/**
	 * Default fields and data
	 *
	 * return array
	 */
	public function groups() {
		return array(
			'Company' => array(
				array(
					'name'       => 'Name',
					'type'       => static::$types['text'],
					'identifier' => 'name',
				),
				array(
					'name'       => 'Address',
					'type'       => static::$types['address'],
					'identifier' => 'addr',
				),
				array(
					'name'       => 'Phone number',
					'type'       => static::$types['phone'],
					'data'       => [ 'text' => 'Call', 'url' => '' ],
					'identifier' => 'phone',
				),
				array(
					'name'       => 'Alternative phone number',
					'type'       => static::$types['phone'],
					'data'       => [ 'text' => 'Call', 'url' => '' ],
					'identifier' => 'alt_phone',
				),
				array(
					'name'       => 'Email address',
					'type'       => static::$types['email'],
					'data'       => [ 'text' => 'Email', 'url' => '' ],
					'identifier' => 'mail',
				),
				//				array(
				//					'name' => 'Map Location',
				//					'type' => TVD_Smart_DB::$types['location'],
				//				),
			),
			'Legal'   => array(
				array(
					'name'       => 'Privacy policy',
					'type'       => static::$types['link'],
					'data'       => array( 'text' => 'Privacy policy', 'url' => '' ),
					'identifier' => 'priv',
				),
				array(
					'name'       => 'Disclaimer',
					'type'       => static::$types['link'],
					'data'       => array( 'text' => 'Disclaimer', 'url' => '' ),
					'identifier' => 'disc',
				),
				array(
					'name'       => 'Terms and conditions',
					'type'       => static::$types['link'],
					'data'       => array( 'text' => 'Terms and conditions', 'url' => '' ),
					'identifier' => 'toc',
				),
				array(
					'name'       => 'Contact',
					'type'       => static::$types['link'],
					'data'       => array( 'text' => 'Contact', 'url' => '' ),
					'identifier' => 'contact',
				),
			),
			'Social'  => array(
				array(
					'name'       => 'Facebook page',
					'icon'       => 'facebook-brands-new',
					'type'       => static::$types['link'],
					'identifier' => 'fb',
				),
				array(
					'name'       => 'YouTube',
					'icon'       => 'youtube-brands-new',
					'type'       => static::$types['link'],
					'identifier' => 'yt',
				),
				array(
					'name'       => 'LinkedIn',
					'icon'       => 'linkedin-brands-new',
					'type'       => static::$types['link'],
					'identifier' => 'in',
				),
				array(
					'name'       => 'Pinterest',
					'icon'       => 'pinterest-brands-new',
					'type'       => static::$types['link'],
					'identifier' => 'pin',
				),
				array(
					'name'       => 'Instagram',
					'icon'       => 'instagram-brands-new',
					'type'       => static::$types['link'],
					'identifier' => 'ig',
				),
				array(
					'name'       => 'Xing',
					'icon'       => 'xing-brands-new',
					'type'       => static::$types['link'],
					'identifier' => 'xing',
				),
				array(
					'name'       => 'X',
					'icon'       => 'x-brands-new',
					'type'       => static::$types['link'],
					'identifier' => 't',
				),
				array(
					'name'       => 'TikTok',
					'icon'       => 'tiktok-brands-new',
					'type'       => static::$types['link'],
					'identifier' => 'tiktok',
				),
			),
		);
	}

	/**
	 * Insert the default data in the db
	 */
	public function insert_default_data() {
		/**
		 * We can't use the migration queries in the migration file because we have relationships, so we insert the data here
		 */
		$result = $this->wpdb->get_row( "SELECT `id` FROM $this->groups_table_name  LIMIT 0,1", ARRAY_A );

		/* if there's no entries in the table, this is the first ( 1.0.0 ) db update */
		if ( empty( $result ) ) {
			foreach ( $this->default_groups as $group => $fields ) {

				/**
				 * Insert the group
				 */
				$result = $this->wpdb->insert(
					$this->groups_table_name,
					array(
						'name'       => $group,
						'is_default' => 1,
					),
					array(
						'%s',
					)
				);
				$id     = $this->wpdb->insert_id;

				if ( $result ) {
					/**
					 * Insert the fields
					 */
					foreach ( $fields as $field ) {
						$this->wpdb->insert(
							$this->fields_table_name,
							array(
								'name'       => $field['name'],
								'type'       => $field['type'],
								'identifier' => $field['identifier'],
								'data'       => empty( $field['data'] ) ? null : maybe_serialize( $field['data'] ),
								'is_default' => 1,
								'group_id'   => $id,
							),
							array(
								'%s',
								'%d',
							)
						);
					}
				}
			}
		} else {
			/*
			 * there are entries in the table => this is the 1.0.X -> 1.0.(X+1) db upgrade
			 * Insert 'Xing' and 'Twitter' manually, and populate the newly added 'identifier' column for each default field
			 */
			$xing_twitter_row = $this->wpdb->get_row( "SELECT * FROM $this->fields_table_name WHERE `identifier`='xing' OR `identifier`='t' LIMIT 0,1", ARRAY_A );

			/* only continue if xing and twitter aren't already inserted */
			if ( empty( $xing_twitter_row ) ) {
				$this->insert_xing_and_twitter_default_fields();
				$this->add_string_identifier_to_fields();
			}

			$tiktok_row = $this->wpdb->get_row( "SELECT * FROM $this->fields_table_name WHERE `identifier`='tiktok' LIMIT 0,1", ARRAY_A );

			/* only continue if tiktok isn't inserted */
			if ( empty( $tiktok_row ) ) {
				$this->add_tiktok_field();
			}
		}
	}

	public function add_tiktok_field() {
		$format = array(
			'%s',
			'%d',
		);

		$this->wpdb->insert(
			$this->fields_table_name,
			array(
				'name'       => 'TikTok',
				'type'       => static::$types['link'],
				'identifier' => 'tiktok',
				'is_default' => 1,
				'group_id'   => 3,
			),
			$format
		);
	}

	/**
	 * Insert the xing and twitter social fields and add their IDs to the 'id - string identifier' map
	 */
	private function insert_xing_and_twitter_default_fields() {
		$format = array(
			'%s',
			'%d',
		);

		$this->wpdb->insert(
			$this->fields_table_name,
			array(
				'name'       => 'Xing',
				'type'       => static::$types['link'],
				'identifier' => 'xing',
				'is_default' => 1,
				'group_id'   => 3,
			),
			$format
		);

		$this->wpdb->insert(
			$this->fields_table_name,
			array(
				'name'       => 'X',
				'type'       => static::$types['link'],
				'identifier' => 't',
				'is_default' => 1,
				'group_id'   => 3,
			),
			$format
		);
	}

	/**
	 * Add a string 'identifier' to the proper column for all the default field rows
	 */
	private function add_string_identifier_to_fields() {
		foreach ( $this->type_id_map as $id => $identifier ) {
			$this->wpdb->update( $this->fields_table_name, array( 'identifier' => $identifier ), array( 'id' => $id ) );
		}
	}

	/**
	 * Get groups with fields
	 *
	 * @param int     $id
	 * @param boolean $with_fields
	 *
	 * @return array|object|null
	 */
	public function get_groups( $id = 0, $with_fields = true ) {
		$args  = array();
		$query = 'SELECT * FROM ' . $this->groups_table_name;
		if ( $id ) {
			$where  = ' WHERE id = %d';
			$args[] = $id;
		} else {
			/**
			 * We need this so WPDB won't complain about not preparing the data correctly
			 */
			$where  = ' WHERE 1 = %d';
			$args[] = 1;
		}

		$query .= $where;

		$results = $this->wpdb->get_results( $this->wpdb->prepare( $query, $args ), ARRAY_A );

		/* I did not write this, I only rewrote it a bit, please don't assassinate me */
		if ( $results && $with_fields ) {
			foreach ( $results as $group_index => $group ) {
				$group_name   = $results[ $group_index ]['name'];
				$group_config = empty( $this->default_groups[ $group_name ] ) ? array() : $this->default_groups[ $group_name ];

				$fields = $this->get_fields( $group );

				if ( ! empty( $fields ) ) {
					foreach ( $fields as $index => $field ) {
						$field['group_name'] = $group['name'];

						if ( ! empty( $fields[ $index ]['name'] ) ) {
							$fields[ $index ]['name'] = $this->ensure_lowercase_label( $fields[ $index ]['name'] );
						}

						$fields[ $index ]['formated_data'] = empty( $field['data'] ) ? '' : static::format_field_data( maybe_unserialize( $field['data'] ), $field );
						$fields[ $index ]['data']          = empty( $field['data'] ) ? '' : maybe_unserialize( $field['data'] );

						$field_config = empty( $field['identifier'] ) ? array() : $this->get_config_for_identifier( $group_config, $field['identifier'] );

						if ( empty( $field_config ) || empty( $field_config['icon'] ) ) {
							$icon = static::field_icon( $field['type'] );
						} else {
							$icon = dashboard_icon( $field_config['icon'], true );
						}

						$fields[ $index ]['icon']          = $icon;
						$fields[ $index ]['default_field'] = empty( $field_config ) ? 0 : 1;
					}
				}

				$results[ $group_index ]['default_field'] = empty( $group_config ) ? 0 : 1;
				$results[ $group_index ]['fields']        = $fields;
			}
		}

		return $results;
	}

	/**
	 * For the current identifier, search the array of configs and return the right one
	 *
	 * @param $default_group
	 * @param $identifier
	 *
	 * @return array|mixed
	 */
	private function get_config_for_identifier( $default_group, $identifier ) {
		$config = array();

		foreach ( $default_group as $index => $field ) {
			if ( ! empty( $field['identifier'] ) && $field['identifier'] === $identifier ) {
				$config = $field;
				break;
			}
		}

		return $config;
	}

	/**
	 * Make sure some specific default labels ( that are stored in the DB ) are lowercase
	 *
	 * @param string $label
	 *
	 * @return string
	 */
	private function ensure_lowercase_label( $label ) {
		if ( $label === 'Terms and Conditions' ) {
			$label = 'Terms and conditions';
		} else if ( $label === 'Facebook Page' ) {
			$label = 'Facebook page';
		}

		return $label;
	}

	/**
	 * @param $field_type
	 *
	 * @return mixed
	 */
	public static function field_icon( $field_type ) {
		return dashboard_icon( static::$icons[ $field_type ], true );
	}

	public static function format_field_data( $field_data, $field, $args = array() ) {
		$data        = '';
		$unavailable = '';
		if ( apply_filters( 'td_smartsite_shortcode_tooltip', false ) ) {
			$unavailable_name = empty( $field['group_name'] ) ? $field['name'] : '[' . $field['group_name'] . '] ' . $field['name'];
			$unavailable      = '<span class="thrive-inline-shortcode-unavailable">' .
			                    '<span class="thrive-shortcode-notice">' .
			                    '!' .
			                    '</span>' .
			                    $unavailable_name .
			                    '</span>';
			if ( empty( $args['hide-tooltip'] ) ) {
				$unavailable .= '<span class="thrive-tooltip-wrapper">' .
				                '<span class="thrive-shortcode-tooltip">' .
				                __( 'This global variable hasn\'t been set.  Define your global variables in the', 'thrive-dash' ) .
				                '<br>' .
				                '<a><span onClick=window.open("' . add_query_arg( 'page', 'tve_dash_smart_site', admin_url( 'admin.php' ) ) . '","_blank") >' . ' ' . __( ' Global Fields dashboard', 'thrive-dash' ) . '</span></a>' .
				                '</span>' .
				                '</span>';
			}
		}

		switch ( (int) $field['type'] ) {
			// text field
			case TVD_Smart_DB::$types['text']:
				$data = empty( $field_data['text'] ) ? $unavailable : $field_data['text'];
				break;
			//address field
			case TVD_Smart_DB::$types['address']:
				if ( empty( $field_data['address1'] ) ) {
					$data = $unavailable;
				} else {
					$address_fields = [ 'address1', 'address2', 'city', 'state', 'zip', 'country' ];
					$field_data     = array_replace( array_fill_keys( $address_fields, null ), $field_data );
					$data           = implode( empty( $args['multiline'] ) ? ', ' : '<br>', array_filter( $field_data ) );
				}
				break;
			// phone field
			case TVD_Smart_DB::$types['phone']:
				$data = empty( $field_data['phone'] ) ? $unavailable : $field_data['phone'];
				break;
			// email field
			case TVD_Smart_DB::$types['email']:
				$data = empty( $field_data['email'] ) ? $unavailable : $field_data['email'];
				break;
			//link field
			case TVD_Smart_DB::$types['link']:
				if ( empty( $field_data['url'] ) ) {
					$data = $unavailable;
				} else {
					if ( empty( $args['prevent-link'] ) ) {
						$link_attr = TVD_Smart_Shortcodes::tvd_decode_link_attributes( $args );

						$field_value = '<a ' . ( ! empty( $args['link-css-attr'] ) ? 'data-css="' . $args['link-css-attr'] . '"' : '' ) . ' href="' . $field_data['url'] . '" target="' . ( ! empty( $link_attr['target'] ) ? $link_attr['target'] : '' ) . '">' . $field_data['text'] . '</a>';
					} else {
						$field_value = $field_data['text'];
					}
					$data = $field_value;
				}
				break;
			// location field
			case TVD_Smart_DB::$types['location']:
				$url = 'https://maps.google.com/maps?q=' . urlencode( empty( $field_data['location'] ) ? 'New York' : $field_data['location'] ) . '&t=m&z=10&output=embed&iwloc=near';

				$data = '<iframe frameborder="0" marginheight="0" marginwidth="0" src="' . $url . '"></iframe>';
				break;
		}

		return $data;
	}

	/**
	 * Get fields for group or by ID
	 *
	 * @param array $group
	 * @param int   $id
	 *
	 * @return array|object|null
	 */
	public function get_fields( $group = array(), $id = 0 ) {
		if ( $group ) {
			$where  = ' WHERE group_id = %d';
			$args[] = $group['id'];
		} else {
			/**
			 * We need this so WPDB won't complain about not preparing the data correctly
			 */
			$where  = ' WHERE 1 = %d';
			$args[] = 1;
		}

		if ( $id ) {
			if ( is_numeric( $id ) ) {
				$where .= ' AND id = %d';
			} else {
				$where .= ' AND identifier = %s';
			}

			$args[] = $id;
		}

		$query = $this->wpdb->prepare( 'SELECT * FROM ' . $this->fields_table_name . $where, $args );

		if ( ! $id ) {
			$results = $this->wpdb->get_results( $query, ARRAY_A );
		} else {
			$results = $this->wpdb->get_row( $query, ARRAY_A );
		}


		return $results;
	}

	/**
	 * Get fields of a specific type which have some data
	 *
	 * @param array $group_id
	 * @param int   $type
	 *
	 * @return array|object|null
	 */
	public function get_fields_by_type( $group_id = array(), $type = 0 ) {
		if ( $type ) {
			$where  = ' WHERE type = %d';
			$args[] = $type;
		} else {
			return array();
		}

		if ( $group_id ) {
			$where  .= ' AND group_id = %d';
			$args[] = $group_id;
		} else {
			$where  .= ' AND 1 = %d';
			$args[] = 1;
		}

		$query = $this->wpdb->prepare( 'SELECT * FROM ' . $this->fields_table_name . $where, $args );

		return $this->wpdb->get_results( $query, ARRAY_A );
	}

	public static function save_field( $model, $action ) {
		$rep = array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
		);

		global $wpdb;

		// Add new field
		if ( $action === 'insert' ) {
			$model['created_at'] = date( 'Y-m-d h:i:s' );
			$result              = $wpdb->insert(
				$wpdb->prefix . 'td_fields',
				array(
					'group_id'   => $model['group_id'],
					'name'       => $model['name'],
					'type'       => $model['type'],
					'data'       => maybe_serialize( $model['data'] ),
					'created_at' => $model['created_at'],
				),
				$rep
			);
			$model['id']         = (string) $wpdb->insert_id;
		} else {
			// Update existing field
			$model['updated_at'] = date( 'Y-m-d h:i:s' );
			$result              = $wpdb->update(
				$wpdb->prefix . 'td_fields',
				array(
					'group_id'   => (int) $model['group_id'],
					'name'       => $model['name'],
					'type'       => $model['type'],
					'data'       => maybe_serialize( $model['data'] ),
					'updated_at' => $model['updated_at'],
				),
				array( 'id' => $model['id'] ),
				$rep,
				array( '%d' )
			);
		}

		$model['formated_data'] = static::format_field_data( $model['data'], $model );
		$model['icon']          = empty( $model['icon'] ) ? static::field_icon( $model['type'] ) : $model['icon'];

		return $result ? $model : false;
	}

	public static function delete_field( $id ) {
		global $wpdb;

		return $wpdb->delete( $wpdb->prefix . 'td_fields', array( 'id' => $id ) );
	}

	public static function insert_group( $model ) {

		$model['created_at'] = date( 'Y-m-d h:i:s' );

		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->prefix . 'td_groups',
			array(
				'name'       => $model['name'],
				'created_at' => $model['created_at'],
			),
			array(
				'%s',
				'%s',
			)
		);

		$model['id'] = (string) $wpdb->insert_id;

		return $result ? $model : false;
	}

	public static function update_group( $model ) {

		global $wpdb;
		$model['updated_at'] = date( 'Y-m-d h:i:s' );

		$result = $wpdb->update(
			$wpdb->prefix . 'td_groups',
			array(
				'name'       => $model['name'],
				'updated_at' => $model['updated_at'],
			),
			array( 'id' => $model['id'] ),
			array( '%s', '%s', ),
			array( '%d' )
		);

		return $result ? $model : false;
	}

	public static function delete_group( $id ) {
		global $wpdb;

		$result = $wpdb->delete( $wpdb->prefix . 'td_groups', array( 'id' => $id ) );
		if ( $result ) {
			$wpdb->delete( $wpdb->prefix . 'td_fields', array( 'group_id' => $id ), array( '%d' ) );
		}

		return $result;
	}

	/*
	 * we're certain that these IDs have these identifiers because they were there from the start and were inserted in this specific order
	 * the 'identifier' is the same as the one from the groups() function
	 * in theory this should never be modified again, it's used only for backwards compatibility when upgrading the DB from 1.0.0 to 1.0.1
	 */
	public $type_id_map = array(
		1  => 'name',
		2  => 'addr',
		3  => 'phone',
		4  => 'alt_phone',
		5  => 'mail',
		6  => 'priv',
		7  => 'disc',
		8  => 'toc',
		9  => 'contact',
		10 => 'fb',
		11 => 'yt',
		12 => 'in',
		13 => 'pin',
		14 => 'ig',
	);
}
