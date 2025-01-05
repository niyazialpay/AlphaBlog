<?php
$userAgent = "Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)";

$agent = strtolower($userAgent);

$badBots = explode(',', "apache, bandit, blackwidow, bot, crawler, disco, dragonfly, grabber, harvest, httpconnect, httrack, larbin, nikto, wget, libwww, offline, searchspider, sucker, turnitinbot, zeus");

foreach ($badBots as $badBot) {
    $bot = trim($badBot);
    echo "$bot\n";
    if (str_contains($agent, $bot)) {
        echo "var";;
    }
    else{
        echo "yok";
    }
    echo "\n\n";
}
