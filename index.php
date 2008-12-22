<?php 
include("general.php");
echo '<?xml version="1.0" encoding="utf-8"?>'
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    Blogs Paraguayos 
  </title>
  <link href="style.css" rel="stylesheet" type="text/css" />
  <link rel="alternate" type="application/rss+xml" title="RSS" href="http://feedproxy.google.com/pygosfera" />
  <link rel="stylesheet" type="text/css" href="plogeshi.css" />
 </head>
<body>
  <div id="head">
   <a href="/"><img src="logo.png" hspace="30" alt="Planet PHP" title="Planet PHP" border="0" /></a>
   <div id="topnavi">
        Directorio de Blogs Paraguayos
   </div>
  </div>
   <div id="middlecontent">
   <?php foreach(get_posts($_GET['page']) as $post): ?>
   <div class="box">
    <fieldset>
     <legend><a href="<?php echo $post['blink']?>"><?php echo $post['btitle']?></a></legend><a href="<?php echo $post['link']?>" class="blogTitle"><?php echo $post['title']?></a> <?php echo ($post['date']>1000) ? date("(Y-m-d H:i:s)",$post['date'])  : ""; ?>
     <div class="feedcontent">
        <p><?php echo $post['description']?></p>
      <a href="<?php echo $post['link']?>">Continuar leyendo "<?php echo $post['title']?>"</a>
     </div>
    </fieldset>
   </div>
   <!--next-->
   <?php endforeach;?>
   <div class="box">
    <fieldset>
    <legend>More Entries</legend>
    <?php if ($_GET['page'] > 1) : ?>
    <span style="float: left;">
    <a href="?page=<?php echo $_GET['page']-1?>">&lt;&lt; Previus <?php echo POST_LIMIT?> entries</a>
    </span>
    <?php endif;?>
    <?php if (more_entries()) : ?>
    <span style="float: right;">
    <a href="?page=<?php echo $_GET['page']+1?>">Next <?php echo POST_LIMIT?> entries &gt;&gt;</a>
    </span>
    <?php endif; ?>
    </fieldset>
   </div>
  </div>
  <!--menu-->
    <div id="rightcol">
   <div class="menu">
    <fieldset>
     <legend>Blogs</legend>
     <?php foreach(get_all_blogs() as $blog) :?>
     <a href="<?php echo $blog['url']?>" class="blogLinkPad"><?php echo $blog['title']?></a>
     <?php endforeach;?>
     <h1><a href="/submit.php" class="blogLinkPad">Agrega tu blog!</a></h1>
    </fieldset>
   </div>

   <div class="menu">
    <fieldset>
     <legend>Acerca de...</legend>
     <?php ob_start();  /* my vim doesn't use UTF-8, so */?> 
     Esta página no es <a href="http://planet-php.net"><em>planetario</em></a> mas, aunque por ahora tengo el mismo diseño. Este planetario fue escrito (todo el código es 100% Paraguayo) para que funcione de una manera simple y efectiva.<br />
     La misión principal es la de dar a conocer blogs Paraguayos agrupandoles a en un solo sitio. <br />
     <b>Idea</b>: <a href="http://thelemongroup.blogspot.com/">César Sanchez</a> y <a href="http://cesarodas.com/">César D.  Rodas</a>  <br/>
     <b>Programación</b>:  <a href="http://cesarodas.com/">César D.  Rodas</a> <br/>
     <b>Diseño</b>: ???
     
     <?php echo utf8_encode(ob_get_clean()); ?> 
    </fieldset>
   </div>
   </div>
   <script type="text/javascript">
   var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
   document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
   </script>
   <script type="text/javascript">
   try {
       var pageTracker = _gat._getTracker("UA-6711785-1");
       pageTracker._trackPageview();
   } catch(err) {}</script>
</body>
</html>
