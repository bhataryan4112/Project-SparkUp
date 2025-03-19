<?php
function storeRedirectInfo($targetUrl, $matchData = null) {
    $_SESSION['redirect_after_login'] = $targetUrl;
    if ($matchData) {
        $_SESSION['pending_match_data'] = $matchData;
    }
}
?>
