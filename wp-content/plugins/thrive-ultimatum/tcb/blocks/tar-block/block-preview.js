import {tveOuterHeight, getPreviewLink} from './utils';
import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';

const createElement = wp.element.createElement;
const previewBlock = ( props ) => {
	let previewLink = props.attributes.previewLink;
	const selectedPost = props.posts ? props.posts.find(
		( post ) =>
			Number( post.id ) === Number( props.attributes.selectedBlock )
	) : 0;
	if ( selectedPost ) {
		props.setAttributes( {
			previewLink: selectedPost.link,
			editLink: selectedPost.edit_url,
		} );
	}

	const editLink = props.attributes.editLink;
	if ( previewLink ) {
		previewLink = getPreviewLink( previewLink );
	}

	return [
		createElement(
			InspectorControls,
			null,

			createElement(
				PanelBody,
				{
					title: __( 'Block settings', 'thrive-cb' ),
					initialOpen: true,
				},
				createElement(
					PanelRow,
					{},

					createElement(
						'a',
						{
							class:
								'thrive-architect-edit-link tcb-enable-editor',
							'data-id': props.attributes.selectedBlock,
							href: editLink,
							target: '_blank',
						},
						createElement(
							'div',
							{
								class: 'thrive-architect-admin-icon-holder',
							},
							createElement( 'div', {
								class: 'thrive-architect-admin-icon',
							} )
						),
						createElement(
							'div',
							{
								class:
									'thrive-architect-admin-text tve-block-edit',
							},
							__( 'Edit block with Architect', 'thrive-cb' )
						)
					)
				)
			)
		),
		createElement( 'iframe', {
				src: previewLink,
				id: `architect-block-${props.attributes.selectedBlock}`,
				class: `${props.attributes.className} architect-block-preview architect-block-${props.attributes.selectedBlock}`,
				scrolling: 'no',
				onLoad() {
					const iframes = document.getElementsByClassName(
						`architect-block-${props.attributes.selectedBlock}`
					);

					/**
					 * in case of duplicate / copy-paste
					 */
					Array.prototype.forEach.call( iframes, ( iframe ) => {
						const iframeDocument = iframe.contentDocument,
							setHeight = () => {
								const height = tveOuterHeight( iframeDocument.body );

								iframe.style.setProperty(
									'height',
									`${height}px`
								);
								iframe.parentNode.style.setProperty(
									'height',
									`${height}px`
								);
							};

						iframe.style.setProperty(
							'pointer-events',
							'none'
						);

						setHeight();
						/**
						 * in case there is content loaded via ajax calls
						 */
						setTimeout( () => {
							setHeight();
						}, 3000 );

						if ( iframe.contentWindow.TVE_Dash ) {
							iframe.contentWindow.TVE_Dash.forceImageLoad( iframeDocument );
						}
					} );
				},
			}
		),
	];
};

export default previewBlock;
