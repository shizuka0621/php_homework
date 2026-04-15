<?php
if(isset($_COOKIE["uID"])){
    echo $_COOKIE["uID"]."歡迎回來!";
    echo "<a href='cookiedel.php'>刪除cookie</a>";
}
?>
<form action="logincheck.php" method="post">
    ID:<input type="text" name="uID"><br/>
    Password:<input type="text" name="uPwd"><br/>
    <input type="submit">
<?php
echo date("Y-m-d H:i:s", time());
?>

</form>