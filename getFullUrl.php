<?php
function get_full_url( $url, $webs, $page )
{
	$tmp= trim( $url );
	$tmp= str_replace( "\\", '/', $tmp );
	
	$page= explode( "?", $page );
	$page= $page[ 0 ];
	
	$arr= explode( "/", page_from_url( $page ) );
	unset( $arr[ count( $arr ) - 1 ] );
	unset( $arr[ 0 ] );
	$dirname= '';
	foreach( $arr AS $val )
	{
		$dirname .= "/". $val;
	}
	$dirname .= "/";
	
	if( substr( $tmp, 0, 7 ) == 'mailto:' ) $tmp= 'http://' . $webs . '/';
	elseif( substr( $tmp, 0, 11 ) == 'javascript:' ) $tmp= 'http://' . $webs . '/';
	elseif( substr( $tmp, 0, 2 ) == '//' ) $tmp= 'http:' . $tmp;
	elseif( substr( $tmp, 0, 1 ) == '/' ) $tmp= 'http://' . $webs . get_url_bez_tochek( $tmp );
	elseif( substr( $tmp, 0, 7 ) == 'http://' ) NULL;
	elseif( substr( $tmp, 0, 8 ) == 'https://' ) NULL;
	elseif( substr( $tmp, 0, 1 ) == '?' ) $tmp= $page . $url;
	else $tmp= 'http://'. $webs . get_url_bez_tochek( ( $dirname != 'http:/' ? $dirname : '' ) . $tmp );
	
	$tmp= explode( "#", $tmp );
	$tmp= $tmp[ 0 ];
	
	$tmp= str_replace( "www.", '', $tmp );
	
	return $tmp;
}
?>
