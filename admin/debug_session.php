<?php
session_start();

echo "<pre>";
echo "Session ID: " . session_id() . "\n\n";
echo "Session Contents:\n";
print_r($_SESSION);
echo "</pre>";

echo "<hr><a href='index.php'>Back to Admin Home</a>";
