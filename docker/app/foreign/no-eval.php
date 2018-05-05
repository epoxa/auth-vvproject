<?php
  header("Content-Security-Policy: script-src 'self'; object-src 'self'");
?>
<html>
<head>
    <title>No eval allowed</title>
</head>
<body>
    <p>This is a test page with CSP</p>
</body>
</html>
