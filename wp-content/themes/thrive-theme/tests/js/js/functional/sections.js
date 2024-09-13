const Controls = require( '../util/controls' ),
	Data = require( '../util/data' ),
	Element = require( '../util/element' ),
	sectionSelector = section => `#theme-${section}-section`;

QUnit.module( 'Sections', {
	before: () => {
		TVE.Elements[ 'main-container' ].components.animation.hidden = true;
	},
	afterEach: () => {
		if ( TVE.ActiveElement ) {
			TVE.ActiveElement.head_css( Data.reset );
		}

		TVE.Components.background.get_collection().reset( [] );
		TVE.Editor_Page.blur();
	}
} );


//Sidebar Section - Position && Columns Section - Gutter
QUnit.testWithSelectedElement( 'Position for Sidebar', sectionSelector( 'sidebar' ), ( assert, $sidebar ) => {
	const positionControl = Controls.get( 'theme_section', 'Position' ),
		gutterControl = Controls.get( 'main-container', 'Gutter' );

	TVE.Components.theme_section.section = TVE.Theme.utils.getSectionInstance( $sidebar );

	positionControl.setValue( 'left', true );

	assert.equal( TVE.inner.window.ThriveTheme.utils.getSidebarPosition(), 'left', 'Move sidebar to left' );

	Controls.get( 'sidebar-settings', 'SidebarDisplay' ).setValue( 'normal', true );

	const $content = Element.getBySelector( sectionSelector( 'content' ) ),
		sidebarRight = Element.offset( $sidebar, 'right' ),
		contentLeft = Element.offset( $content, 'left' );

	gutterControl.update( TVE.Theme.$main );

	assert.equal( sidebarRight < contentLeft, true, 'Sidebar is on left' );
	assert.equal( contentLeft - sidebarRight, gutterControl.getValue(), 'Correct gutter.' );

	positionControl.setValue( 'right', true );
} );
[ 'top', 'bottom' ].forEach( section => {
	const label = TVE.ucFirst( section ),
		sectionSel = sectionSelector( section );

	// Section - Visibility
	QUnit.testWithSelectedElement( `Visibility for ${label} Section`, sectionSel, ( assert, $section ) => {
		const control = Controls.get( 'theme_section', 'Visibility' );

		TVE.Components.theme_section.section = TVE.Theme.utils.getSectionInstance( $section );

		control.setChecked( false, true );
		assert.equal( Element.isVisible( $section ), false, `Visibility hide functionality on ${label} Section` );

		control.setChecked( true, true );
		assert.equal( Element.isVisible( $section ), true, `Visibility show functionality on ${label} Section` );
	} );

	// Section - Full Width
	QUnit.testWithSelectedElement( `Contained(Full Width) for ${label} Section`, sectionSel, ( assert, $section ) => {
		const positionControl = Controls.get( 'theme_section', 'StretchBackground' );

		TVE.Components.theme_section.section = TVE.Theme.utils.getSectionInstance( $section );

		positionControl.setChecked( true, true )
		assert.equal( $section.parent().hasClass( 'main-container' ), false, `${label} section is Full width` );

		positionControl.setChecked( false, true )
	} );

	// Section - Height
	QUnit.testWithSelectedElement( `Section Height for ${label} Section`, sectionSel, ( assert, $section ) => {
		const heightControl = Controls.get( 'theme_section', 'SectionHeight' ),
			heightValue = 500;

		TVE.Components.theme_section.section = TVE.Theme.utils.getSectionInstance( $section );

		heightControl.setValue( heightValue, true );

		assert.equal( parseInt( $section.css( 'height' ) ), heightValue, `${label} section height is ${heightValue}px` );
	} );

	// Section - Width
	QUnit.testWithSelectedElement( `Width for ${label} Section`, sectionSel, ( assert, $section ) => {
		const widthControl = Controls.get( 'theme_section', 'MinWidth' );

		TVE.Components.theme_section.section = TVE.Theme.utils.getSectionInstance( $section );

		widthControl.setValue( 500, true );

		assert.equal( parseInt( $section.css( 'min-width' ) ), 500, `${label} section Content max-width is 500px` );

		widthControl.setValue( '100%', true );

		assert.equal( parseInt( $section.css( 'min-width' ) ), 100, `${label} section Content has full width` );
	} );

	// Section - Vertical Position
	QUnit.testWithSelectedElement( `VerticalPosition for ${label} Section`, sectionSel, ( assert, $section ) => {
		const $content = $section.find( '.section-content' ),
			positionControl = Controls.get( 'theme_section', 'VerticalPosition' );

		TVE.Components.theme_section.section = TVE.Theme.utils.getSectionInstance( $section );

		Controls.setActive( positionControl, 'center' );

		assert.equal( $content.css( 'justify-content' ), 'center', `${label} section Vertical position center` );

		Controls.setActive( positionControl, 'flex-end' );

		assert.equal( $content.css( 'justify-content' ), 'flex-end', `${label} section Vertical position bottom` );

		positionControl.$( '.default' ).trigger( 'click' );

		assert.equal( $content.css( 'justify-content' ), 'normal', `${label} section Vertical position top` );

		//restore the normal height
		Controls.get( 'theme_section', 'SectionHeight' ).setValue( 20, true );
	} );
} )

//Columns - Gutter(Space Between )
QUnit.testWithSelectedElement( 'Gutter for Columns', '.main-container', ( assert, $columns ) => {
	const gutterControl = Controls.get( 'main-container', 'Gutter' ),
		$colSpace = Element.getBySelector( '.main-columns-separator' );

	gutterControl.setValue( 100, true );

	assert.equal( parseInt( $colSpace.css( 'width' ) ), gutterControl.getValue(), 'Correct gutter. Gutter was changed' );
} );

//Template Container - Content Maximum Width
QUnit.testWithSelectedElement( 'Content Width for Template Section', '#content', ( assert, $templateContainer ) => {
	const widthControl = Controls.get( 'template-content', 'LayoutWidth' ),
		widthValue = 1234;

	widthControl.setValue( widthValue, true );
	assert.equal( parseInt( TVE.Theme.$main.css( 'width' ) ), widthValue, `The template width was changed to ${widthValue}px` );
} );

//Template Container - Boxed Layout
QUnit.testWithSelectedElement( 'Boxed Layout(ContentFullWidth) for Template Section', '#content', ( assert, $templateContainer ) => {
	TVE.Components[ 'template-content' ].$( '.full-width-control [data-fn="toggleFullWidth"][data-boxed="0"]' ).trigger( 'click' );

	assert.equal( TVE.Theme.$main.css( 'max-width' ).trim(), '100%', 'Template container was changed to Full Width' );
} );

Data.elements.forEach( element => {
	QUnit.testWithSelectedElement( `Background for ${element.name}`, element.selector, ( assert, $section ) => {
		const backgroundControl = Controls.get( 'background.ColorPicker' );

		Controls.background.color( Data.colors.cyan.hex );
		assert.equal( Controls.head_css( backgroundControl, 'background-color' ), Data.colors.cyan.rgb, `${element.name} Background is Cyan` );
	} );

	QUnit.testWithSelectedElement( `Solid Background for ${element.name}`, element.selector, ( assert, $section ) => {
		const solidBackgroundControl = Controls.get( 'background.ColorPicker' );

		Controls.background.add_solid_layer( Data.colors.cyan.hex );
		assert.equal( Controls.head_css( solidBackgroundControl, 'background-image' ), 'linear-gradient(' + Data.colors.cyan.rgb + ', ' + Data.colors.cyan.rgb + ')', `${element.name} Solid Background is Cyan` );
	} );

	QUnit.testWithSelectedElement( `Corners for ${element.name}`, element.selector, ( assert, $section ) => {
		const cornersControl = Controls.get( 'borders.Corners' );

		cornersControl.current_corner = '';

		Controls.corner.size( 40 );
		assert.equal( parseInt( Controls.head_css( cornersControl, 'border-radius' ) ), 40, `Border radius is 40px for ${element.name}` );
	} );

	QUnit.testWithSelectedElement( `Borders for ${element.name}`, element.selector, ( assert, $section ) => {
		const bordersControl = Controls.get( 'borders.Borders' );

		bordersControl.border_side = '';

		Controls.border.side( '' );
		Controls.border.color( Data.colors.red.hex );
		Controls.border.width( 5 );

		assert.equal( Controls.head_css( bordersControl, 'border-color' ), Data.colors.red.rgb, 'Border color was changed to Red' );
		assert.equal( parseInt( Controls.head_css( bordersControl, 'border-width' ) ), 5, 'Border width was changed to 5px' );
	} );
} );

//Sidebar Section - Visibility
QUnit.testWithSelectedElement( 'Visibility for Sidebar', sectionSelector( 'sidebar' ), ( assert, $sidebar ) => {
	const control = Controls.get( 'theme_section', 'Visibility' );

	control.setChecked( false, true );
	assert.equal( Element.isVisible( $sidebar ), false, 'Visibility hide functionality' );

	control.setChecked( true, true );
	assert.equal( Element.isVisible( $sidebar ), true, 'Visibility show functionality' );
}, 'theme_section' );
