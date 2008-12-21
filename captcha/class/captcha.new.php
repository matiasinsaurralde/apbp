<?php

  /******************************************************************

   Projectname:   CAPTCHA class
   Version:       2.0
   Author:        Pascal Rehfeldt <Pascal@Pascal-Rehfeldt.com>
   Last modified: 15. January 2006

   * GNU General Public License (Version 2, June 1991)
   *
   * This program is free software; you can redistribute
   * it and/or modify it under the terms of the GNU
   * General Public License as published by the Free
   * S:oftware Foundation; either version 2 of the License,
   * or (at your option) any later version.
   *
   * This program is distributed in the hope that it will
   * be useful, but WITHOUT ANY WARRANTY; without even the
   * implied warranty of MERCHANTABILITY or FITNESS FOR A
   * PARTICULAR PURPOSE. See the GNU General Public License
   * for more details.

   Description:
   This class can generate CAPTCHAs, see README for more details!

  ******************************************************************/

  error_reporting(E_ALL);

  require('./class/filter.class.php');  
  require('./class/error.class.php');

  class captcha
  {

    var $CaptchaString;
    var $fontpath;
    var $fonts;

    function captcha ($str='',$fname='')
    {
      
      $this->fontpath = getcwd().'/fonts/';      
      $this->fonts    = $this->getFonts();
      $errormgr       = new error;

      if ($this->fonts == FALSE)
      {
      	$errormgr->addError('No fonts available!');
      	$errormgr->displayError();
      	die();
      }

      if (function_exists('imagettftext') == FALSE)
      {
        $errormgr->addError('');
        $errormgr->displayError();
        die();
      }
      $this->CaptchaString = $str;
      $this->fname = $fname;
      $this->makeCaptcha();
    } //captcha
    
    function getFonts ()
    {

      $fonts = array();    
      if ($handle = @opendir($this->fontpath))
      {
   
        while (($file = readdir($handle)) !== FALSE)
        {
       
          $extension = strtolower(substr($file, strlen($file) - 3, 3));
          if ($extension == 'ttf')
          {
          	
            $fonts[] = $file;
            
          }
        
        }
        
        closedir($handle);

      }
      else
      {
      	
      	return FALSE;
      	
      }
      
      if (count($fonts) == 0)
      {
      	
      	return FALSE;
      	
      }
      else
      {
      	
      	return $fonts;
      	
      }
    
    } //getFonts
    
    function getRandFont ()
    {
    
      $str= $this->fontpath . $this->fonts[mt_rand(0, count($this->fonts) - 1)];
      return $str;
    } //getRandFont


    function makeCaptcha ()
    {
      $imagelength = strlen($this->CaptchaString) * 25 + 16;
      $imageheight = 75;

      $image       = imagecreate($imagelength, $imageheight);

      //$bgcolor     = imagecolorallocate($image, 222, 222, 222);
      $bgcolor     = imagecolorallocate($image, 0xf2, 0xf2, 0xf2);

      $stringcolor = imagecolorallocate($image, 0, 0, 0);

      $filter      = new filters;

      $filter->signs($image, $this->getRandFont());

      for ($i = 0; $i < strlen($this->CaptchaString); $i++)
      {
        imagettftext($image, 25, mt_rand(-15, 15), $i * 25 + 10,
                     mt_rand(30, 70),
                     $stringcolor,
                     $this->getRandFont(),
                     $this->CaptchaString[$i]);
      
      }
      $filter->noise($image, 10);
   $filter->blur($image, 1);

      print "Creating ".$this->CaptchaString." (".$this->fname.")\n";
      imagepng($image,$this->fname);
      
      imagedestroy($image);

    } //MakeCaptcha

    function getCaptchaString ()
    {

      return $this->CaptchaString;

    } //GetCaptchaString
    
  } //class: captcha

?>
