<?php
echo "<h2>Welcome to Coders Without Borders!</h2>";
echo "<p>Is this your first time? <a href='#'>Take the tour!</a></p>";

if ( cowobo()->query->action == 'login' ) return;

echo "<p>Returning visitor? <a href='/?action=login' class='toggle-loginform'>Login</a></p>";
echo "<div class='loginform hide-if-js'>";
cowobo()->query->login = 'login';
include TEMPLATEPATH . '/templates/login.php';
echo "</div>";