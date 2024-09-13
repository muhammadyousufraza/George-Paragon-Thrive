import apiFetch from '@wordpress/api-fetch';
import { setUrlParam } from "@/utils/data-fn";

/**
 * Set wp-rest nonce
 */
apiFetch.use( apiFetch.createNonceMiddleware( TAPAdmin.nonce ) );
/**
 * Generic model for CRUD operations
 */
export default class ApiRequest {
	type = '';

	constructor( type ) {
		this.type = type;
	}

	/**
	 * Generic api request through apiFetch of WordPress
	 * @param path
	 * @param method
	 * @param payload
	 * @returns {*}
	 */
	static wpFetchRequest( {path, method = 'GET', body = null} ) {
		const options = {
			path,
			method
		}

		if ( body !== null ) {
			if ( method === 'GET' ) {
				Object.keys( body ).forEach( key => options.path = setUrlParam( options.path, key, body[ key ] ) );
			} else {
				options[ 'data' ] = body;
			}
		}

		return apiFetch( options )
	}

	/**
	 * Generic api request through fetch API
	 * @param path
	 * @param method
	 * @param headers
	 * @param body
	 * @returns {Promise<any>}
	 */
	static genericFetch( {path = '', method = 'GET', headers = {}, body = {}} ) {
		headers = {
			...{
				'X-WP-Nonce': TAPAdmin.nonce
			},
			...headers
		}
		const fetchOptions = {
			method,
			headers,
		};
		if ( ! TAPAdmin._.isEmpty( body ) || ( body instanceof FormData && [ ...body.keys() ].length ) ) {
			fetchOptions.body = body;
		}
		return fetch( path, fetchOptions ).then( response => {
			if ( response.ok ) {
				return response.json();
			} else {
				return response.json().then( data => {
					const error = new Error( 'Fetch failed' );
					error.data = data;
					throw error;
				} )
			}
		} );
	}

	/**
	 * Generic AJAX request through jQuery
	 * @param path
	 * @param method
	 * @param headers
	 * @param data
	 * @param withCredentials
	 * @param processData
	 * @param contentType
	 * @returns {*}
	 */
	static jqueryFetch( {path, method = 'GET', headers = {}, data = {}, withCredentials = true, processData = true, contentType = 'json'} ) {
		return TAPAdmin.$.ajax(
			{
				headers,
				url: path,
				data,
				xhrFields: {
					withCredentials
				},
				processData,
				contentType,
				type: method,
			}
		)
	}

	route() {
		return `${TAPAdmin.routes}/${this.type}`;
	}

	getAll() {
		return apiFetch( {
			path: this.route()
		} );
	}

	getOne( id ) {
		return apiFetch( {
			path: `${this.route()}/${id}`
		} );
	}

	getOneByParams( payload = {} ) {
		let path = this.route();

		Object.keys( payload ).forEach( key => path = setUrlParam( path, key, payload[ key ] ) );

		return apiFetch( {
			path
		} )
	}

	create( props, id ) {
		return apiFetch( {
			path: `${this.route()}/${id}`,
			method: 'POST',
			data: props,
		} );
	}

	updateOne( id, props ) {
		return apiFetch( {
			path: `${this.route()}/${id}`,
			method: 'PUT',
			data: props,
		} );
	}

	deleteOne( id ) {
		return apiFetch( {
			path: `${this.route()}/${id}`,
			method: 'DELETE'
		} );
	}

	duplicateOne( id ) {
		return apiFetch( {
			path: `${this.route()}/${id}/duplicate`,
			method: 'PUT'
		} );
	}

	deleteByProps( id, props ) {
		return apiFetch( {
			path: `${this.route()}/${id}`,
			method: 'DELETE',
			data: props,
		} );
	}

	deleteAll() {
		return apiFetch( {
			path: `${this.route()}`,
			method: 'DELETE'
		} );
	}

	updateSettings( props ) {
		return apiFetch( {
			path: `${this.route()}/settings`,
			method: 'POST',
			data: props,
		} )
	}

	customRequest( subPath, data = {}, method = 'GET' ) {
		return apiFetch( {
			path: `${this.route()}/${subPath}`,
			method,
			data
		} )
	}

	syncObject( props ) {
		return apiFetch( {
			path: `${this.route()}`,
			method: 'PUT',
			data: props,
		} )
	}

	sendWebhook( props ) {
		return apiFetch( {
			path: `${this.route()}/webhook`,
			method: 'POST',
			data: props,
		} )
	}
}
