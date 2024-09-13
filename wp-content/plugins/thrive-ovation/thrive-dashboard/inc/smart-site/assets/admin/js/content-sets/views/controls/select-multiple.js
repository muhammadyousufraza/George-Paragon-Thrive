module.exports = require( './base' ).extend( {
	template: 'tvd-c-s-select-multiple',
	afterRender() {
		this.$select = this.$( 'select' );

		if ( this.$select.data( 'select2' ) ) {
			this.$select.select2( 'destroy' );
		}

		const model = this.ruleModel;

		this.$select.select2( {
			placeholder: "Search for content",
			minimumInputLength: 2,
			delay: 250,
			multiple: true,
			tags: false, //TAGS: true or false. If set to false, new values can not be created
			ajax: {
				url: TD_SETS.routes.base,
				type: 'GET',
				dataType: 'json',
				data: function ( obj ) {
					return {...model.toJSON(), ...{_wpnonce: TD_SETS.nonce, query_string: obj.term}};
				},
				delay: 250,
				cache: true,
				processResults: function ( data ) {
					return {
						results: data
					};
				}
			}
		} );

		if ( this.hasStoredValue() ) {
			this.setSelected( this.getStoredValue() )
		}

		this.$select.data( 'select2' ).$container.addClass( 'tvd-content-set-select-multiple' );

		this.$select.on( 'select2:select', ( e ) => {
			this.change();
		} ).on( 'select2:unselect', ( e ) => {
			if ( ! e.params.originalEvent ) {
				return;
			}
			e.params.originalEvent.stopPropagation();

			this.change()

		} ).data( 'select2' ).$dropdown.addClass( 'tvd-content-set-delete-select-multiple-dropdown' );
	},
	/**
	 * Sets value to a select that has data from remote location (dynamically from server)
	 */
	setSelected: function ( values ) {
		const _ids = [];

		this.$select.empty();

		//Remove empty values
		values = values.filter( ( a ) => a );

		values.forEach( _val => {
			_ids.push( _val.id );
			this.$select.append( '<option value="' + _val.id + '">' + _val.text + '</option>' );
		} );

		this.$select.val( _ids ).trigger( 'change' );
	},
	change( event, dom ) {
		const value = [];

		this.$select.select2( 'data' ).forEach( data => {
			value.push( {id: data.id, text: data.text} );
		} );

		this.ruleModel.trigger( 'control-changed', this.step, value );
	}
} );