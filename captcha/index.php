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
   * Software Foundation; either version 2 of the License,
   * or (at your option) any later version.
   *
   * This program is distributed in the hope that it will
   * be useful, but WITHOUT ANY WARRANTY; without even the
   * implied warranty of MERCHANTABILITY or FITNESS FOR A
   * PARTICULAR PURPOSE. See the GNU General Public License
   * for more details.

   Description:
   Testsuit for the CAPTCHA Class

  ******************************************************************/

  error_reporting(E_ALL);

  //Start the session
  session_start();

  //Load the Class
  require('./class/captcha.class.php');

  //Create a CAPTCHA
  $captcha = new captcha(4);

  //Store the String in a session
  $_SESSION['CAPTCHAString'] = $captcha->getCaptchaString();

?>
