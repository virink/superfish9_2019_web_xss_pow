<?php
    // clever way to enable CSP ?
    error_reporting(0);
    ini_set('display_errors', 'Off');
    header("Content-Type: text/javascript");
    header('Cache-Control: no-cache, must-revalidate');   
    header('Pragma: no-cache');
?>
meta = document.createElement('meta');
meta.httpEquiv = 'Content-Security-Policy';
meta.content = "script-src 'nonce-<?php echo $_GET['nonce'];?>'";
document.head.appendChild(meta);