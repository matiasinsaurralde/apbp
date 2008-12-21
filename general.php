<?php
define("APBP_PATH",dirname(__FILE__));
include(APBP_PATH."/config.php");
include(APBP_PATH."/db.php");

/*  extra functions */
function get_inputs() {
    $input   = array();
    $input[] = array("id"=>"name","name"=>"Título del blog","value"=>@$_POST['name']);
    $input[] = array("id"=>"url","name"=>"URL del blog","value"=>@$_POST['url']);
    $input[] = array("id"=>"rss","name"=>"URL del RSS","value"=>@$_POST['rss']);
    return $input;
}

function try_process() {
    if (count($_POST)==0) return "";
    $msg = "";
    session_start();
    /**/
    foreach(get_inputs() as $i)
        if ( $_POST[$i['id']]=='') 
            $msg.="<li>".$i['name']." no puede dejarse en blanco</li>";
    /**/
    if ($_SESSION['CAPTCHAString'] != $_POST['captcha']) {
        $msg.="<li>Copie correctamente la imagen</li>";
        $_SESSION['CAPTCHAString']='';
    }

    return $msg==="" ? queue_blog() : "<span class='blogTitle'>Errores</span><ul>$msg</ul>"; 
}
?>
