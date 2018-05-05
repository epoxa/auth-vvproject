<?php
  header("Content-Security-Policy: default-src *; frame-src 'self'");
  header("Content-Security-Policy: frame-ancestors 'self'");
  header("Content-Security-Policy: child-src 'self'");
  header("Content-Security-Policy: script-src 'self' 'unsafe-inline' 'unsafe-eval'");
?>
<html>
<head>
    <title>Cross-domain frames denied</title>
</head>
<body>
    <p>This is a test page with CSP frame-src</p>
</body>
</html>
