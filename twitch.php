<?php ob_start();

/* Copyright © 2016 toxiicdev.net */

$clientId = "your_client_key_here"; // Set your client key

// Curl function

function Curl($url, $header)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	$response = curl_exec($ch);
	curl_close ($ch);
	return $response;
}

// Check for get parameter 'channel' to exist and to be valid

if (isset($_GET['channel']) && preg_match("/^[a-zA-Z0-9_]{4,25}$/u", $_GET['channel']))
{
	// Lets request to twitch tv the access token
	$response = Curl("https://api.twitch.tv/api/channels/" . $_GET['channel'] . "/access_token/",
				[
					"Client-ID: $clientId",
					"Host: api.twitch.tv",
					"User-Agent: Mozilla/5.0 (Windows NT 6.3; rv:43.0) Gecko/20100101 Firefox/43.0 Seamonkey/2.40",
					"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				]
			);
	
	// If the reply contains token
	if(strstr($response, "token"))
	{
		$json = json_decode($response, true); // Decode the JSON array
		header('Location: http://usher.twitch.tv/api/channel/hls/' . $_GET['channel'] . '.m3u8?player=twitchweb&token=' . rawurlencode($json['token']) . '&sig=' . $json['sig']);
	}
	else
	{
		// Print error
		echo "Couldn't get the token";
	}
}
else
{
	// Print error
	echo "Channel name not provided or invalid";
}

?>
