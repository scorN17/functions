<?php
function curl_exec_followlocation(&$curl, &$uri)
{
	// v2.1
	// Date 16.02.2017
	// -----------------------------------------
	if(preg_match("/^(http(s){0,1}:\/\/[a-z0-9\.-]+)(.*)$/i", $uri, $matches)!==1) return;
	$website= $matches[1];
	do{
		// if($referer) curl_setopt($curl, CURLOPT_REFERER, $referer);
		curl_setopt($curl, CURLOPT_URL, $uri);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		$response= curl_exec($curl);
		if(curl_errno($curl)) return false;
		$headers= str_replace("\r",'',$response);
		$headers= explode("\n\n",$headers,2);
		if(preg_match("/^location: (.*)$/im", $headers[0], $matches)===1)
		{
			$location= true;
			$referer= $uri;
			$uri= trim($matches[1]);
			if(preg_match("/^http(s){0,1}:\/\/[a-z0-9\.-]+/i", $uri, $matches)!==1)
				$uri= $website.(substr($uri,0,1)!='/'?'/':'').$uri;
		}else $location= false;
		if($location)
		{
			if($redirects_list[$uri]<=1) $redirects_list[$uri]++;
				else $location= false;
		}
	}while($location);
	return $response;
}
