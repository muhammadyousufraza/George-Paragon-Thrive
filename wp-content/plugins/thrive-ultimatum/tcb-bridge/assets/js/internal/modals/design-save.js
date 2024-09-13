/**
 * Created by Ovidiu on 7/21/2017.
 */
var _instance = null,
	_utils = require( '../_utils' );

module.exports = TVE.modal.base.extend( {
	after_initialize: function () {
		this.$el.addClass( 'medium' );
	},
	checkUniqueName: function ( name ) {
		var unique = true;
		tve_ult_page_data.saved_tpls.forEach( function ( tpl ) {
			if ( tpl.name === name ) {
				unique = false;
			}
		} )
		return unique;
	},
	save: function () {
		var _name = this.$( 'input#tve-template-name' ).val(),
			self = this;


		if ( _name && _name.length > 0 ) {
			_name = _name.trim();
			if ( this.checkUniqueName( _name ) ) {
				TVE.main.editor_settings.save( null, null, function () {
					_utils.tpl_ajax( {
						custom: 'save',
						name: _name
					} ).done( function ( response ) {
						if ( response && response.saved_tpls ) {
							tve_ult_page_data.saved_tpls = response.saved_tpls;
						}

						TVE_Ult_Int.savePreview( _name, data => {
								const tplIndex = tve_ult_page_data.saved_tpls.findIndex( tpl => tpl.name === _name );
								/**
								 * set the thumb
								 */
								if ( tplIndex !== - 1 ) {
									tve_ult_page_data.saved_tpls[ tplIndex ].thumb = data.thumb;
									tve_ult_page_data.saved_tpls[ tplIndex ].thumb_size = data.thumb_size;
								}
							}
						);
						self.close();
						TVE.main.overlay( 'close' );
					} );
				} );
			} else {
				TVE.page_message( tve_ult_page_data.L.tpl_existing_name, true, 5000 );
			}
		} else {
			TVE.page_message( tve_ult_page_data.L.tpl_name_required, true, 5000 );
		}
	}
}, {
	/**
	 * "Singleton" implementation for modal instance
	 *
	 * @param el
	 */
	get_instance: function ( el ) {
		if ( ! _instance ) {
			_instance = new TVE_Ult_Int.DesignSave( {
				el: el
			} );
		}

		return _instance;
	}
} );
