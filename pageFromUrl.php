<?php
function page_from_url( $str )
{
	$str= trim( $str );
	$str= str_replace( "https://", '', $str );
	$str= str_replace( "http://", '', $str );
	$str= str_replace( "../", '.../', $str );
	$str= str_replace( "./", '/', $str );
	$str= str_replace( "//", '/', $str );
	
	$str2= explode( "/", $str );
	$str= str_replace( $str2[0], '', $str );
	$str= trim( $str );
	
	return $str;
}
?>
