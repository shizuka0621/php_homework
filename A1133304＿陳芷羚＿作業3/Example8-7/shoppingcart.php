<?php
session_start();
?>
<html>
<head>
<meta charset="utf-8">
<title>shoppingcart.php</title>
</head>
<body>
<body bgcolor="#f2f4fa">
<table border="1">
<tr bgcolor="#9ff0f0">
<td>功能</td><td>編號</td><td>名稱</td><td>價格</td><td>數量</td>
</tr>
<?php
$total = 0;
$flag = true;
foreach ($_COOKIE as $arr => $value) {
    // 檢查COOKIE名稱是否存在，且為陣列
    if (isset($_COOKIE[$arr]) && is_array($_COOKIE[$arr])) {
        if ($flag) {
            $flag = false;
            $color = "#dec8fc";
        } else {
            $flag = true;
            $color = "#f7dbd2";
        }
        $cid = isset($_COOKIE[$arr]["ID"]) ? $_COOKIE[$arr]["ID"] : "";
        $cname = isset($_COOKIE[$arr]["Name"]) ? $_COOKIE[$arr]["Name"] : "";
        $cprice  = isset($_COOKIE[$arr]["Price"]) ? $_COOKIE[$arr]["Price"] : 0;
        $cquantity = isset($_COOKIE[$arr]["Quantity"]) ? $_COOKIE[$arr]["Quantity"] : 0;

        echo "<tr bgcolor='".$color."'>";
        echo "<td><a href='delete.php?Id=".$arr."'>刪除</a></td>";
        echo "<td>".$cid."</td>";
        echo "<td>".$cname."</td>";
        echo "<td>".$cprice."</td>";
        echo "<td>".$cquantity."</td>";
        echo "</tr>";

        $total += $cprice * $cquantity;
    }
}
?>
<tr>
<td colspan="5" align="right">總金額 = NT$<?php echo $total; ?>元</td>
</tr>
</table>
<br>
<a href="catalog.php">商品目錄</a> |
<a href="shoppingcart.php">檢視購物車</a>
</body>
</html>