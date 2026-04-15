<?php
session_start();
$sID="Student";
$sPWD="student";

$tID="Teacher";
$tPWD="teacher";

$aID="Admin";
$aPWD="admin";

$uID=$_POST["uID"];
$uPwd=$_POST["uPwd"];

$date=strtotime("+5 days",time());

if($uID==$sID&&$uPwd==$sPWD){
    header("Refresh:0;url=success.php");
    $_SESSION['login']='user';
    setcookie("uID",$uID,$date);
}elseif($uID==$tID&&$uPwd==$tPWD){
    header("Refresh:0;url=success.php");
    $_SESSION['login']='user';
    setcookie("uID",$uID,$date);
}elseif($uID==$aID&&$uPwd==$aPWD){
    header("Refresh:0;url=admin.php");
    $_SESSION['login']='admin';
    setcookie("uID",$uID,$date);
}else{
    echo "登入失敗";
    header("Refresh:2;url=login.php");
}
?>