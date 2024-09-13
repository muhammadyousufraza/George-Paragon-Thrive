/* global TCB_Post_Edit_Data */
import tarLogo from './logo';
import {getTerm, getPreviewLink, maxNrOfPosts} from './utils';
import {__} from '@wordpress/i18n';
import {
	Button,
	Icon,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

const createElement = wp.element.createElement;

/**
 * Whether or not the blur is triggered by clicking the search icon
 * @type {boolean}
 */
let isBlurOverSearch = false;

const renderAddBlock = ( props ) => {
	if ( ! props.posts ) {
		return __( 'Loading …', 'thrive-cb' );
	}
	let defaultLabel = 'No matching results';

	if ( props.posts && props.posts.length ) {
		if ( props.attributes.searchBlockSel ) {
			defaultLabel = `-- ${props.posts.length} blocks found --`;
		} else {
			defaultLabel = `-- Select a block --`;
		}
	}

	const options = [ {value: 0, label: __( defaultLabel, 'thrive-cb' )} ];

	props.posts.forEach( ( post ) => {
		options.push( {value: post.id, label: post.title.raw} );
	} );

	return [
		createElement(
			'div',
			{
				class: 'tar-new-block-container',
			},

			tarLogo(),

			createElement(
				'div',
				{
					class: 'tar-block-title',
				},
				createElement( 'h2', {}, 'Thrive Block' )
			),
			createElement(
				'div',
				{
					class: 'tar-new-block-description',
				},
				__(
					'Select a template from your Thrive Symbols & Blocks Library or create a new one',
					'thrive-cb'
				)
			),

			createElement(
				'div',
				{
					class: 'tar-block-content tve-show-saved',
				},
				createElement(
					'div',
					{
						class: 'tar-block-tabs',
					},
					createElement(
						'a',
						{
							class: 'tar-block-tab tar-block-tab-saved',
							href: 'javascript:void(0)',
							onClick: ( event ) => {
								const el = event.target.closest(
									'.tar-block-content'
								);

								el.classList.add( 'tve-show-saved' );
								el.classList.remove( 'tve-show-create' );

								event.stopPropagation();
								event.preventDefault();
							},
						},
						__( 'Saved blocks', 'thrive-cb' )
					),
					createElement(
						'a',
						{
							class: 'tar-block-tab tar-block-tab-create',
							href: 'javascript:void(0)',
							onClick: ( event ) => {
								const el = event.target.closest(
									'.tar-block-content'
								);

								el.classList.add( 'tve-show-create' );
								el.classList.remove( 'tve-show-saved' );

								event.stopPropagation();
								event.preventDefault();
							},
						},
						__( 'Create a new block', 'thrive-cb' )
					)
				),
				createElement(
					'div',
					{
						class: 'tar-block-tabs-content',
					},
					createElement(
						'div',
						{
							class: 'tar-block-tabs-content-saved',
						},

						createElement(
							'div',
							{
								class: 'tar-block-search-container',
							},

							createElement( TextControl, {
								placeholder: __(
									'Filter block list',
									'thrive-cb'
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
										'.tar-block-search-container'
									);
									container
										.getElementsByClassName(
											'tar-block-clear-button'
										)[ 0 ]
										.classList.add( 'hidden' );
									container
										.getElementsByClassName(
											'tar-block-search-button'
										)[ 0 ]
										.classList.remove( 'hidden', 'opacity' );
								},

								onBlur( event ) {
									isBlurOverSearch =
										event.relatedTarget &&
										event.relatedTarget.classList.contains(
											'tar-block-search-button'
										);

									if ( ! isBlurOverSearch ) {
										const container = event.target.closest(
											'.tar-block-search-container'
											),
											clearEl = container.getElementsByClassName(
												'tar-block-clear-button'
											)[ 0 ],
											searchEl = container.getElementsByClassName(
												'tar-block-search-button'
											)[ 0 ];

										clearEl.classList.toggle(
											'hidden',
											! props.attributes.searchText.length
										);
										searchEl.classList.toggle(
											'hidden',
											props.attributes.searchText.length
										);
										searchEl.classList.add(
											'opacity'
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
								className: 'tar-block-search-input',
								value: props.attributes.searchText,
							} ),

							createElement(
								'a',
								{
									href: 'javascript:void(0)',
									class: `tar-block-search-button  ${
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
								createElement( Icon, {icon: 'search'} )
							),
							createElement(
								'a',
								{
									href: 'javascript:void(0)',
									className: `tar-block-clear-button ${
										props.attributes.searchText
											? ''
											: 'hidden'
									} `,
									onClick() {
										props.setAttributes( {
											searchText: '',
											searchBlockSel: '',
										} );
									},
								},
								createElement( Icon, {icon: 'no-alt'} )
							)
						),

						createElement(
							'div',
							{
								class: 'tar-block-select-wrapper',
							},
							createElement( SelectControl, {
								options,
								onChange( value ) {
									props.setAttributes( {
										selectedBlock: parseInt( value ),
									} );
								},
								value: props.attributes.selectedBlock,
							} )
						),

						createElement(
							'div',
							{
								class: `tar-block-number-warning ${
									TCB_Post_Edit_Data.symbols_number <
									maxNrOfPosts()
										? 'hidden'
										: ''
								}`,
							},
							createElement( Icon, {icon: 'info'} ),
							createElement(
								'span',
								{},
								__(
									'Displaying maximum 50 results. Try Filtering the list.',
									'thrive-cb'
								)
							)
						)
					),
					createElement(
						'div',
						{
							class: 'tar-block-tabs-content-create',
						},
						createElement(
							'div',
							{
								class: 'tar-block-create-description',
							},
							__(
								'Enter a name for your new block:',
								'thrive-cb'
							)
						),
						createElement(
							'div',
							{
								class: 'tar-block-create-input',
							},
							createElement( TextControl, {
								type: 'text',
								placeholder: __(
									'Enter new block title',
									'thrive-cb'
								),
								onChange( content ) {
									props.setAttributes( {
										blockTitle: content,
									} );
								},
								className: 'tar-block-create-input',
								value: props.attributes.blockTitle,
							} ),
							createElement(
								Button,
								{
									className: 'tar-create-block-button',
									type: 'button',
									onClick( event ) {
										const errorDom = event.target
										                      .closest(
											                      '.tar-new-block-container'
										                      )
										                      .getElementsByClassName(
											                      'tar-block-error'
										                      )[ 0 ],
											term = getTerm( 'gutenberg-block' ),
											gutenbergTermID = term
												? term.term_id
												: '';

										errorDom.textContent = '';
										errorDom.classList.remove(
											'tve-show-error'
										);

										apiFetch( {
											path: `/${TCB_Post_Edit_Data.rest_routes.symbols_short_path}`,
											method: 'POST',
											data: {
												title:
												props.attributes.blockTitle,
												tcb_symbols_tax: gutenbergTermID,
												status: 'publish',
											},
										} )
											.then( ( data ) => {
												props.setAttributes( {
													selectedBlock: parseInt(
														data.id
													),
													previewLink: getPreviewLink(
														data.permalink_template
													),
													editLink: data.edit_url,
												} );
												window.open(
													data.edit_url,
													'_blank'
												);
											} )
											.catch( function ( data ) {
												errorDom.textContent =
													data.message;
												errorDom.classList.add(
													'tve-show-error'
												);
											} );
									},
								},
								__( 'Create Block', 'thrive-cb' )
							)
						),
						createElement(
							'span',
							{
								className:
									'tar-block-error tar-block-number-warning',
							},
							'Error'
						)
					)
				)
			),

			createElement(
				'div',
				{
					class: 'tar-block-go-dash',
				},
				__( 'Go to the ', 'thrive-cb' ),
				createElement(
					'a',
					{
						href: TCB_Post_Edit_Data.symbols_dash,
						target: '_blank',
					},
					__( 'Symbols Dashboard', 'thrive-cb' )
				)
			)
		),
	];
};

export default renderAddBlock;
