<?php

function isMobile() {
    $isMobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    if(!isset($_COOKIE['isMobile'])) {
        setcookie("isMobile", $isMobile, time()+3600, "/","", 0); 
    } else {
        $isMobile = $_COOKIE['isMobile'];
    }
    return $isMobile;
}

?>