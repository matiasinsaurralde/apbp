<?php
include("../general.php");
include("../gFeed.php");
include("../gHttp.php");

foreach(get_blogs() as $id => $rss) {
    $fp = fopen("rss://g$rss","r"); 
    fread($fp,1);
    while ( fread($fp, 1) ) { 
        print "$rss => $title - ".strtotime($date)."\n";
        do_insert_post($id,$title,$link,strtotime($date),$description);
    }
    fclose($fp);
}
@unlink(xml_cache);
?>
