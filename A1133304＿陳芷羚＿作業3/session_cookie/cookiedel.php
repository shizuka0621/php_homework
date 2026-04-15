<?php
setcookie('uID','',time()-100);
header("Refresh:0;url=login.php");
?>