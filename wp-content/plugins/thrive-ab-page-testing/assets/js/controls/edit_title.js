var base_view = require( './../views/base' );

module.exports = base_view.extend( {
	className: 'tvd-input-field',
	template: TVE_Dash.tpl( 'util/edit-title' ),
	events: {
		'keyup input': 'keyup',
		'change input': function () {
			var trim_value = this.input.val().trim();
			if ( ! trim_value ) {
				this.input.addClass( 'tvd-invalid' );
				return false;
			}
			this.model.set( 'post_title', trim_value );
			return false;
		},
		'blur input': function () {
			this.model.trigger( 'thrive-ab-title-no-change' );
		}
	},
	initialize: function ( args ) {

		var is_valid_args = true;

		if ( ! args ) {
			is_valid_args = false;
		}

		if ( typeof args !== 'object' ) {
			is_valid_args = false;
		}

		if ( typeof args === 'object' && typeof args.el === 'undefined' ) {
			is_valid_args = false;
		}

		if ( ! is_valid_args ) {
			console.log( 'Error: invalid arguments for edit title control' );
			return;
		}

		this.render();
	},

	keyup: function ( event ) {
		if ( event.which === 27 ) {
			this.model.trigger( 'thrive-ab-title-no-change' );
		}
	},
	render: function () {
		this.$el.html( this.template( {item: this.model} ) );
		this.input = this.$( 'input' );

		return this;
	},
	focus: function () {
		this.input.focus().select();
	}
} );
