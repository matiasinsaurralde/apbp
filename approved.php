<?php
include("general.php");

if (enable_blog($_GET['id'],$_GET['key']))
    die("done");
die("bad hacker, do something better");
?>
