<?php

class Iworks_Favicon {

	/**
	 * Configuration for:
	 * /manifest.json
	 * /browserconfig.xml
	 *
	 * @since 1.0.0
	 */
	private $color_title      = '#2d2683';
	private $color_theme      = '#ffffff';
	private $color_background = '#ffffff';
	private $short_name       = '';

	public function __construct() {
		add_action( 'wp_head', [ $this, 'html_head' ], PHP_INT_MAX );
		add_filter( 'get_site_icon_url', [ $this, 'get_site_default_icon_url' ], 10, 3 );
		add_filter( 'site_icon_meta_tags', [ $this, 'site_icon_meta_tags' ] );
		add_action( 'parse_request', [ $this, 'manifest_json' ] );
		add_action( 'parse_request', [ $this, 'browserconfig_xml' ] );
		add_action( 'parse_request', [ $this, 'request_favicon' ] );
	}

	/**
	 * Get default favicon
	 *
	 * @since 1.0.0
	 */
	public function get_site_default_icon_url( $url, $size, $blog_id ) {
		if ( ! empty( $url ) ) {
			return $url;
		}
		return get_stylesheet_directory_uri() . '/assets/images/icons/favicon/apple-icon.png';
	}

	/**
	 *
	 * @since 1.0.0
	 */
	public function html_head() {
		echo '<meta name="msapplication-config" content="/browserconfig.xml" />' . PHP_EOL;
	}

	/**
	 * get url
	 *
	 * @since 1.0.0
	 */
	private function get_favicon_url( $icon, $extension = 'png' ) {
		$file = sprintf( '%s.%s', $icon, $extension );
		$file = sprintf( 'icons/favicon/%s?v=%s', sanitize_file_name( $file ), $this->version );
		$url  = $this->get_asset_url( $file );
		return wp_make_link_relative( esc_url( $url ) );
	}

	/**
	 * Favicons + meta settings
	 *
	 * @since 1.0.0
	 */
	public function site_icon_meta_tags( $meta_tags ) {
		$meta_tags = array();
		$icons     = array(
			'icon'    => array(
				16,
				32,
				96,
			),
			'apple'   => array(
				57,
				60,
				72,
				76,
				114,
				120,
				152,
				180,
			),
			'android' => array(
				192,
			),
		);
		foreach ( $icons as $type => $sizes ) {
			foreach ( $sizes as $size ) {
				$s    = sprintf( '%1$dx%1$d', $size );
				$mask = $file = '';
				switch ( $type ) {
					case 'icon':
						$file = sprintf( 'favicon-%s', $s );
						$mask = '<link rel="icon" type="image/png" sizes="%1$s" href="%2$s" />';
						break;
					case 'apple':
						$file = sprintf( 'apple-icon-%s', $s );
						$mask = '<link rel="apple-touch-icon" sizes="%1$s" href="%2$s" />';
						break;
					case 'android':
						$file = sprintf( 'android-icon-%s', $s );
						$mask = '<link rel="apple-touch-icon" sizes="%1$s" href="%2$s" />';
						break;
				}
				if ( ! empty( $mask ) && ! empty( $file ) ) {
					$meta_tags[] = sprintf( $mask, $s, $this->get_favicon_url( $file ) );
				}
			}
		}
		$meta_tags[] = sprintf(
			'<link rel="shortcut icon" href="%s" />',
			$this->get_favicon_url( 'favicon', 'ico' )
		);
		$meta_tags[] = sprintf(
			'<link rel="mask-icon" href="%s" color="#5bbad5" />',
			$this->get_favicon_url( 'safari-pinned-tab', 'svg' )
		);
		$meta_tags[] = sprintf( '<meta name="msapplication-TileColor" content="%s">', esc_attr( $this->color_title ) );
		$meta_tags[] = sprintf( '<meta name="theme-color" content="%s" />', esc_attr( $this->color_theme ) );
		$meta_tags[] = sprintf(
			'<meta name="msapplication-TileImage" content="%s" />',
			$this->get_favicon_url( 'ms-icon-144x144' )
		);
		$meta_tags[] = '<link rel="manifest" href="/manifest.json" />';
		return $meta_tags;
	}

	/**
	 * Handle "/favicon.json" request.
	 *
	 * @since 1.0.0
	 */
	public function request_favicon() {
		if (
			! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		if ( '/favicon.ico' !== $_SERVER['REQUEST_URI'] ) {
			return;
		}
		header( 'Location: ' . $this->get_favicon_url( 'favicon', 'ico' ) );
		exit;
	}

	/**
	 * Handle "/browserconfig.xml" request.
	 *
	 * @since 1.0.0
	 */
	public function browserconfig_xml() {
		if (
			! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		if ( '/browserconfig.xml' !== $_SERVER['REQUEST_URI'] ) {
			return;
		}
		header( 'Content-type: text/xml' );
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo PHP_EOL;
		echo '<browserconfig>';
		echo '<msapplication>';
		echo '<tile>';
		$sizes = array( 70, 150, 310 );
		foreach ( $sizes as $size ) {
			$url = $this->get_asset_url(
				sprintf(
					'icons/favicon/ms-icon-%1$dx%1$d.png',
					$size
				)
			);
			printf( '<square%1$dx%1$dlogo src="%2$s"/>', $size, esc_url( $url ) );
		}
		printf( '<TileColor>%s</TileColor>', $this->color_title );
		echo '</tile>';
		echo '</msapplication>';
		echo '</browserconfig>';
		exit;
	}

	/**
	 * Handle "/manifest.json" request.
	 *
	 * @since 1.0.0
	 */
	public function manifest_json() {
		if (
			! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		if ( '/manifest.json' !== $_SERVER['REQUEST_URI'] ) {
			return;
		}
		$data = array(
			'name'             => get_bloginfo( 'sitename' ),
			'short_name'       => $this->short_name,
			'theme_color'      => $this->color_theme,
			'background_color' => $this->color_background,
			'display'          => 'standalone',
			'Scope'            => '/',
			'start_url'        => '/',
			'icons'            => array(
				array(
					'src'     => esc_url( $this->get_asset_url( 'icons/favicon/android-icon-36x36.png' ) ),
					'sizes'   => '36x36',
					'type'    => 'image/png',
					'density' => '0.75',
				),
				array(
					'src'     => esc_url( $this->get_asset_url( 'icons/favicon/android-icon-48x48.png' ) ),
					'sizes'   => '48x48',
					'type'    => 'image/png',
					'density' => '1.0',
				),
				array(
					'src'     => esc_url( $this->get_asset_url( 'icons/favicon/android-icon-72x72.png' ) ),
					'sizes'   => '72x72',
					'type'    => 'image/png',
					'density' => '1.5',
				),
				array(
					'src'     => esc_url( $this->get_asset_url( 'icons/favicon/android-icon-96x96.png' ) ),
					'sizes'   => '96x96',
					'type'    => 'image/png',
					'density' => '2.0',
				),
				array(
					'src'     => esc_url( $this->get_asset_url( 'icons/favicon/android-icon-144x144.png' ) ),
					'sizes'   => '144x144',
					'type'    => 'image/png',
					'density' => '3.0',
				),
				array(
					'src'     => esc_url( $this->get_asset_url( 'icons/favicon/android-icon-192x192.png' ) ),
					'sizes'   => '192x192',
					'type'    => 'image/png',
					'density' => '4.0',
				),
			),
			'splash_pages'     => null,
		);
		header( 'Content-Type: application/json' );
		echo json_encode( $data );
		exit;
	}

	/**
	 * Get assets URL
	 *
	 * @since 1.0.0
	 *
	 * @param string $file File name.
	 * @param string $group Group, default "images".
	 *
	 * @return string URL into asset.
	 */
	private function get_asset_url( $file, $group = 'images' ) {
		$url = sprintf(
			'%s/assets/%s/%s',
			$this->url,
			$group,
			$file
		);
		return esc_url( $url );
	}

}

