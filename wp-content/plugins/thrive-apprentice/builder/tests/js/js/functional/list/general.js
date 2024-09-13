QUnit.module( 'General List', {} );

QUnit.test( 'General checks', ( assert ) => {
	assert.true( ! TVE.Theme.template.isSingular(), 'This is not a singular template' );
} );
