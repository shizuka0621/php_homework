<?php
session_start();
if(isset($_SESSION['login'])){
    if($_SESSION['login']=='admin'){
        echo"<h1>Welcome!Admin Success</h1>";
        echo"<a href='logout.php'>Logout</a>";
    }else{
        echo"<h1>非法進入網頁</h1>";
        header("Refresh:2;url=login.php");
    }
}else{
    echo"<h1>非法進入網頁</h1>";
    header("Refresh:2;url=login.php");
}
?>
