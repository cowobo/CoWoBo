<?php
echo "<p class='left'><a href='" . wp_logout_url( "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ) . "'>Logout</a></p>";
echo "<p class='right'><a href='/?action=contact'>Contact us</a></p>";