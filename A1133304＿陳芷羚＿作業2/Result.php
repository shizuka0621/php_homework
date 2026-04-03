<?php
    $mGender=$_POST["gender"];
    $mGrade = $_POST["grade"];
    $mCity=$_POST["city"];
    $mBirthday=$_POST["birthday"];
    $mTel=$_POST["tel"];
    $mEmail=$_POST["email"];
    $mEmergency=$_POST["emergency"];
    $mEtel=$_POST["etel"];
    $mLike=$_POST["like"];
    $mFood=$_POST["food"];
    $mPassport=$_POST["passport"];
    $mComment=$_POST["comment"];

    echo "姓名:";
    echo $_POST["name"]. "<br/>";
    
    if($mGender=="boy"){
        echo "Your Gender is:男性<br/>";
    }else if($mGender=="girl"){
        echo "Your Gender is:女性<br/>";
    }else{
        echo "Your Gender is:其他<br/>";
    }

    echo "就讀:".$mGrade."<br>";
    echo "來自:".$mCity."<br>";
    echo "出生".$mBirthday."<br>";
    echo "連絡電話:".$mTel."<br/>";
    echo "電子郵件:".$mEmail. "<br/>";
    echo "緊急聯絡人:".$mEmergency. "<br/>";
    echo "緊急聯絡人電話:".$mEtel. "<br/>";

    echo "最期待的活動:";
    for($i=0;$i<count($mLike);$i++){
        echo $mLike[$i];
    }
    echo "<br>";

    echo "飲食需求:".$mFood."<br>";

    if($mPassport=="yes"){
        echo "護照狀態:有<br/>";
    }else{
        echo "護照狀態:無<br/>";
    }

    echo nl2br(($mComment));
    echo "<br>";
?>