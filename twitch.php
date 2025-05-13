<?php
/* Copyright © 2016-2025 toxiicdev.net */
ob_start();

// Configuration
$twitch_home = file_get_contents("https://www.twitch.tv/"); // lazy to make a curl request just for it
preg_match('/clientId\s*=\s*"([0-9a-z]+)/is', $twitch_home, $auth);
$clientId = array_pop($auth);
$userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36';

// Get input from $_GET or CLI
parse_str(implode('&', array_slice($argv ?? [], 1)), $cliArgs);
$params = array_merge($_GET, $cliArgs);

// Extract and validate channel
$channel = $params['channel'] ?? null;
$format = $params['format'] ?? null;
$isValidChannel = is_string($channel) && preg_match('/^[a-zA-Z0-9_]{4,25}$/', $channel);

// Get token from Twitch API
function getTwitchToken($channel, $clientId, $userAgent) {
    $query = [
        'operationName' => 'PlaybackAccessToken_Template',
        'query' => 'query PlaybackAccessToken_Template($login: String!, $isLive: Boolean!, $vodID: ID!, $isVod: Boolean!, $playerType: String!, $platform: String!) {
            streamPlaybackAccessToken(channelName: $login, params: {platform: $platform, playerBackend: "mediaplayer", playerType: $playerType}) @include(if: $isLive) {
                value
                signature
                authorization { isForbidden forbiddenReasonCode }
                __typename
            }
            videoPlaybackAccessToken(id: $vodID, params: {platform: $platform, playerBackend: "mediaplayer", playerType: $playerType}) @include(if: $isVod) {
                value
                signature
                __typename
            }
        }',
        'variables' => [
            'isLive' => true,
            'login' => $channel,
            'isVod' => false,
            'vodID' => '',
            'playerType' => 'site',
            'platform' => 'web'
        ]
    ];

    $ch = curl_init('https://gql.twitch.tv/gql');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [ "Client-ID: $clientId", "User-Agent: $userAgent", "Content-Type: application/json" ],
        CURLOPT_POSTFIELDS => json_encode($query)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

if (!$isValidChannel) {
	$error = 'Invalid channel name. Channel name must be 4-25 characters long and can only contain letters, numbers, and underscores.';
	if ($format === 'json') {
		header('Content-Type: application/json');
		echo json_encode([ 'success' => false, 'channel' => $channel, 'error' => $error ]);
	}
	else echo "Error: " . $error;
	exit();
}

$response = getTwitchToken($channel, $clientId, $userAgent);
$data = $response['data']['streamPlaybackAccessToken'] ?? null;

if ($data == null || $data['authorization']['isForbidden']) {
	$error = 'Channel does not exist or stream is not available/forbidden from your location..';
	if ($format === 'json') {
		header('Content-Type: application/json');
		echo json_encode([ 'success' => false, 'channel' => $channel, 'error' => $error ]);
	}
	else echo "Error: " . $error;
	exit();
}

$sig = urlencode($data['signature']);
$token = urlencode($data['value']);
$sessionId = md5(time());
$url = "https://usher.ttvnw.net/api/channel/hls/$channel.m3u8?"
		. "acmb=$sessionId&allow_source=true&browser_family=chrome&browser_version=136.0"
		. "&cdm=wv&enable_score=true&fast_bread=true&os_name=macOS&os_version=10.15.7"
		. "&p=1337&platform=web&play_session_id=$sessionId&player_backend=mediaplayer"
		. "&player_version=1.41.0-rc.1&playlist_include_framerate=true"
		. "&reassignments_supported=true&sig=$sig&supported_codecs=av1,h265,h264"
		. "&token=$token&transcode_mode=cbr_v1";

if ($format === 'json') {
	header('Content-Type: application/json');
	echo json_encode([ 'success' => true, 'channel' => $channel, 'url' => $url ]);
} 
else header("Location: $url");

?>
