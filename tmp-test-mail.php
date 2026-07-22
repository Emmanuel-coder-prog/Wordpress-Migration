<?php
// test
require "/var/www/html/gh/wp-load.php";
$result = wp_mail("test@example.test", "CETECH local mail test", "Mailpit delivery is working.");
var_dump($result);
