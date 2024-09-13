var base = require( './base' ),
	base_model = require( './../models/base' ),
	range = require( '../controls/range' ),
	delete_modal = require( '../modals/archive' ),
	title_editor = require( './../controls/edit_title' );

module.exports = base.extend( {

	className: 'tvd-col tvd-l3 tvd-m6',

	template: TVE_Dash.tpl( 'html-variation-card' ),
	initialize: function ( args ) {
		base.prototype.initialize.apply( this, args );

		this.archived_variations = args.archived_variations;
		this.published_variations = args.published_variations;
	},
	archive: function () {

		TVE_Dash.modal( delete_modal, {
			submit: _.bind( function () {
				this.remove();
				this.model.set( 'action', 'archive' );
				this.model.save();

				this.published_variations.distribute_traffic( this.model.set( 'traffic', 0 ) );
				this.published_variations.save_distributed_traffic();
				this.archived_variations.add( this.model );
				this.published_variations.remove( this.model );
			}, this ),
			model: this.model,
			btn_yes_txt: ThriveAB.t.archive_title,
			btn_no_txt: ThriveAB.t.cancel,
			title: ThriveAB.t.archive_variation,
			description: TVE_Dash.sprintf( ThriveAB.t.about_to_archive, this.model.get( 'post_title' ) )
		} );

		return false;
	},

	delete: function () {

		TVE_Dash.modal( delete_modal, {
			submit: _.bind( function () {
				this.remove();
				this.model.destroy();

				this.published_variations.equalize_traffic();
				this.published_variations.save_distributed_traffic();

			}, this ),
			model: this.model,
			btn_yes_txt: ThriveAB.t.delete_title,
			btn_no_txt: ThriveAB.t.cancel,
			title: ThriveAB.t.delete_variation,
			description: TVE_Dash.sprintf( ThriveAB.t.about_to_delete, this.model.get( 'post_title' ) )
		} );

		return false;
	},

	duplicate: function () {
		var clone = this.model.clone(),
			_new_traffic = parseInt( 100 / ( this.published_variations.length + 1 ) );

		clone.set( {
			ID: '',
			post_title: 'Copy of ' + this.model.get( 'post_title' ),
			source_id: this.model.get( 'ID' ),
			traffic: _new_traffic,
			is_control: false,
			post_parent: ThriveAB.page.ID
		} );
		TVE_Dash.showLoader();
		clone.save( null, {
			success: _.bind( function ( model ) {
				this.published_variations.add( model );
				this.published_variations.equalize_traffic();
				this.published_variations.save_distributed_traffic();
				TVE_Dash.success( ThriveAB.t.variation_added, 1000 );
			}, this ),
			error: function () {
				TVE_Dash.err( ThriveAB.t.add_variation_error );
			},
			complete: function () {
				TVE_Dash.hideLoader();
			}
		} );
	},

	edit_title: function ( e ) {
		var self = this,
			$original_title = this.$el.find( '.thrive-ab-title-content' ),
			editModel = new base_model( {post_title: this.model.get( 'post_title' )} );

		editModel.on( 'thrive-ab-title-no-change', function () {
			self.$( '.thrive-ab-title-editor' ).html( '' );
			$original_title.show();
		} );

		editModel.on( 'change:post_title', function () {
			self.model.save( {post_title: editModel.get( 'post_title' )}, {patch: true} );
			self.$( '.thrive-ab-title-editor' ).html( '' );
			$original_title.find( '.thrive-ab-card-title' ).html( editModel.get( 'post_title' ) );
			$original_title.show();
		} );

		var titleEditor = new title_editor( {
			el: this.$( '.thrive-ab-title-editor' ),
			model: editModel
		} );
		$original_title.hide().after( titleEditor.render().$el );
		titleEditor.focus();

		return false;
	},

	render: function () {

		this.$el.html( this.template( {item: this.model} ) );

		new range( {
			el: this.$( '.thrive-ab-card-footer' ),
			model: this.model
		} );
	}

} );
