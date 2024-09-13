<?php

/**
 * Class TVA_Video
 * - handles embed code for data provided
 */
class TVA_Video extends TVA_Media {

	/**
	 * Ready made embed code for Custom
	 *
	 * @return string
	 */
	protected function _custom_embed_code() {

		$data = $this->_data;

		/**
		 * If by any change someone puts a wistia url here we try to generate the html based on that url
		 *
		 * @see tva_get_custom_embed_code() wtf is this ?
		 */
		if ( preg_match( '/wistia/', $data['source'] ) && ! preg_match( '/(script)|(iframe)/', $data['source'] ) ) {
			$this->_data['type'] = 'wistia';

			return $this->_wistia_embed_code();
		}

		return html_entity_decode( $data['source'] );
	}

	/**
	 * Ready made embed code for Wistia
	 *
	 * @return string
	 */
	protected function _wistia_embed_code() {

		$url     = ! empty( $this->_data['source'] ) ? $this->_data['source'] : '';
		$url     = preg_replace( '/\?.*/', '', $url );
		$options = empty( $this->_data['options'] ) ? array() : $this->_data['options'];

		$split = parse_url( $url );
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) || strpos( $split['host'], 'wistia' ) === false ) {
			return '';
		}

		$exploded = explode( '/', $split['path'] );
		$video_id = end( $exploded );

		$embed_options = array( 'autoplay' => 'false', 'controls' => 'true', 'fullscreen' => 'true' );
		if ( isset( $options['autoplay'] ) ) {
			$embed_options['autoplay'] = 'true';
		}
		if ( isset( $options['hide-controls'] ) ) {
			$embed_options['controls'] = 'false';
		}
		if ( isset( $options['hide-full-screen'] ) ) {
			$embed_options['fullscreen'] = 'false';
		}

		$embed_code = "
		<script>
			window._wq = window._wq || [];
			_wq.push( {
				id: '" . $video_id . "',
				options: {
					autoPlay: " . $embed_options['autoplay'] . ",
					controlsVisibleOnLoad: " . $embed_options['controls'] . ",
					fullscreenButton: " . $embed_options['fullscreen'] . ",
					playerColor: '#000000',
				},
			} );
		</script>";
		$embed_code .= '<div class="wistia_embed wistia_async_' . $video_id . '" style="height:360px;width:640px">&nbsp;</div>';
		$embed_code .= '<script src="https://fast.wistia.com/assets/external/E-v1.js" async></script>';

		return $embed_code;
	}

	/**
	 * Ready made embed code for Vimeo
	 *
	 * @return string
	 */
	protected function _vimeo_embed_code() {

		$width   = '100%';
		$source  = ! empty( $this->_data['source'] ) ? $this->_data['source'] : '';
		$options = empty( $this->_data['options'] ) ? array() : $this->_data['options'];

		/**
		 * Vimeo videos can be of 2 forms:
		 * 1. https://vimeo.com/604925161
		 * 2. https://vimeo.com/614046843/a485d4710e
		 */
		if ( ! preg_match( '/(http|https)?:\/\/(www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|video\/|)(\d+)(?:|\/\?)(\/(.+))?/', $source, $m ) ) {
			return '';
		}

		$video_id = $m[4];
		$rand_id  = 'player' . rand( 1, 1000 );

		$src_url       = '//player.vimeo.com/video/' . $video_id;
		$embed_options = array( 'autoplay' => 'autoplay=false', 'title' => 'title=true', 'byline' => 'byline=true', 'portrait' => 'portrait=true', 'fullscreen' => 'webkitallowfullscreen mozallowfullscreen allowfullscreen' );
		if ( isset( $options['autoplay'] ) ) {
			$embed_options['autoplay'] = 'autoplay=true&muted=true';
		}
		if ( isset( $options['hide-title'] ) ) {
			$embed_options['title'] = 'title=false';
		}
		if ( isset( $options['hide-byline'] ) ) {
			$embed_options['byline'] = 'byline=false';
		}
		if ( isset( $options['hide-portrait'] ) ) {
			$embed_options['portrait'] = 'portrait=false';
		}
		if ( isset( $options['hide-full-screen'] ) ) {
			$embed_options['fullscreen'] = '';
		}

		if ( ! empty( $m[6] ) ) {
			$src_url .= '?h=' . $m[6];
		}

		$src_url .= strpos( $src_url, '?' ) === false ? '?' : '&';

		$video_height = '400';

		return "<iframe id='" . $rand_id . "' src='" . $src_url . $embed_options['autoplay'] . '&' . $embed_options['title'] . '&' . $embed_options['byline'] . '&' . $embed_options['portrait'] . "' height='" . $video_height . "' width='" . $width . "' frameborder='0' " . $embed_options['fullscreen'] . "></iframe>";
	}

	/**
	 * Ready made embed code for YouTube
	 *
	 * @return string
	 */
	protected function _youtube_embed_code() {

		$url_params = array();
		$rand_id    = 'player' . rand( 1, 1000 );
		$video_url  = empty( $this->_data['source'] ) ? '' : $this->_data['source'];
		$options    = empty( $this->_data['options'] ) ? array() : $this->_data['options'];

		if ( empty( $video_url ) ) {
			return '';
		}

		parse_str( parse_url( $video_url, PHP_URL_QUERY ), $url_params );

		$video_id = ( isset( $url_params['v'] ) ) ? trim( $url_params['v'] ) : 0;

		if ( strpos( $video_url, 'youtu.be' ) !== false ) {
			$chunks   = array_filter( explode( '/', $video_url ) );
			$video_id = array_pop( $chunks );
		}

		$src_url = '//www.youtube.com/embed/' . $video_id . '?not_used=1';

		/**
		 * Check if the url is a playlist url
		 */
		$matches = array();

		preg_match( '/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|list\/|playlist\?list=|playlist\?.+&list=))((\w|-){34})(?:\S+)?$/', $video_url, $matches );

		if ( isset( $matches[1] ) ) {
			$src_url = '//www.youtube.com/embed?listType=playlist&list=' . $matches[1];
		}
		if ( ! isset( $options['show-related'] ) || ( isset( $options['show-related'] ) && ( $options['show-related'] == 0 || $options['show-related'] === 'false' ) ) ) {
			$src_url .= '&rel=0';
		}
		if ( isset( $options['hide-logo'] ) ) {
			$src_url .= '&modestbranding=1';
		}
		if ( isset( $options['hide-controls'] ) ) {
			$src_url .= '&controls=0';
		}
		if ( isset( $options['hide-title'] ) ) {
			$src_url .= '&showinfo=0';
		}
		$hide_fullscreen = 'allowfullscreen';
		if ( isset( $options['hide-full-screen'] ) ) {
			$src_url .= '&fs=0';
		}
		if ( isset( $options['autoplay'] ) ) {
			$src_url .= '&autoplay=1&mute=1';
		}
		if ( ! isset( $options['video_width'] ) ) {
			$options['video_width']  = '100%';
			$options['video_height'] = 400;
		} else {
			if ( $options['video_width'] > 1080 ) {
				$options['video_width'] = 1080;
			}
			$options['video_height'] = ( $options['video_width'] * 9 ) / 16;
		}

		return '<iframe id="' . $rand_id . '" src="' . $src_url . '" height="' . $options['video_height'] . '" width="' . $options['video_width'] . '" frameborder="0" ' . $hide_fullscreen . ' ></iframe>';
	}

	/**
	 * Ready made embed code for Bunny.net Stream
	 *
	 * @return string
	 */
	protected function _bunnynet_embed_code() {
		$video_url = empty( $this->_data['source'] ) ? '' : $this->_data['source'];
		$options   = empty( $this->_data['options'] ) ? array() : $this->_data['options'];
		$rand_id   = 'player' . rand( 1, 1000 );

		if ( empty( $video_url ) ) {
			return '';
		}

		$video_id         = '0';
		$video_library_id = '0';

		$url_path = parse_url( $video_url, PHP_URL_PATH );

		if ( isset( $url_path ) && ! empty( $url_path ) ) {
			$url_path = ltrim($url_path, '/');
			$matches  = explode('/', $url_path);
			if ( ! empty($matches) && sizeof($matches) === 3 ) {
				$video_library_id = $matches[1];
				$video_id         = $matches[2];
			}
		}

		$src_url = 'https://iframe.mediadelivery.net/embed/' . $video_library_id . '/' . $video_id . '?';
		$allow   = 'allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"';
		if ( ! isset( $options['autoplay'] ) ) {
			$src_url .= '&autoplay=false';
		}

		if ( ! isset( $options['preload'] ) && ! isset( $options['autoplay'] ) ) {
			$src_url .= '&preload=false';
		}

		if ( isset( $options['muted'] ) ) {
			$src_url .= '&muted=true';
		}

		if ( isset( $options['loop'] ) ) {
			$src_url .= '&loop=true';
		}
		$iframe = '<iframe id="' . $rand_id . '" src="' . $src_url . ' " frameborder="0"' . $allow . ' loading="lazy" allowfullscreen ';
		if ( isset( $options['responsive'] ) ) {
			$iframe .= 'style="border: none; position: absolute; top: 0; height: 100%; width: 100%"></iframe>';
			return '<div style="position: relative; padding-top: 56.25%">' . $iframe . '</div>';
		} else {
			$iframe .= 'style="border: none" width="1280" height="720"></iframe>';
			return $iframe;
		}
	}
}
