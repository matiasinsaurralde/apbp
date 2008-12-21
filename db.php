<?php

/* code */
if (!isset($_GET['page']) || (int)$_GET['page']==0) $_GET['page']=1;

/* dirty model */
function get_db_conn() {
    static $db=false;
    if ($db===false) {
        $db  = mysql_connect(my_host,my_user,my_pass) or die("error-db");
        mysql_select_db(my_db,$db) or die("error-sdb");
    }
    return $db;
}

function get_blogs() {
    $db = get_db_conn();
    $sql = mysql_query("SELECT id,rss FROM blogs WHERE allow=1",$db) or die("db-err");
    $result = array();
    while($r = mysql_fetch_array($sql)) {
        $result[$r['id']] = $r['rss'];
    }
    mysql_free_result($sql);
    return $result;
}

function enable_blog($id,$key) {
    $id=(int)$id;
    if ($id==0) return false;
    $sql = mysql_query("SELECT url,rss,title FROM blogs WHERE id=$id && allow=0",get_db_conn());
    if (mysql_num_rows($sql)!=1)return false;
    $out_key ="";
    foreach(mysql_fetch_array($sql) as $k=>$value)
        if(is_numeric($k)) $our_key.=addslashes(utf8_decode($value));
    if(md5($our_key)!=$key) return false;
    mysql_query("UPDATE blogs SET allow=1 WHERE id=$id",get_db_conn());
    return true;
}

function queue_blog() {
    foreach($_POST as $id=>$value)
        $$id=addslashes($value); /* sql injection */
    $sqlstr = "INSERT INTO blogs(url,rss,title) VALUES('$url','$rss','$name')";
    $sql=mysql_query($sqlstr,get_db_conn());
    if(!$sql) return "<b>Error en la base datos</b>";

    /* avisar al admin, un mail rapido, again dirty */
    $link = site_url."approved.php?id=".mysql_insert_id(get_db_conn())."&key=".md5($url.$rss.$name);
    $str=<<<EOT
Has recibido una nueva  postulacion.

$link

EOT;
    $str.= print_r($_POST,true); /* dirty */ 
    mail(admin,"[pygosfera] nueva postulacion",$str,"from: robot@pygosfera.com");
    return true;
}

function get_all_blogs() {
    $db = get_db_conn();
    $sql = mysql_query("SELECT * FROM blogs WHERE allow=1 ORDER BY title",$db) or die("db-err");
    $result = array();
    while($r = mysql_fetch_array($sql)) {
        $result[] = $r;
    }
    mysql_free_result($sql);
    return $result;
}

function do_insert_post($blog, $title,$link,$date,$description) {
    if ((int)$date==0) $date = time();
    foreach(array('blog','title','link','date','description') as $id) {
        $$id = addslashes($$id);
    }
    $sqlstr="INSERT INTO posts VALUES(null,'$blog','$title','$link','$date','$description',0)";
    $done=true;
    mysql_query($sqlstr,get_db_conn()) or ($done=false);
    return $done;
}

function more_entries() {
    return $GLOBALS['has_more'];
}

function get_posts($page=1,$limit=POST_LIMIT) {
    $start=$limit*(--$page);
    $limit++; /* for more_entries */
    $sql = mysql_query("SELECT posts.*,blogs.url as blink, blogs.title as btitle  FROM posts INNER JOIN blogs ON (posts.blog = blogs.id) ORDER BY date DESC LIMIT $start,$limit",get_db_conn()) or die("error ");
    $result = array();
    /**
     *  dirty, ugly but efficient way to
     *  know if there is more entries.
     */
    $GLOBALS['has_more']=false;
    $e=0;
    if (mysql_num_rows($sql)==$limit) 
        $GLOBALS['has_more']=true;
    while ($r=mysql_fetch_array($sql)) {
        if (++$e == $limit) break;
        $r['description'] = substr(nl2br(strip_tags($r['description'])),0,400)."...";
        $r['link']        = site_url.'post/'.$r['id'];
        $result[] = $r;
    }
    return $result;
}

function follow_link($id) {
    $id = (int)$id;
    if($id==0)return false;
    $sql = mysql_query("SELECT link  FROM posts WHERE id=$id",get_db_conn()) or die("error");
    if (mysql_num_rows($sql)!=1) {
        header("location: /");
        return;
    }
    $info = mysql_fetch_array($sql);
    mysql_query("UPDATE posts SET visits = visits+1 WHERE id=$id",get_db_conn()) or die("error-1");
    header("location: ".$info['link']);
}


?>
