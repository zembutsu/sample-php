<?php

ini_set('display_errors', "On");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

print "<h1>counter</h1>\n";

$mysqlhost = "host";
$mysqluser = "user";
$mysqlpass = "pass";
$mysqldata = "database";

$connect = new mysqli($mysqlhost, $mysqluser, $mysqlpass, $mysqldata);

if (!$connect) {
        die ("database error");
}

$result = $connect->query("UPDATE counter SET count = count + 1");
$result = $connect->query("SELECT count FROM counter");
$count = $result->fetch_row();
print "count=$count[0]";
mysqli_close($connect);

?>
