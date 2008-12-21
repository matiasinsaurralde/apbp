<?php 
include("general.php");
$try = try_process();
echo '<?xml version="1.0" encoding="utf-8"?>';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    Blogs Paraguayos | Agregar
  </title>
  <link href="/style.css" rel="stylesheet" type="text/css" />
  <link rel="alternate" type="application/rss+xml" title="RSS" href="http://feedproxy.google.com/pygosfera" />
  <link rel="stylesheet" type="text/css" href="/plogeshi.css" />
 </head>
<body>
  <div id="head">
   <a href="/"><img src="/logo.png" hspace="30" alt="PYgosfera" title="PYgosfera" border="0" /></a>
   <div id="topnavi">
        Directorio de Blogs Paraguayos 
   </div>
  </div>
   <div id="middlecontent">
   <div class="box">
    <fieldset>
     <legend>Requisitos</legend>
    
     <div class="feedcontent">
        <?php ob_start()?>
        <ul>
            <li>La persona debe vivir ser paraguaya o vivir en Paraguay</li>
            <li>El contenido debe estar en español (preferentemente), guaraní o en ingles</li>
        </ul>
        <?php echo utf8_encode(ob_get_clean())?>
     </div>
    </fieldset>
   </div>

   <div class="box">
    <fieldset>
     <legend>Postular blog</legend>
     <?php
        if($try===true) {
            echo "<h1>Su blog esta pendiente de aprobacion</h1>";
        } else {
     ?>
    <b>Por favor, no al spam</b><br/>
    <?php echo $try ?>
     <div class="feedcontent">
        <?php ob_start()?>
        <form action="/submit.php" id="submit" method="post">
        <?php 
        foreach(get_inputs() as $i):
        ?>
        <label for="<?php echo $i['id']?>"><?php echo $i['name']?>:</label><input id="<?php echo $i['id']?>" name="<?php echo $i['id']?>" value="<?php echo $i['value']?>" /><br/>
        <?php endforeach;?>
        <label for="desc">Descripción del blog</label>
        <textarea name="desc" id="desc" cols=40 rows=10><?php echo @$_POST['desc']?></textarea>
        <span style="margin-left: 130px"><img src="/captcha/?<?php echo time()?>" ><br/></span>
        <label for="captcha">Eres humano?</label>
        <input type="input" id="captcha" name="captcha" />
        <input type="submit" value="Postularce!" />
        </form>
        <?php echo utf8_encode(ob_get_clean())?>
     </div>
    </fieldset><?php } //end if?>
   </div>
   </div>
   <!---->
</body>
</html>
