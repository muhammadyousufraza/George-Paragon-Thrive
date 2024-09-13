module.exports = {
	lyrics: '<p>Oh, let the sun beat down upon my face</p>' +
		'<p>And stars fill my dream</p>' +
		'<p>I\'m a traveler of both time and space</p>' +
		'<p>To be where I have been</p>' +
		'<p>To sit with elders of the gentle race</p>' +
		'<p>This world has seldom seen</p>' +
		'<p>They talk of days for which they sit and wait</p>' +
		'<p>All will be revealed</p>',
	reset: {
		'background-image': '',
		'background-color': '',
		'border-color': '',
		'border-width': 0,
		'border-radius': 0
	},
	default: {
		color: 'rgb(0, 174, 239)',
		align: 'justify',
		style: [ 'bold', 'line-through', 'underline' ],
		transform: 'capitalize',
		size: '3em',
		lineHeight: '2em',
		letterSpacing: '5px',
		font: '"Comic Sans MS", cursive, sans-serif'
	},
	hover: {
		color: 'rgb(0, 174, 239)',
		align: 'right',
		style: [ 'italic' ],
		transform: 'uppercase',
		size: '4em',
		lineHeight: '1em',
		letterSpacing: '10px',
		font: 'Verdana, sans-serif'
	},
	colors: {
		cyan: {
			hex: '#00ffff',
			rgb: 'rgb(0, 255, 255)'
		},
		red: {
			hex: '#ff0000',
			rgb: 'rgb(255, 0, 0)'
		}
	},
	elements: [
		{
			name: 'Template Container',
			selector: '#content'
		},
		{
			name: 'Main Section Container',
			selector: '.main-container'
		},
		{
			name: 'Top Section',
			selector: '.top-section'
		},
		{
			name: 'Bottom Section',
			selector: '.bottom-section'
		},
		{
			name: 'Sidebar Section',
			selector: '.sidebar-section'
		},
		{
			name: 'Content Section',
			selector: '.content-section'
		}
	]
};
