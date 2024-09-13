( $ => {
	const Notifications = {
		init() {
		},
		editorInit() {
			TVE.Editor_Page.selection_manager.init( TVE.inner_$( '.notifications-editor-wrapper' ) );
			TVE.main.sidebar_extra.$( '.sidebar-item:not(.click):not(.add-element)' ).remove();
			TVE.main.sidebar_extra.$( '.sidebar-item.add-element' ).hide();
		},
	};

	$( window ).on( 'tcb_after_dom_ready', () => {
		/* Initialize the typography settings */
		TVE.main.on( 'tcb-ready', Notifications.init.bind( Notifications ) );
		Notifications.editorInit();
	} );

} )( jQuery );
