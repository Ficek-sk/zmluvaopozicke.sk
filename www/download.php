<?php
	define( 'DOWNLOAD_COUNTER_API_KEY', 'X*AoiE7r5q9c>' );
	define( 'DOWNLOAD_DOMAIN', 'zmluvaopozicke.sk' );
	define( 'DOWNLOAD_FOLDER', 'downloads' );

	$file = isset( $_GET['file'] ) ? $_GET['file'] : '';

	/* Security */
	$file = str_replace( '../', '', $file );
	$file = str_replace( './', '', $file );
	if( !$file ){
		header('Content-Type: text/html; charset=utf-8');
		exit( 'Nesprávny názov súboru' );
	}

	$filePath = DOWNLOAD_FOLDER . '/' . $file;
	if( !file_exists( $filePath ) ){
		header('Content-Type: text/html; charset=utf-8');
		exit( 'Súbor neexistuje.' );
	}

	$url = 'https://www.rsba.sk/counter.php';
	$url .= '?api_key=' .  urlencode( DOWNLOAD_COUNTER_API_KEY ) . '&domain=' .  urlencode( DOWNLOAD_DOMAIN ) . '&file=' .  urlencode( $file );
	@file_get_contents( $url ); //Count download

	header( 'Content-Description: File Transfer' );
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Disposition: attachment; filename=' . basename( $filePath ) );
	header( 'Expires: 0' );
	header( 'Cache-Control: must-revalidate' );
	header( 'Pragma: public' );
	header( 'Content-Length: ' . filesize( $filePath ) );

	ob_clean();
	flush();
	readfile( $filePath );

	exit;

?>
