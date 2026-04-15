<?php
session_start();
session_destroy();
echo"<h1>Youhave been logged out.<br>Please wait two seconds.</h1>";
header("Refresh:2;url=login.php");


?>