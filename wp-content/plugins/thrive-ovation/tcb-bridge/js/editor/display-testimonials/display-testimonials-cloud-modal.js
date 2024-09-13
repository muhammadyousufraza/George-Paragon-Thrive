module.exports = TVE.CloudTemplatesCategories.extend( {
	$$type: 'display_testimonials',
	categoryId: 'display_testimonials_type',
	templateName: 'display_testimonials',

	alter_wrapper_attributes ( attr, data ) {
		return {
			'data-ct-name': data.name,
			'data-ct': `${data.type}-${data.id}`,
		};
	},
} );
