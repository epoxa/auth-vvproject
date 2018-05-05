<?php
  header("Content-Security-Policy: unsafe-eval; default-src 'self'");
?>
<html>
<head>
    <title>Cross-domain sources denied</title>
</head>
<body>
    <p>This is a test page with CSP default-src</p>
</body>
</html>
