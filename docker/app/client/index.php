<?php

session_start();

if (isset($_SESSION['userName'], $_SESSION['publicKey'])) {

    echo "<p>Hello, <span class='user-name'>$_SESSION[userName]</span>! Your public key is <span class='public-key'>$_SESSION[publicKey]</span></p>";
    echo "<p><a href='logout.php'>Log out</a></p>";

} else {

    echo "<p>You are not logged in now.</p>";
    echo "<p><a href='login.php'>Log in</a></p>";

}
