<?php
$fID="Shu";
$fPWD="0502";
if(isset($_POST["uID"])&&isset($_POST["uPWD"])){
    $uID=$_POST["uID"];
    $uPWD=$_POST["uPWD"];

    if($fID==$uID&&$fPWD==$uPWD){
        header("Location: Application_Form.php");
    }else{
        echo "失敗";
        header("Refresh:2;url=Login.php");
    }
}




?>