<?php
include("gFeed.php");
include("general.php");

header("content-type: text/xml");
if (!is_file(xml_cache)) {
   $fp = fopen("rss://".xml_cache,"w");
        $title="pygosfera";
        $link=site_url;
        $description="Coleccion de blogs del Paraguay";
        $date = date("Y-m-d H:i:s");
        $language = "es";
        fwrite($fp,1);
        foreach(get_posts() as $post) {
            foreach($post as $key=>$value)
                    $$key=$value;
            fwrite($fp,1);
        }
   fclose($fp);
}

echo file_get_contents(xml_cache);

?>
