import { __ } from '@wordpress/i18n';
import { PanelBody, PanelRow, Button, SelectControl, Icon, TextControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import thriveLogo from './thrive-logo';

const createElement = wp.element.createElement,
	tveOuterHeight = el => {
		if ( ! el ) {
			return 0;
		}
		let height = el.offsetHeight;
		const style = getComputedStyle( el );

		height += parseInt( style.marginTop ) + parseInt( style.marginBottom );
		return height;
	},
	generateRandomString = ( radix = 16 ) => {
		return (
			new Date().getTime() + Math.floor( Math.random() * 100000 )
		).toString( radix );
	},
	getNonCachedLink = link => {
		return `${link}${
			link.split( '?' )[ 1 ] ? '&' : '?'
		}tvo=${generateRandomString()}`;
	};

wp.domReady( function() {
	wp.blocks.updateCategory( 'thrive', { icon: thriveLogo } );
} );

/**
 * Once an block is update try to update the preview in gutenberg too
 */
window.addEventListener(
	'storage',
	storageEvent => {
		if (
			storageEvent.key &&
			storageEvent.key.includes( 'tvo_block' )
		) {
			const id = storageEvent.key.split( '-' )[ 1 ],
				iframes = document.getElementsByClassName(
					`tvo-block-${id}`,
				);

			/**
			 * in case of duplicate / copy-paste
			 */
			Array.prototype.forEach.call( iframes, iframe => {
				iframe.setAttribute(
					'src',
					getNonCachedLink( iframe.getAttribute(
						'src',
					) ),
				);
			} );

			localStorage.removeItem( storageEvent.key );
		}
	},
	false,
);

/**
 * Render the sidebar & iframe once you added a block
 *
 * @param  props
 * @param  label
 * @return {(*)[]}
 */
export function renderSidebar( props, label ) {
	let previewLink = props.attributes.previewLink || '',
		editLink = props.attributes.editLink || '';
	if ( ! previewLink ) {
		const posts = props.posts ? props.posts : props[ props.attributes.type ];

		const selectedPost = posts ? posts.find(
			post =>
				Number( post.id ) === Number( props.attributes.selectedBlock ),
		) : 0;
		if ( selectedPost ) {
			previewLink = selectedPost.link;
			editLink = selectedPost.edit_url;
		}
	}

	if ( previewLink ) {
		previewLink += `${previewLink.split( '?' )[ 1 ] ? '&' : '?'}tve_block_preview=1`;
	}

	return [ createElement(
		InspectorControls,
		null,
		createElement(
			PanelBody,
			{
				title: __( 'Block settings', 'thrive-ovation' ),
				initialOpen: true,
			},
			createElement(
				PanelRow,
				{},
				createElement(
					PanelRow,
					{},
					createElement(
						'a',
						{
							class:
								'thrive-ovation-edit-link tcb-enable-editor',
							'data-id': props.attributes.selectedBlock,
							href: editLink,
							target: '_blank',
						},
						createElement(
							'div',
							{
								class: 'thrive-ovation-admin-icon-holder',
							},
							createElement( 'div', {
								class: 'thrive-ovation-admin-icon',
							} ),
						),
						createElement(
							'div',
							{
								class:
									'thrive-ovation-admin-text tve-block-edit',
							},
							__( 'Edit with Thrive Ovation', 'thrive-cb' ),
						),
					),
				),
			),
		),
	),
	createElement( 'iframe', {
		src: `${previewLink}`,
		id: `tvo-block-${props.attributes.selectedBlock}`,
		class: `${props.attributes.className} tvo-block-preview tvo-block-${props.attributes.selectedBlock}`,
		scrolling: 'no',
		onLoad() {
			const iframes = document.getElementsByClassName(
				`tvo-block-${props.attributes.selectedBlock}`,
			);

			/**
			 * in case of duplicate / copy-paste
			 */
			Array.prototype.forEach.call( iframes, iframe => {
				const iframeDocument = iframe.contentDocument,
					setHeight = () => {
						const height = tveOuterHeight( iframeDocument.body );

						iframe.style.setProperty(
							'height',
							`${height}px`,
						);
						iframe.parentNode.style.setProperty(
							'height',
							`${height}px`,
						);
					};

				iframe.style.setProperty(
					'pointer-events',
					'none',
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
	} ),
	];
}

/**
 * Render the add block view
 *
 * @param  props
 * @param  title
 * @param  description
 * @param  logo
 * @param  postType
 * @return {any}
 */
export function renderBlock( props, title, description, logo, postType ) {
	return createElement(
		'div',
		{
			class: 'tvo-new-block-container',
		},
		logo(),
		createElement(
			'div',
			{
				class: 'tvo-block-title',
			},
			createElement( 'h2', {}, __( title, 'thrive-ovation' ) ),
		),
		createElement(
			'div',
			{
				class: 'tvo-new-block-description',
			},
			__( description, 'thrive-ovation' ),
		),

		createElement(
			Button,
			{
				className: 'tvo-create-block-button',
				type: 'button',
				onClick() {
					apiFetch( {
						path: `/wp/v2/${postType}`,
						method: 'POST',
						data: {
							status: 'publish',
						},
					} )
						.then( data => {
							props.setAttributes( {
								selectedBlock: parseInt(
									data.id,
								),
								previewLink: data.link,
								editLink: data.edit_url,
							} );
							window.open(
								data.edit_url,
								'_blank',
							);
						} )
						.catch( function( data ) {
						} );
				},
			},
			__( 'Setup', 'thrive-ovation' ),
		),
	);
}

export function renderFrame( props, title, description, logo ) {
	return createElement(
		'div',
		{
			class: 'tvo-new-block-container',
		},
		logo(),
		createElement(
			'div',
			{
				class: 'tvo-block-title',
			},
			createElement( 'h2', {}, __( title, 'thrive-ovation' ) ),
		),
		createElement(
			'div',
			{
				class: 'tvo-new-block-description',
			},
			__( description, 'thrive-ovation' ),
		),

		renderButtons( props ),
		renderTabs( props ),
		renderGoTOButton(),
	);
}

export function renderTabs( props ) {
	const classes = props.attributes.type ? `tvo-show-${props.attributes.type}` : `tvo-hide tvo-show-capture`;
	return [
		createElement(
			'div',
			{
				class: `tvo-block-content ${classes}`,
			},
			createElement(
				'div',
				{
					class: 'tvo-block-tabs',
				},
				createElement(
					'a',
					{
						class: 'tvo-block-tab tvo-block-tab-capture',
						href: 'javascript:void(0)',
						onClick: event => {
							const el = event.target.closest(
								'.tvo-block-content',
							);

							el.classList.add( 'tvo-show-capture' );
							el.classList.remove( 'tvo-show-display' );

							event.stopPropagation();
							event.preventDefault();
						},
					},
					__( 'Capture testimonials', 'thrive-ovation' ),
				),
				createElement(
					'a',
					{
						class: 'tvo-block-tab tvo-block-tab-display',
						href: 'javascript:void(0)',
						onClick: event => {
							const el = event.target.closest(
								'.tvo-block-content',
							);

							el.classList.add( 'tvo-show-display' );
							el.classList.remove( 'tvo-show-capture' );

							event.stopPropagation();
							event.preventDefault();
						},
					},
					__( 'Display testimonials', 'thrive-ovation' ),
				),
			),
			createElement(
				'div',
				{
					class: 'tvo-block-tabs-content',
				},
				renderCapture( props ),
				renderDisplay( props ),
			),
		) ];
}

export function renderButtons( props ) {
	const classes = props.attributes.type ? `tvo-hide` : '';
	return createElement(
		'div',
		{
			class: `tvo-block-buttons-container ${classes}`,
		},
		createElement(
			'div',
			{
				class: 'tvo-block-card',
			},
			createElement(
				'h3',
				{
					class: 'tvo-block-h3',
				},
				__( 'Capture testimonials', 'thrive-ovation' ),
			),
			createElement(
				'p',
				{
					class: 'tvo-block-p',
				},
				__( 'Get new testimonials with a beautifully optimized Ovation Capture Form', 'thrive-ovation' ),
			),
			createElement(
				Button,
				{
					className: 'tvo-split-block-button',
					type: 'button',
					onClick() {
						const container = document.querySelector( '.tvo-block-content' );
						container.classList.remove( 'tvo-show-display' );
						container.classList.remove( 'tvo-hide' );
						container.classList.add( 'tvo-show-capture' );
						document.querySelector( '.tvo-block-buttons-container' ).classList.add( 'tvo-hide' );
						props.attributes.type = 'capture';
					},
				},
				__( 'Select', 'thrive-ovation' ),
			),
		),
		createElement(
			'div',
			{
				class: 'tvo-block-card',
			},
			createElement(
				'h3',
				{
					class: 'tvo-block-h3',
				},
				__( 'Display Testimonials', 'thrive-ovation' ),
			),
			createElement(
				'p',
				{
					class: 'tvo-block-p',
				},
				__( 'Show a selection of your existing testimonials with beautiful Ovation Designs', 'thrive-ovation' ),
			),
			createElement(
				Button,
				{
					className: 'tvo-split-block-button',
					type: 'button',
					onClick() {
						const container = document.querySelector( '.tvo-block-content' );
						container.classList.remove( 'tvo-show-capture' );
						container.classList.remove( 'tvo-hide' );
						container.classList.add( 'tvo-show-display' );
						document.querySelector( '.tvo-block-buttons-container' ).classList.add( 'tvo-hide' );
						props.attributes.type = 'display';
					},
				},
				__( 'Select', 'thrive-ovation' ),
			),
		),
	);
}

export function renderCapture( props ) {
	let defaultLabel = '-- Select Capture form --';

	if ( props.attributes.searchBlockSel ) {
		defaultLabel = `-- ${props.capture.length} forms found --`;
	}

	const options = [
		{
			value: 0,
			label: __( defaultLabel, 'thrive-ovation' ),
		},
	];

	props.capture.forEach( post => {
		options.push( { value: post.id, label: post.title.raw } );
	} );

	return renderIndividualTabContent( props, 'capture', options );
}

export function renderDisplay( props ) {
	if ( ! props.display ) {
		return false;
	}

	let defaultLabel = '-- Select a display testimonial --';

	if ( props.attributes.searchBlockSel ) {
		defaultLabel = `-- ${props.display.length} items found --`;
	}

	const options = [
		{
			value: 0,
			label: __( defaultLabel, 'thrive-ovation' ),
		},
	];

	props.display.forEach( post => {
		options.push( { value: post.id, label: post.title.raw } );
	} );

	return renderIndividualTabContent( props, 'display', options );
}

let isBlurOverSearch = false;
export function renderIndividualTabContent( props, type, options ) {
	return createElement(
		'div',
		{
			class: `tvo-block-tabs-content-${type}`,
		},

		createElement(
			'div',
			{
				class: 'tvo-block-search-container',
			},

			createElement( TextControl, {
				placeholder: __(
					'Filter block list',
					'thrive-ovation',
				),
				isPressEnterToChange: true,
				onChange( content ) {
					const data = {
						searchText: content,
					};

					props.setAttributes( data );
				},
				onFocus( event ) {
					isBlurOverSearch = false;
					const container = event.target.closest(
						'.tvo-block-search-container',
					);
					container
						.getElementsByClassName(
							'tvo-block-clear-button',
						)[ 0 ]
						.classList.add( 'hidden' );
					container
						.getElementsByClassName(
							'tvo-block-search-button',
						)[ 0 ]
						.classList.remove( 'hidden', 'opacity' );
				},

				onBlur( event ) {
					isBlurOverSearch =
						event.relatedTarget &&
						event.relatedTarget.classList.contains(
							'tvo-block-search-button',
						);

					if ( ! isBlurOverSearch ) {
						const container = event.target.closest(
								'.tvo-block-search-container',
							),
							clearEl = container.getElementsByClassName(
								'tvo-block-clear-button',
							)[ 0 ],
							searchEl = container.getElementsByClassName(
								'tvo-block-search-button',
							)[ 0 ];

						clearEl.classList.toggle(
							'hidden',
							! props.attributes.searchText.length,
						);
						searchEl.classList.toggle(
							'hidden',
							props.attributes.searchText.length,
						);
						searchEl.classList.add(
							'opacity',
						);
					}
				},

				onKeyDown( event ) {
					/**
					 * On enter press trigger the search
					 */
					if ( event.which === 13 ) {
						props.setAttributes( {
							searchText: event.target.value,
							searchBlockSel: event.target.value,
						} );
					}
				},
				className: 'tvo-block-search-input',
				value: props.attributes.searchText,
			} ),

			createElement(
				'a',
				{
					href: 'javascript:void(0)',
					class: `tvo-block-search-button  ${
						props.attributes.searchText
							? 'hidden'
							: 'opacity'
					}`,
					onClick() {
						props.setAttributes( {
							searchBlockSel:
							props.attributes.searchText,
						} );
					},
				},
				createElement( Icon, { icon: 'search' } ),
			),
			createElement(
				'a',
				{
					href: 'javascript:void(0)',
					className: `tvo-block-clear-button ${
						props.attributes.searchText
							? ''
							: 'tvo-hide'
					} `,
					onClick() {
						props.setAttributes( {
							searchText: '',
							searchBlockSel: '',
						} );
					},
				},
				createElement( Icon, { icon: 'no-alt' } ),
			),
		),
		renderSelect( options, props, '' ),
	);
}

export function renderGoTOButton() {
	return createElement(
		'div',
		{
			class: 'tvo-go-dash',
		},
		__( 'Go to the ', 'thrive-ovation' ),
		createElement(
			'a',
			{
				href: TVO_Data.dashboard_url,
				target: '_blank',
			},
			__( 'Thrive Ovation Dashboard', 'thrive-ovation' ),
		),
	);
}

export function renderSelect( opts, props, label ) {
	return createElement(
		'div',
		{ class: 'tvo-block-select-wrapper' },

		createElement( SelectControl, {
			label: __( label, 'thrive-ovation' ),
			options: opts,
			onChange( value ) {
				props.setAttributes( {
					selectedBlock: parseInt( value ),
					type: props.attributes.type,
				} );
			},
			value: props.attributes.selectedBlock,
		} ),
	);
}
