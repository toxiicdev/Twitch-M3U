<?php

/* Copyright © 2016 toxiicdev.net */

if (isset($_GET['channel']) && preg_match("/^[a-zA-Z0-9_]{4,25}$/u", $_GET['channel']))
{
    $url = sprintf('https://api.twitch.tv/api/channels/%s/access_token/', $_GET['channel']);
    $json = json_decode(file_get_contents($url), true);

    header('Location: http://usher.twitch.tv/api/channel/hls/' . $_GET['channel'] . '.m3u8?player=twitchweb&token=' . rawurlencode($json['token']) . '&sig=' . $json['sig']);
}
?>
