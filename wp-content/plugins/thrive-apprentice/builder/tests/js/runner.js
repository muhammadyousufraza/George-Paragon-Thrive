#! /usr/bin/env node
const chalk = require( 'chalk' );
const puppeteer = require( 'puppeteer' );


const argv = key => {
	// Return true if the key exists and a value is defined
	if ( process.argv.includes( `--${key}` ) ) {
		return true;
	}

	const value = process.argv.find( arg => arg.startsWith( `--${key}=` ) );

	// Return null if the key does not exist and a value is not defined
	if ( ! value ) {
		return null;
	}

	return value.replace( `--${key}=`, '' );
};

/* comma separated template urls */
const urls = argv( 'templates' );

if ( urls ) {
	runTests( urls.split( ',' ).map( t => decodeURIComponent( t ) ) )
} else {
	console.warn( 'No wp url provided!', process.argv )
}

function runTests( templates = [] ) {
	const timeout = argv( 'timeout' ) || 420024;

	function __toString( log ) {

		if (
			! log ||
			! log._text.trim() ||
			[ 'JQMIGRATE', 'Synchronous XMLHttpRequest', 'Velocity', 'element was lazyloaded', 'console.trace', 'No form to register impression for', '___________.__' ].some( str => log._text.includes( str ) )
		) {
			return '';
		}

		let text = log._text;

		if ( log._type && ! text.includes( 'Now running' ) ) {
			text = log._type + ': ' + text;
		}

		return text;
	}

	( async () => {
		const browser = await puppeteer.launch( {
			defaultViewport: {
				width: 1920,
				height: 1080,
				deviceScaleFactor: 1
			},
			headless: true,
			args: [
				'--user-agent=Puppeteer-CLI-Runner',
				'--disable-setuid-sandbox',
				'--no-sandbox'
			]
		} );

		async function testUrl( url, cb ) {
			const page = await browser.newPage();

			// Attach to browser console log events, and log to node console
			await page.on( 'console', ( ...params ) => {
				for ( let i = 0; i < params.length; ++ i ) {
					const str = __toString( params[ i ] );
					if ( str ) {
						console.log( str );
					}
				}
			} );

			const moduleErrors = [];
			let testErrors = [];
			let assertionErrors = [];

			await page.exposeFunction( 'harness_moduleDone', context => {
				if ( context.failed ) {
					var msg = "Module Failed: " + context.name + "\n" + testErrors.join( "\n" );
					moduleErrors.push( msg );
					testErrors = [];
				}
			} );

			await page.exposeFunction( 'harness_testDone', context => {
				if ( context.failed ) {
					var msg = "  Test Failed: " + context.name + assertionErrors.join( "    " );
					testErrors.push( msg );
					assertionErrors = [];
					process.stdout.write( "F" );
				} else {
					process.stdout.write( "." );
				}
			} );

			await page.exposeFunction( 'harness_log', context => {
				if ( context.result ) {
					return;
				} // If success don't log

				var msg = "\n    Assertion Failed:";
				if ( context.message ) {
					msg += " " + context.message;
				}

				if ( context.expected ) {
					msg += "\n      Expected: " + context.expected + ", Actual: " + context.actual;
				}

				assertionErrors.push( msg );
			} );

			await page.exposeFunction( 'harness_done', context => {
				if ( moduleErrors.length > 0 ) {
					for ( var idx = 0; idx < moduleErrors.length; idx ++ ) {
						console.error( chalk.red( moduleErrors[ idx ] ) + "\n" );
					}
				}

				cb( context );
			} );

			await page.goto( url );

			await page.exposeFunction( 'qunit_get_timeout', () => timeout );

			await page.evaluate( () => {
				QUnit.config.testTimeout = window.qunit_get_timeout();

				// Cannot pass the window.harness_blah methods directly, because they are
				// automatically defined as async methods, which QUnit does not support
				QUnit.moduleDone( ( context ) => {
					window.harness_moduleDone( context );
				} );
				QUnit.testDone( ( context ) => {
					window.harness_testDone( context );
				} );
				QUnit.log( ( context ) => {
					window.harness_log( context );
				} );
				QUnit.done( ( context ) => {
					window.harness_done( context );
				} );
				QUnit.testStart( details => {
					/*console.info( `Now running: ${details.module} > ${details.name}` );*/
				} );
				QUnit.start();
			} );
		}

		const stats = {
			runtime: 0,
			total: 0,
			passed: 0,
			failed: 0
		};

		function hasMore() {
			return templates.length > 0;
		}

		const testCallback = context => {
			stats.runtime += context.runtime;
			stats.total += context.total;
			stats.passed += context.passed;
			stats.failed += context.failed;

			if ( hasMore() ) {
				testUrl( templates.shift(), testCallback );
			} else {
				const strLog = [
					"Time: " + stats.runtime + "ms",
					"Total: " + stats.total,
					"Passed: " + stats.passed,
					"Failed: " + stats.failed
				];
				console.log( "\n" + strLog.join( ", " ) );
				browser.close();

				process.exit( stats.failed > 0 ? 1 : 0 );
			}
		};

		await testUrl( templates.shift(), testCallback );

		function wait( ms ) {
			return new Promise( resolve => setTimeout( resolve, ms ) );
		}

		await wait( timeout );
		console.error( 'Tests timed out' );
		browser.close();
		process.exit( 1 )
	} )().catch( ( error ) => {
		console.error( error );
	} );

}
