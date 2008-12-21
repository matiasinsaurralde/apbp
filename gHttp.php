<?php
include(dirname(__FILE__)."/http.php");

/*
**    This class provides a 'http' replacement wrapper for php5 and for olders
**    a 'ghttp' wrapper. This is useful for open and get pages with a fopen because    
**    many hosting deny it in the php.ini for security reasons.
**    The ghttp stream wreaper are created by defualt. And for php5 the http is replaced,
**    this is only because the php5 has a function for unregister wrappers and olders
**    versions do not :'(
**
**    This code is free, I disclaims the copyright over this class, 
**    the only think that I ask you is that is you find a bug or something for make 
**    this class work better plz tellme in the forums of phpclasses. 
**
**    Cesar D. Rodas
*/
class gHttp  {
    var $position;
    var $url;
	
    var $http;
    
    var $isEOF;
    var $isCompressedPage;
    var $content;
	
	
	
	/*
	**	gHttp gather cache, it is only for make a better performance
	**	when you download many pages, and usually the same page.
	*/
	var $isCached;
	function doFileCache() {
		global $gHttpCache;
		global $gHeaders;
		if ($this->isCached) return true;
		if ($gHttpCache && is_array($gHttpCache)) {
			$path = $gHttpCache['folder'].'/'.md5($this->url);
			$f = fopen($path,'w');
				fwrite($f, $this->content);
			fclose($f);
			$f = fopen("${path}.header",'w');
				fwrite($f, serialize($gHeaders));
			fclose($f);
		}
		return true;
	}
	
	function isFileValid($path, $timeout) {
		if (!is_file($path)) return false;
	 	$info = stat($path);
        if ($timeout > 0 && $info['mtime'] + $timeout < time())
			return false;
		return true;
	}
	 
	function isFoundInCache() {
		global $gHttpCache;
		global $gHeaders;
		if ($gHttpCache && is_array($gHttpCache)) {
			@clearstatcache();
			$path = $gHttpCache['folder'].'/'.md5($this->url);
			
			if ( 
				!$this->isFileValid($path, $gHttpCache['timeout']) or
				!$this->isFileValid("${path}.header", $gHttpCache['timeout']) 
			)
				return false;
				
			$f = fopen($path,'rb');
				$this->content = fread($f, filesize($path) );
			fclose($f);
			
			$f = fopen("${path}.header",'rb');
				$gHeaders = unserialize( fread($f, filesize("${path}.header"))  );
			fclose($f);
			return true;
		}
		return false;
	}
	/* end of cache */
	
    function configProxy(&$args) {
        global $Proxy;
        $proxy = &$Proxy;
        if ($proxy && is_array($proxy)) {
            $args["ProxyHostName"]=$proxy['host'];
            $args["ProxyHostPort"]=$proxy['port'];
            $args["ProxyUser"]=$proxy['user'];
            $args["ProxyPassword"]=$proxy['pass'];
            $args["ProxyRealm"] = isset($proxy['realm']) ? $proxy['realm'] : "proxyrealm" ;
            $this->http->proxy_authentication_mechanism= isset($proxy['mech']) ? $proxy['mech']:  ""; 
         }
    }

    function stream_open($path, $mode, $options, &$opened_path) 
    {
        global $gHttpErr;
        global $gHeaders;
		
		$method = 'GET';
        /*
         *    change an ghttp://www.cesarodas.com URL for
         *    http://www.cesarodas.com/
         */
        if (strtolower($path[0]) == 'g') $path = substr($path,1);
				
		if (strtolower($path[0]) == 'p') {
			global $gPost;
			if ( !isset($gPost) || !is_array($gPost)) {
				trigger_error('Missing $gPost array.', E_USER_WARNING);
				return false;
			}
			
			$path = substr($path,1);
			$method = 'POST';
		}

		$this->url= $path;
		$this->isEOF = false;
		$this->isCached =  $this->isFoundInCache(); 
		
        if ( $this->isCached ) return true;
		
		$this->http=new http_class;
        $this->http->timeout=0;
        $this->http->data_timeout=0;
        $this->http->debug=0;
        $this->http->html_debug=1;
        $this->http->user_agent="gHttp (+http://cesars.users.phpclasses.org/ghttp)";
        $this->http->follow_redirect=1;
        $this->http->redirection_limit=5;
        $this->http->exclude_address="";
        $this->http->request_method= $method;
        
        /*
         * Http is amazing!, it has its own URL parse
         * so, I do not worry about this.
         */
        $this->http->GetRequestArguments($path,$arguments);
		if ($method == 'POST')
			$arguments['PostValues'] = &$gPost;
        /* Config a proxy */
        $this->configProxy($arguments);

        /* Ask for compressed page, for keep bandwith */
        #$arguments['Headers']['Accept-encoding'] = "gzip";
        /* Open connection */
        $gHttpErr = $this->http->Open($arguments);
        if ($gHttpErr != "") return false;
        /* Send Request */
        $this->http->SendRequest($arguments);
        if ($gHttpErr != "") return false;
        /* Get Header */
        $gHttpErr = $this->http->ReadReplyHeaders($gHeaders);
        if ($gHttpErr != "") return false;
        /* */
        if ($this->http->response_status != 200) { 
            $gHttpErr = "Page status: ".$this->http->response_status;
            $this->http->Close();
            return false;
        }
		
        /* */
        
        if (isset($gHeaders['content-encoding']) && 
            strtolower($gHeaders['content-encoding']) == 'gzip') 
        {
			$buffer = '';
            while (!$this->isEOF &&  $r = $this->stream_read(1024)  )
                $buffer .= $r;
            
            $this->content = substr($buffer, 10); 
            $this->content = gzinflate($this->content);
            $this->position = 0;
                        
            $this->isCompressedPage = true;    
			$this->doFileCache();
        }
        
        return true;
    }

    function stream_read($count) 
    {
        global $gHttpErr;
		
        if ( !$this->isCompressedPage && !$this->isCached) {
            $gHttpErr=$this->http->ReadReplyBody($ret,$count);
            if($gHttpErr!="" && strlen($ret)==0) $this->isEOF = true;
            $this->content .= $ret; /* buffer this! */
        } else {
            $ret = substr($this->content, $this->position, $count);
        }

        $this->position += strlen($ret);
        return strlen($gHttpErr)>0 ? false : $ret;
    }

    function stream_write($data) 
    {
        return false; /* you cannot write ;-) */
    }

    function stream_tell() 
    {
        return $this->position; /* */
    }

    function stream_eof() 
    {
        return $this->isEOF;
    }
    /*
     *    What usually people do is to open, read, and close a connection,    
     *    i dont know if fseek is supported, it is supported in this class
     *    but with a single caracterist... it only could rewind or set a position
     *    lower than the actual position. I can change this, but for me it is not
     *    useful bacause this class will need to download the hole page and buffer
     *    it... what do you think of it? saddor [(a)] gmail [dot ]com answear me plz!
     */
    function stream_seek($offset, $whence) 
    {
        $max = &$this->position;
        switch ($whence) {
            case SEEK_SET:
                if ($offset < $max && $offset >= 0) {
                    $this->position = $offset;
                    return true;
                } else {
                    return false;
                }
                break;
                   
            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->position += $offset;
                    return true;
                } else {
                    return false;
                }
                break;
                   
            case SEEK_END:
               if ($max + $offset >= 0 && $offset >= 0) {
                    $this->position = $max + $offset;
                    return true;
               } else {
                    return false;
               }
               break;
                   
            default:
                return false;
        }
    }

    function stream_flush() {
		$this->doFileCache();
		if (!$this->isCached) $this->http->Close(); /* close the http-class */
    }
}

/* ghttp(s)*/
stream_wrapper_register("ghttp", "gHttp") or die("Failed to register protocol");
stream_wrapper_register("ghttps", "gHttp") or die("Failed to register protocol");
/* post */
stream_wrapper_register("phttp", "gHttp") or die("Failed to register protocol");
stream_wrapper_register("phttps", "gHttp") or die("Failed to register protocol");
/* replacing standar http(s) wrapper */
if ( is_callable('stream_wrapper_unregister') ) {
    stream_wrapper_unregister('http');
	stream_wrapper_unregister('https');
    stream_wrapper_register("http", "gHttp");
	stream_wrapper_register("http2", "gHttp");
}

?>
