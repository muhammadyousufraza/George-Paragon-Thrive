<?php

/**
 * Include Thrive Ovation instances into tcb
 *
 * @param $instances
 *
 * @return mixed
 */
function tvo_tcb_element_instances( $instances ) {
	if ( ! class_exists( 'TCB_Capture_Testimonials_v2', false ) ) {
		require_once __DIR__ . '/classes/class-tcb-capture-testimonials-v2.php';
		require_once __DIR__ . '/classes/class-tcb-capture-testimonials-element.php';
		require_once __DIR__ . '/classes/class-tcb-display-testimonials-element.php';
	}
	$instances['capture_testimonials_v2'] = new TCB_Capture_Testimonials_v2( 'capture_testimonials_v2' );
	$instances['ovation_capture']         = new TCB_Capture_Testimonials( 'ovation_capture' );
	$instances['ovation_display']         = new TCB_Display_Testimonials( 'ovation_display' );

	return $instances;
}


/**
 * Enqueue tcb scripts
 */
function tvo_tcb_enqueue_scripts() {
	tvo_enqueue_script( 'tvo_tcb_editor', TVO_URL . 'tcb-bridge/js/tar-editor' . \TCB_Utils::get_js_suffix(), [], false, true );
	tvo_enqueue_editor_scripts();
}

/**
 * Add lightbox templates to TCB 2
 *
 * @param $modals
 *
 * @return mixed
 */
function tvo_tcb_add_modal_templates( $modals ) {

	array_push( $modals,
		dirname( __FILE__ ) . '/templates/capture-testimonial-templates-lightbox.php',
		dirname( __FILE__ ) . '/templates/capture-form-settings-lightbox.php',
		dirname( __FILE__ ) . '/templates/display-settings-lightbox.php',
		dirname( __FILE__ ) . '/templates/display-testimonial-templates-lightbox.php'
	);

	return $modals;
}

/**
 * Return capture testimonial menu component
 *
 * @return string
 */
function tvo_tcb_capture_menu_path() {
	return dirname( __FILE__ ) . '/templates/tcb-menu-capture.php';
}

/**
 * Return capture testimonial menu component
 *
 * @return string
 */
function tvo_tcb_display_menu_path() {
	return dirname( __FILE__ ) . '/templates/tcb-menu-display.php';
}

/**
 * Include ovation backbone templates into tcb
 *
 * @param $templates
 *
 * @return array
 */
function tvo_tcb_add_backbone_templates( $templates ) {

	$ovation_templates = tve_dash_get_backbone_templates( dirname( __FILE__ ) . '/templates/backbone' );

	return array_merge( $templates, $ovation_templates );
}

/**
 * Enqueue thrlider inside the editor
 */
function tvo_tcb_load_editor_scripts() {
	wp_enqueue_script( 'thrlider', TVO_URL . 'tcb-bridge/js/libs/thrlider.min.js', array( 'jquery' ), false, true );

	tvo_enqueue_style( 'tvo_tcb_style', TVO_URL . 'tcb-bridge/css/editor.css' );
}

/**
 * Add some Ovation post types to Architect Post Grid Element Banned Types
 *
 * @param array $banned_types
 *
 * @return array
 */
function tvo_add_post_grid_banned_types( $banned_types = array() ) {
	$banned_types[] = TVO_TESTIMONIAL_POST_TYPE;
	$banned_types[] = TVO_SHORTCODE_POST_TYPE;

	return $banned_types;
}

/**
 * Include scripts needed for tcb integration
 */
function tvo_enqueue_editor_scripts() {

	tvo_enqueue_script( 'backbone' );

	tvo_enqueue_script( 'tvo_testimonials', TVO_URL . 'tcb-bridge/js/testimonials.min.js', array( 'jquery' ), false, true );

	wp_enqueue_script( 'thrlider', TVO_URL . 'tcb-bridge/js/libs/thrlider.min.js', array( 'jquery' ), false, true );

	wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery' ) );

	if ( ! defined( 'TVE_EDITOR_FLAG' ) || ( defined( 'TVE_EDITOR_FLAG' ) && empty( $_GET[ TVE_EDITOR_FLAG ] ) ) ) {
		/*Select 2*/
		tvo_enqueue_script( 'tvo-select2-script', TVO_URL . 'tcb-bridge/js/libs/select2.min.js' );
		tvo_enqueue_style( 'tvo-select2-style', TVE_DASH_URL . '/css/select2.css' );
	}


	tvo_enqueue_style( 'tvo_tcb_style', TVO_URL . 'tcb-bridge/css/editor_to.css' );
	wp_localize_script( 'tvo_testimonials', 'TVO_Front', array(
		'nonce'                         => wp_create_nonce( 'wp_rest' ),
		'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
		'all_tags'                      => tvo_get_all_tags(),
		'shortcode_id'                  => get_the_ID(),
		'testimonial_image_placeholder' => tvo_get_default_image_placeholder(),
		'routes'                        => array(
			'testimonials' => tvo_get_route_url( 'testimonials' ),
			'shortcodes'   => tvo_get_route_url( 'shortcodes' ),
			'tags'         => tvo_get_route_url( 'tags' ),
		),
		'const'                         => array(
			'ready_to_display_status' => TVO_STATUS_READY_FOR_DISPLAY,
		),
		'translations'                  => array(
			'bigger_value' => __( "The 'from' value should be smaller than the 'to' value", 'thrive-ovation' ),
			'only_numbers' => __( 'Please insert only numbers', 'thrive-ovation' ),
		),
	) );
}

/**
 * return an array with tcb testimonials
 *
 * @param $type string
 *
 * @return array
 */
function tvo_get_testimonial_templates( $type ) {

	$templates = array();

	switch ( $type ) {
		case 'capture':
			$templates = array(
				'default-template' => array(
					'name'      => 'Default Capture',
					'file'      => 'default-template',
					'thumbnail' => TVO_URL . 'templates/thumbnails/capture/default-template.png',
					'css'       => array( 'capture/default-template.css' ),
				),
				'set1-template'    => array(
					'name'      => 'Capture 01',
					'file'      => 'set1-template',
					'thumbnail' => TVO_URL . 'templates/thumbnails/capture/set1-template.png',
					'css'       => array( 'capture/set1-template.css' ),
				),
				'set2-template'    => array(
					'name'      => 'Capture 02',
					'file'      => 'set2-template',
					'thumbnail' => TVO_URL . 'templates/thumbnails/capture/set2-template.png',
					'css'       => array( 'capture/set2-template.css' ),
				),
			);
			break;
		case 'display':
			/**
			 * type: grid, single, slider
			 * fonts, css: array
			 */
			$templates = array(
				'grid/default-template-grid'     => array(
					'name'      => 'Default Grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/default-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/default-template.css' ),
				),
				'grid/set2-template-grid'        => array(
					'name'      => 'Grid 02',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set2-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set2-template.css' ),
				),
				'grid/set3-template-grid'        => array(
					'name'      => 'Grid 03',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set3-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set3-template.css' ),
				),
				'grid/set4-template-grid'        => array(
					'name'      => 'Grid 04',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set4-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set4-template.css' ),
				),
				'grid/set5-template-grid'        => array(
					'name'      => 'Grid 05',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set5-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set5-template.css' ),
				),
				'grid/set6-template-grid'        => array(
					'name'      => 'Grid 06',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set6-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set6-template.css' ),
				),
				'grid/set7-template-grid'        => array(
					'name'      => 'Grid 07',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set7-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set7-template.css' ),
				),
				'grid/set8-template-grid'        => array(
					'name'      => 'Grid 08',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set8-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set8-template.css' ),
				),
				'grid/set12-template-grid'       => array(
					'name'      => 'Grid 12',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set12-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set12-template.css' ),
				),
				'grid/set13-template-grid'       => array(
					'name'      => 'Grid 13',
					'file'      => 'grid/set13-template-grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set13-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set13-template.css' ),
				),
				'grid/set14-template-grid'       => array(
					'name'      => 'Grid 14',
					'file'      => 'grid/set14-template-grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set14-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set14-template.css' ),
				),
				'grid/set15-template-grid'       => array(
					'name'      => 'Grid 15',
					'file'      => 'grid/set15-template-grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set15-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set15-template.css' ),
				),
				'grid/set16-template-grid'       => array(
					'name'      => 'Grid 16',
					'file'      => 'grid/set16-template-grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/grid/set16-template.png',
					'type'      => 'grid',
					'css'       => array( 'display/grid/set16-template.css' ),
				),
				'single/default-template-single' => array(
					'name'      => 'Default Single',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/default-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/default-template.css' ),
				),
				'single/set3-template-single'    => array(
					'name'      => 'Single 03',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set3-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set3-template.css' ),
				),
				'single/set4-template-single'    => array(
					'name'      => 'Single 04',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set4-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set4-template.css' ),
				),
				'single/set5-template-single'    => array(
					'name'      => 'Single 05',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set5-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set5-template.css' ),
				),
				'single/set6-template-single'    => array(
					'name'      => 'Single 06',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set6-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set6-template.css' ),
				),
				'single/set7-template-single'    => array(
					'name'      => 'Single 07',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set7-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set7-template.css' ),
				),
				'single/set8-template-single'    => array(
					'name'      => 'Single 08',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set8-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set8-template.css' ),
				),
				'single/set12-template-single'   => array(
					'name'      => 'Single 12',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set12-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set12-template.css' ),
				),
				'single/set13-template-single'   => array(
					'name'      => 'Single 13',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set13-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set13-template.css' ),
				),
				'single/set14-template-single'   => array(
					'name'      => 'Single 14',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set14-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set14-template.css' ),
				),
				'single/set15-template-single'   => array(
					'name'      => 'Single 15',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set15-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set15-template.css' ),
				),
				'single/set17-template-single'   => array(
					'name'      => 'Single 17',
					'thumbnail' => TVO_URL . 'templates/thumbnails/single/set17-template.png',
					'type'      => 'single',
					'css'       => array( 'display/single/set17-template.css' ),
				),
				'slider/default-template-slider' => array(
					'name'      => 'Default Slider',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/default-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/default-template.css' ),
				),
				'slider/set1-template-slider'    => array(
					'name'      => 'Slider 01',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set1-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set1-template.css' ),
				),
				'slider/set2-template-slider'    => array(
					'name'      => 'Slider 02',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set2-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set2-template.css' ),
				),
				'slider/set9-template-slider'    => array(
					'name'      => 'Slider 09',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set9-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set9-template.css' ),
				),
				'slider/set10-template-slider'   => array(
					'name'      => 'Slider 10',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set10-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set10-template.css' ),
				),
				'slider/set11-template-slider'   => array(
					'name'      => 'Slider 11',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set11-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set11-template.css' ),
				),
				'slider/set14-template-slider'   => array(
					'name'      => 'Slider 14',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set14-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set14-template.css' ),
				),
				'slider/set15-template-slider'   => array(
					'name'      => 'Slider 15',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set15-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set15-template.css' ),
				),
				'slider/set16-template-slider'   => array(
					'name'      => 'Slider 16',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set16-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set16-template.css' ),
				),
				'slider/set18-template-slider'   => array(
					'name'      => 'Slider 18',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set18-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set18-template.css' ),
				),
				'slider/set19-template-slider'   => array(
					'name'      => 'Slider 19',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set19-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set19-template.css' ),
				),
				'slider/set20-template-slider'   => array(
					'name'      => 'Slider 20',
					'thumbnail' => TVO_URL . 'templates/thumbnails/slider/set20-template.png',
					'type'      => 'slider',
					'css'       => array( 'display/slider/set20-template.css' ),
				),
				'no-image/set1'                  => array(
					'name'      => 'No image 1',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/1_list.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set1.css' ),
				),
				'no-image/set1-grid'             => array(
					'name'      => 'No image 1 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/1_grid.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set1-grid.css' ),
				),
				'no-image/set2'                  => array(
					'name'      => 'No image 2',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/2_list.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set2.css' ),
				),
				'no-image/set2-grid'             => array(
					'name'      => 'No image 2 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/2_grid.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set2-grid.css' ),
				),
				'no-image/set3'                  => array(
					'name'      => 'No image 3',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/3_list.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set3.css' ),
				),
				'no-image/set3-grid'             => array(
					'name'      => 'No image 3 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/3_grid.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set3-grid.css' ),
				),
				'no-image/set4'                  => array(
					'name'      => 'No image 4',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/4_list.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set4.css' ),
				),
				'no-image/set4-grid'             => array(
					'name'      => 'No image 4 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/4_grid.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set4-grid.css' ),
				),
				'no-image/set5'                  => array(
					'name'      => 'No image 5',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/5_list.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set5.css' ),
				),
				'no-image/set5-grid'             => array(
					'name'      => 'No image 5 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/5_grid.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set5-grid.css' ),
				),
				'no-image/set6'                  => array(
					'name'      => 'No image 6',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/6_list.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set6.css' ),
				),
				'no-image/set6-grid'             => array(
					'name'      => 'No image 6 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/6_grid.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set6-grid.css' ),
				),
				'no-image/set6-slider'           => array(
					'name'      => 'No image 6 - slider',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/6_slider.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set6-slider.css' ),
				),
				'no-image/set7'                  => array(
					'name'      => 'No image 7',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/7_list.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set7.css' ),
				),
				'no-image/set7-slider'           => array(
					'name'      => 'No image 7 - slider',
					'thumbnail' => TVO_URL . 'templates/thumbnails/no-image/7_slider.png',
					'type'      => 'no-image',
					'css'       => array( 'display/no-image/set7-slider.css' ),
				),
				'small/grid/set1-template-grid'  => array(
					'name'      => 'Small 1 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/grid/1a.png',
					'type'      => 'small',
					'css'       => array( 'display/small/grid/set1-template.css' ),
				),
				'small/list/set1-template-list'  => array(
					'name'      => 'Small 1 - list',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/list/set1-template.png',
					'type'      => 'small',
					'css'       => array( 'display/small/list/set1-template.css' ),
				),
				'small/grid/set2-template-grid'  => array(
					'name'      => 'Small 2 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/grid/set2-template.png',
					'type'      => 'small',
					'css'       => array( 'display/small/grid/set2-template.css' ),
				),
				'small/list/set2-template-list'  => array(
					'name'      => 'Small 2 - list',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/list/set2-template.png',
					'type'      => 'small',
					'css'       => array( 'display/small/list/set2-template.css' ),
				),
				'small/grid/set3-template-grid'  => array(
					'name'      => 'Small 3 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/grid/set3-template.png',
					'type'      => 'small',
					'css'       => array( 'display/small/grid/set3-template.css' ),
				),
				'small/list/set3-template-list'  => array(
					'name'      => 'Small 3 - list',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/list/set3-template.png',
					'type'      => 'small',
					'css'       => array( 'display/small/list/set3-template.css' ),
				),
				'small/grid/set4-template-grid'  => array(
					'name'      => 'Small 4 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/grid/set4-template.png',
					'type'      => 'small',
					'css'       => array( 'display/small/grid/set4-template.css' ),
				),
				'small/list/set4-template-list'  => array(
					'name'      => 'Small 4 - list',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/list/set4-template.png',
					'type'      => 'small',
					'css'       => array( 'display/small/list/set4-template.css' ),
				),
				'small/grid/set5-template-grid'  => array(
					'name'      => 'Small 5 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/grid/set5-template.png',
					'type'      => 'small',
					'css'       => array( 'display/small/grid/set5-template.css' ),
				),
				'small/list/set5-template-list'  => array(
					'name'      => 'Small 5 - list',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/list/set5-template.png',
					'type'      => 'small',
					'css'       => array( 'display/small/list/set5-template.css' ),
				),
				'small/grid/set6-template-grid'  => array(
					'name'      => 'Small 6 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/grid/2a.png',
					'type'      => 'small',
					'css'       => array( 'display/small/grid/set6-template.css' ),
				),
				'small/list/set6-template-list'  => array(
					'name'      => 'Small 6 - list',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/list/2a.png',
					'type'      => 'small',
					'css'       => array( 'display/small/list/set6-template.css' ),
				),
				'small/grid/set7-template-grid'  => array(
					'name'      => 'Small 7 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/grid/3a.png',
					'type'      => 'small',
					'css'       => array( 'display/small/grid/set7-template.css' ),
				),
				'small/list/set7-template-list'  => array(
					'name'      => 'Small 7 - list',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/list/3a.png',
					'type'      => 'small',
					'css'       => array( 'display/small/list/set7-template.css' ),
				),
				'small/grid/set8-template-grid'  => array(
					'name'      => 'Small 8 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/grid/4a.png',
					'type'      => 'small',
					'css'       => array( 'display/small/grid/set8-template.css' ),
				),
				'small/list/set8-template-list'  => array(
					'name'      => 'Small 8 - list',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/list/4a.png',
					'type'      => 'small',
					'css'       => array( 'display/small/list/set8-template.css' ),
				),
				'small/grid/set9-template-grid'  => array(
					'name'      => 'Small 9 - grid',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/grid/5a.png',
					'type'      => 'small',
					'css'       => array( 'display/small/grid/set9-template.css' ),
				),
				'small/list/set9-template-list'  => array(
					'name'      => 'Small 9 - list',
					'thumbnail' => TVO_URL . 'templates/thumbnails/small/list/5a.png',
					'type'      => 'small',
					'css'       => array( 'display/small/list/set9-template.css' ),
				),
			);
			break;
	}

	return $templates;
}

/**
 * Display or hide shortcode post preview
 *
 * @return mixed
 */
function tvo_shortcode_post() {

	global $post;

	if ( ! empty( $post ) && $post->post_type === TVO_SHORTCODE_POST_TYPE ) {
		if ( is_user_logged_in() && TVO_Product::has_access() ) {
			$shortcode_type = get_post_meta( $post->ID, 'tvo_shortcode_type', true );

			if ( ! empty( $shortcode_type ) ) {
				$post->post_content = '<div id="tvo_' . $shortcode_type . '_shortcode" data-id="' . $post->ID . '"></div>';
				add_action( 'wp_footer', 'tvo_wp_footer' );
			}

			/* we use the same scripts from  */
			tvo_enqueue_editor_scripts();

			tvo_load_frontend_scripts();
		} else {
			/* logged in admins can see the preview */
			ob_start();
			include dirname( __FILE__ ) . '/frontend/views/no-access.php';
			$output = ob_get_contents();
			ob_end_clean();
			$post->post_title   = '';
			$post->post_content = $output;
		}
	}
}

/**
 * Include footer html - menu and lightbox
 */
function tvo_wp_footer() {
	$post_id        = get_the_ID();
	$shortcode_type = get_post_meta( $post_id, 'tvo_shortcode_type', true );
	if ( ! empty( $post_id ) && ! empty( $shortcode_type ) ) {
		include dirname( __FILE__ ) . '/frontend/views/menu/' . $shortcode_type . '-testimonial.php';

		tvo_include_frontend_lightbox( $shortcode_type );

		include dirname( __FILE__ ) . '/frontend/views/preloader.php';
		$templates = tve_dash_get_backbone_templates( dirname( __FILE__ ) . '/templates/backbone', 'backbone' );
		tve_dash_output_backbone_templates( $templates, 'tve-' );
	}
}

/**
 * Include lightboxes needed for shortcode preview
 *
 * @param      $type string
 * @param bool $echo
 *
 * @return string
 */
function tvo_include_frontend_lightbox( $type, $echo = true ) {

	$files = array();
	switch ( $type ) {
		case 'capture':
			$files = array(
				'testimonial-templates' => dirname( __FILE__ ) . '/templates/capture-testimonial-templates-lightbox.php',
				'form-settings'         => dirname( __FILE__ ) . '/templates/capture-form-settings-lightbox.php',
			);
			break;
		case 'display':
			$files = array(
				'display-testimonials'  => dirname( __FILE__ ) . '/templates/display-settings-lightbox.php',
				'testimonial-templates' => dirname( __FILE__ ) . '/templates/display-testimonial-templates-lightbox.php',
			);
			break;
	}

	$content = '';
	foreach ( $files as $name => $file ) {
		ob_start();
		include $file;
		$output = ob_get_contents();
		ob_end_clean();

		$content .= sprintf( '
			<div id="tvo-%s" class="tvd-modal">
	  			<div class="tvd-modal-content">
	  				<span class="tvd-modal-close">X</span>
	  				%s
				</div>
	  		</div>', $name, $output );
	}

	if ( $echo ) {
		echo $content;
	} else {
		return $content;
	}
}

/**
 * Load scripts needed for live preview
 */
function tvo_load_frontend_scripts() {

	$id = get_the_ID();
	if ( empty( $id ) ) {
		return;
	}

	if ( wp_script_is( 'tvo_frontend_script', 'enqueued' ) ) {
		return;
	}

	tvo_enqueue_script( 'tvo_frontend_script', TVO_URL . 'tcb-bridge/frontend/js/frontend.min.js', array(), false, true );
	tvo_enqueue_script( 'tvo_frontend_velocity', TVO_URL . 'tcb-bridge/frontend/js/libs/velocity.js', array(), false, true );
	tvo_enqueue_script( 'tvo_frontend_modal', TVO_URL . 'tcb-bridge/frontend/js/libs/leanModal.js', array(), false, true );

	tvo_enqueue_style( 'tvo_frontend_style', TVO_URL . 'tcb-bridge/css/style.css' );
	tvo_enqueue_style( 'tvo_frontend_modal', TVE_DASH_URL . '/css/modal.css' );
	tvo_enqueue_style( 'tvo_frontend_preloader', TVE_DASH_URL . '/css/preloader.css' );

	wp_localize_script( 'tvo_frontend_script', 'TVO_TCB', array(
		'display_shortcodes' => tvo_get_shortcodes( 'display' ),
	) );
}

/**
 * Render capture testimonial
 *
 * @param $config array
 *
 * @return string
 */
function tvo_render_shortcode( $config ) {

	if ( empty( $config ) ) {
		return '';
	}

	if ( ! empty( $config['id'] ) ) {
		$shortcode_post = get_post( $config['id'] );

		if ( ! empty( $shortcode_post ) && ( $shortcode_post->post_type === TVO_CAPTURE_POST_TYPE || $shortcode_post->post_type === TVO_DISPLAY_POST_TYPE ) ) {
			return TVO_Block::render_block( [ 'selectedBlock' => $config['id'] ] );
		}
	}

	/* render old shortcodes */
	if ( empty( $config['template'] ) ) {
		$config = tvo_get_shortcode_config( $config['id'] );
	}

	foreach ( $config as $k => $v ) {
		if ( ! is_array( $v ) ) {
			$config[ $k ] = stripslashes( $v );
		}
	}

	$facebook_app_id     = tvo_get_facebook_app_id();
	$google_client_id    = tvo_get_google_client_id();
	$default_placeholder = tvo_get_default_image_placeholder();

	if ( $config['type'] === 'display' ) {
		$testimonials = tvo_get_testimonials_from_config( $config );
	}

	$file_path = dirname( __DIR__ ) . '/templates/' . $config['type'] . '/' . $config['template'] . '.php';
	/* in case the template doesn't exist, we don't display anything */
	if ( ! is_file( $file_path ) ) {
		return '';
	}

	$output = '<div class="thrive-shortcode-html">';

	$templates = tvo_get_testimonial_templates( $config['type'] );
	if ( ! empty( $templates[ $config['template'] ]['css'] ) ) {
		foreach ( $templates[ $config['template'] ]['css'] as $css ) {
			$output .= '<link rel="stylesheet" href="' . TVO_URL . 'templates/css/' . $css . '?ver=' . TVO_VERSION . '" type="text/css"/>';
		}
	}

	if ( strpos( $config['template'], 'slider' ) !== false ) {
		if ( ! wp_script_is( 'tvo_slider', 'enqueued' ) ) {
			tvo_enqueue_script( 'tvo_slider', TVO_URL . 'tcb-bridge/js/libs/thrlider.min.js?ver=' . TVO_VERSION, array(), false, true );
		}
	}

	/* if we're doing ajax, get_the_ID will be false. also check the tcb flag */

	$is_editor = ( get_post_type() === TVO_SHORTCODE_POST_TYPE ) || ( defined( 'TVE_EDITOR_FLAG' ) && isset( $_GET[ TVE_EDITOR_FLAG ] ) ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX );

	if ( ! wp_script_is( 'tvo_frontend_form', 'enqueued' ) && $config['type'] === 'capture' && ! $is_editor ) {
		tvo_enqueue_forms_script();
	}

	$unique_id = uniqid( 'thrlider-', false );

	$config = tvo_prepare_shortcode_config( $config );

	ob_start();
	include $file_path;
	if ( $config['type'] === 'display' ) {
		include dirname( __DIR__ ) . '/templates/display/config.php';
	}
	$output .= ob_get_contents() . '</div>';
	ob_end_clean();

	return $output;
}

/**
 * Checks if the lightbox content has tvo_shortcode and includes the script
 *
 * @param $content - thrive lightbox content
 */
function tvo_load_resources( $content ) {
	if ( strpos( $content, 'tvo_shortcode' ) !== false ) {
		tvo_enqueue_forms_script();
	}
}

/**
 * Do some parsing on the $config before sending it to the templates
 *
 * @param $config
 *
 * @return mixed
 */
function tvo_prepare_shortcode_config( $config ) {

	if ( $config['type'] === 'capture' ) {
		$config['name_label']        = htmlentities( $config['name_label'], ENT_COMPAT, 'UTF-8' );
		$config['email_label']       = htmlentities( $config['email_label'], ENT_COMPAT, 'UTF-8' );
		$config['role_label']        = htmlentities( $config['role_label'], ENT_COMPAT, 'UTF-8' );
		$config['title_label']       = htmlentities( $config['title_label'], ENT_COMPAT, 'UTF-8' );
		$config['website_url_label'] = isset( $config['website_url_label'] ) ? htmlentities( $config['website_url_label'], ENT_COMPAT, 'UTF-8' ) : '';
		$config['questions']         = array_map( 'stripslashes', $config['questions'] );
		$config['placeholders']      = isset( $config['placeholders'] ) ? array_map( 'stripslashes', $config['placeholders'] ) : array_fill( 0, count( $config['questions'] ), '' );
		$config['image_display']     = isset( $config['image_display'] ) ? $config['image_display'] : 1;
		$config['on_success_option'] = isset( $config['on_success_option'] ) ? $config['on_success_option'] : 'message';
	}

	return $config;
}

/* enqueue scripts needed in front-end(until we do the lightspeed part) */
function tvo_tcb_frontend_enqueue_scripts() {
	tve_enqueue_script( 'display-testimonials-tcb', TVO_URL . 'tcb-bridge/frontend/js/display-testimonials-tcb' . \TCB_Utils::get_js_suffix(), [
		'jquery',
		'jquery-masonry',
		'tve_frontend_post-list',
	] );
}

/* Function used for loading Display Testimonials module */

/**
 * Checks if the content has Display Testimonials element
 *
 * @param $content
 *
 * @return bool
 */
function tvo_content_has_tvo_elements( $content ) {
	preg_match( '/thrive-display-testimonials/', $content, $matches );

	return ! empty( $matches );
}

/**
 * @return array
 */
function tvo_get_frontend_js() {
	$dependencies = [ 'jquery' ];

	if ( wp_script_is( 'tve_frontend' ) ) {
		$dependencies [] = 'tve_frontend';
	}

	return [
		'id'           => 'display-testimonials-tcb',
		'url'          => TVO_URL . 'tcb-bridge/frontend/js/display-testimonials-tcb.min.js',
		'dependencies' => $dependencies,
	];
}

/**
 * get the corresponding editing / displaying layout template based on the form type
 *
 * @param array $layouts
 * @param       $post_id
 * @param       $post_type
 *
 * @return mixed
 */
function tvo_get_editor_layout( $layouts, $post_id, $post_type ) {
	if ( tvo_is_editable( $post_type ) ) {
		$file_path = TVO_PATH . 'blocks/blocks-layout.php';

		if ( is_file( $file_path ) ) {
			$layouts[ $post_type ] = $file_path;
		}
	}

	return $layouts;
}

function tvo_is_editable( $post_type ) {
	return $post_type === TVO_CAPTURE_POST_TYPE || $post_type === TVO_DISPLAY_POST_TYPE;
}

function tvo_add_post_types_to_array() {
	$options[] = TVO_CAPTURE_POST_TYPE;
	$options[] = TVO_DISPLAY_POST_TYPE;

	return $options;
}

function tvo_disable_tar_settings( $allow ) {
	$post_type = get_post_type();

	if ( tvo_is_editable( $post_type ) ) {
		return false;
	}

	return $allow;
}

/**
 * called when there is no active license for TCB, but it is installed and enabled
 * the function returns true only for pieces of content that "belong" to Thrive Ovation, so only the following:
 *
 *  TVO_CAPTURE_POST_TYPE
 *  TVO_DISPLAY_POST_TYPE
 *
 * @param bool $value
 *
 * @return bool whether or not the current piece of content can be edited with TCB core functions
 */
function tvo_tcb_license_override( $value ) {
	/**
	 * if already valid, return it
	 */
	if ( $value ) {
		return true;
	}

	return tvo_is_editable( get_post_type() );
}

/**
 * Check if the post can be edited by checking access and post type
 *
 * @param bool $has_access
 *
 * @return bool
 */
function tvo_user_can_use_plugin( $has_access ) {
	if ( tvo_is_editable( get_post_type() ) ) {
		$has_access = TVO_Product::has_access();
	}

	return $has_access;
}
