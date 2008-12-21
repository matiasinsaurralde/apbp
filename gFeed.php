<?php
/*
 *    Cesar D. Rodas' gFeed
 *    
 *    This class allows you to easy read and write RSS files.
 *    @ before $ is to skip warning messages.
 *    This class is Public domain!, you are legally free to do with this
 *    what you want, with the only condition that you *must* report bugs
 *    if you find.
 */

define('READ',0);
define('APPEND',1);
define('TRUNCATE',2);
define('UNKNOWN',3);
define('none',0);
define('item',1);
define('image',2);
define('channel',3);

$gRSS_errors = array( 
    'return "Cannot open $path for $mode. Only can open with the mode r";',
    'return "Unknown open mode $mode";',
    'return "Could not open $file (mode:$mode)";',
    'return "Error while read $file (mode:$mode)";',
    'return "Error while writing $file";',
    'return "$file doesn\'t have a valid RSS2 file format";'
);



/*
 *    This class read and write and RSS
 *    to a file.
 */
class gRSS {
    var $rssArr; /* RSS parsed @Array */
    var $flag;
    var $size;
    var $currentTag;

    function iniParse() {
        unset($this->rssArr);
        /* Basic @ structure*/
        $this->rssArray['channel']=array();
        $this->rssArr['channel']['encoding'] = 'UTF-8';
        $this->rssArray['item']=array();
    }

    /*
    **    This function transform an RSS2 text into 
    **    an Array.
    */    
    function parse($txt) {
        $this->size = 0; /**/
        $this->iniParse();
        $this->pzXML = xml_parser_create();
        xml_parser_set_option($this->pzXML,XML_OPTION_CASE_FOLDING,true);
        xml_set_element_handler($this->pzXML, array(&$this,"startTag"),array(&$this,"endTag") );
        xml_set_character_data_handler($this->pzXML,array(&$this,"dataHandling") );
        xml_parse($this->pzXML,$txt, true);
        xml_parser_free($this->pzXML);
		$this->cleanXML($this->rssArr);
		$tmp = &$this->rssArr;
		$this->size++; /* Increment to one the size, because the first one is the RSS information */
        return isset($tmp['channel']) && isset($tmp['item']) && is_array($tmp['item']);
    }
    
    /*
    **    This function transform an Array into a RSS2
    **    file.
    */
    function unParse() {
        $tmp = $this->rssArr['channel'];
        $this->safeXML($tmp);
        $r[] = '<?xml version="1.0" encoding="'.(isset($tmp['encoding']) ? $tmp['encoding'] : 'UTF-8').'"?>';
        $r[] = '<!-- Powered by Cesar D. Rodas\' gFeed (http://cesars.users.phpclasses.org/gfeed/) -->';
        $r[] = '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/">';
        $r[] = "\t<channel>";
        $r[] = "\t\t<title>".$tmp['title']."</title>";
        $r[] = "\t\t<link>".$tmp['link']."</link>";
        $r[] = "\t\t<generator>".$tmp['generator']."</generator>";
        $r[] = "\t\t<pubdate>".$tmp['pubdate']."</pubdate>";
        $r[] = "\t\t<description>".$tmp['description']."</description>";
        
        foreach($this->rssArr['item'] as $k => $y) {
            $this->safeXML($y);
            $r[] = "\t\t<item>";
            $r[] = "\t\t\t<title>".$y['title']."</title>";
            $r[] = "\t\t\t<author>".$y['author']."</author>";
            $r[] = "\t\t\t<pubdate>".$y['pubdate']."</pubdate>";
            $r[] = "\t\t\t<link>".$y['link']."</link>";
            $r[] = "\t\t\t<guid>".$y['link']."</guid>";
            $r[] = "\t\t\t<description><![CDATA[".$y['description']."]]></description>";    
            $r[] = "\t\t</item>";
        }
        $r[] = "\t</channel>";
        $r[] = "</rss>";
        
        return implode("\r\n",$r);     
    }
    /**    
    **    Auxiliars functinon used for RSS 2 Array
    */
    function safeXML(&$arr) {
        foreach($arr as  $k => $y) {
            if (is_array($k)) $this->safeXML($y);
            else $arr[$k] = /*htmlentities*/($y);
        }
    }
    function cleanXML(&$array) {
        if (!is_array($array)) return false;
        foreach ($array as $k => $v) {
            if (trim($k)=='') unset($array[$k]);
            
            else if (is_array($v)) $this->cleanXML($array[$k]);
            else { 
                $v = trim($v);
                /*
                **    Show HTML tags if there is.
                */
                $v = preg_replace("/<(.*?)>/e","'<'.stripslashes(trim(strtoupper('\\1'))).'>'", $v);
                $array[$k] = str_replace(' & ','&', $v);
            
            }
        }
        return true;
        
    }
    function startTag(&$parser,&$name,&$attribs){ 
        if($name){    
            switch(strtolower($name)){
                case "rss":
                    if ( isset($attribs['ENCODING']) ){
                        $this->rssArr['channel']['encoding'] = $attribs['ENCODING'];
                        break;
                    }
                    break;
                case "channel":
                    $this->flag=channel;
                    break;
                case "image":
                    $this->flag=image;
                    break;
                case "entry":
                case "item":
                    $this->flag=item;
                    $this->size++;

                    break;    
                case "link":
                    if ( isset($attribs['REL']) && $attribs['REL'] == 'alternate' && $attribs['TYPE'] == 'text/html'){
                        $this->rssArr["item"][$this->size]['link']=$attribs['HREF'];
                        break;
                    }
                default:
                    $this->currentTag=trim(strtolower($name));
                    break;
            }
        }
    }
    function endTag(&$parser,&$name){ 
        $this->currentTag="";
    }
    function dataHandling(&$parser,&$data){ 
        switch ($this->flag) {
            case channel:
                if(isset($this->rssArr["channel"][$this->currentTag])){
                    $this->rssArr["channel"][$this->currentTag].=$data;
                 }else{
                    $this->rssArr["channel"][$this->currentTag]=$data;
                }
                break;
            case item:
                if(isset($this->rssArr["item"][$this->size][$this->currentTag])){
                    $this->rssArr["item"][$this->size][$this->currentTag].=' '.$data;           
                }else{
                    $this->rssArr["item"][$this->size][$this->currentTag]=$data;
                }
                break;
            case image:
                if(isset($this->rssArr["image"][$this->currentTag])){
                    $this->rssArr["image"][$this->currentTag].=$data;
                }else{
                    $this->rssArr["image"][$this->currentTag]=$data;
                }
                break;
            }
    }


}

/**
**    Extendes gRSS and implement a PHP Stream wreaper for 
**    made easy and transparent works with RSS 2.
*/

/*
 *    RSS is a wide used format, and of course there is some implementation 
 *    variants as for example how some sites suchs as WordPress, Blogger 
 *    and others give differents names for the same thing.
 *    Here is an array for those common differeces.
 */
$rssTagsName = array (
    'gTitle' => array('title'),
    'gAuthor' => array('author','dc:creator','name'),
    'gPubDate' => array('published','pubdate'),
    'gLink' => array('link','guid'),
    'gContent' => array('content','description')
);

class gRSStreamWreaper extends gRSS {
	var $mode; /* Opened flags @String*/
    var $path; /* Current file path @String */
    var $position; /* Current position */
    var $errStr; /* @Array of error messages */
    var $classContructed; /* @Boolean that show is the class was contructed. */

    var $fp; /* This is the File Pointer */    
    
    /*
    **    Constructor
    **
    */
    function gRSStreamWreaper() {
        global $gRSS_errors;
        $this->classContructed = true;
        $this->errStr = &$gRSS_errors;
    }
    
    /*
    **    If you use gFeed with gHttp this enables the RSS auto discover
    **    this mean that you can give as a parameter a web-site address and this function
    **    will search the RSS2 file. If there is not exist it will exit.
    */
    function rssAutoDiscover() {
        global $gHeaders;
        if ( !isset($gHeaders) || !is_array($gHeaders)) return; /* gHttp is not used */
        
        /*
        **    This archive is a text/xml file, let pass it
        **    to analize later!
        */
        if ( strpos(strtolower($gHeaders['content-type']),'text/xml') !== false) 
            return true;
        
        /*
        **    The given page is not a RSS, download the hole page
        **    and search there the RSS link
        */
        $response='';
        while ($c = fread($this->fp, 1024))
            $response .= $c;
        
        /* search the RSS */
        $return = false;
        $pattern = "/<link(.*?)>/i";
        preg_match_all($pattern,$response,$result);
        if (! is_array($result) ) return false;
        foreach($result[0] as $info) {                
            preg_match("/type\s*=\s*['|\"|\s*](.*?)['|\"|>]/i",$info,$type);
            preg_match("/rel\s*=\s*['|\"|\s*](.*?)['|\"|>]/i",$info,$rel);
            /*
            **    "rel" property must be 'alternate' if exists
            **    "type" must be 'application/rss+xml'         
            */
            if (
                (!isset($rel[1]) || strtolower($rel[1]) == 'alternate') && 
                strtolower($type[1]) == 'application/rss+xml'
            ) { /* The RSS address is found! */
                preg_match("/href\s*=\s*['|\"|\s*](.*?)['|\"|>]/i",$info,$href);
                fclose($this->fp); /* close this file */
                $this->fp = fopen($href[1], 'r'); /* open the RSS file */
                $return = $this->fp === false ? false : true; /* Could open? */
                $return =  true;
                break; /* why search more? */
            } 
        }
        
        return $return;
        
    }
    
    function getOpenedMode() {
        switch ( strtolower($this->mode) ) {
            case "r":
            case "rb":
                $OpenFlags = READ;
                break;
            case "r+":
            case "a":
            case "ab":
            case "r+b":
                $OpenFlags = APPEND;
                break;
            case "w":
                $OpenFlags = TRUNCATE;
                break;
            default: 
                $OpenFlags = UNKNOWN;
                break;
        }    
        return $OpenFlags;
    }
    


    function OpenFile() {
        $file = &$this->path;    
        $mode = $this->getOpenedMode();
        $this->fp = fopen($file, $mode==TRUNCATE ? 'w' : 'r' );
        /*
        **
        **
        */
        if ($this->fp === false) {    
            if ($mode == APPEND && ( $this->fp = fopen($file, 'w') === false) ) 
                return false;        
        }    
            
        /*
         *    If the file is not for TRUNCATE load the content 
         *    or create an Empty Array structure.
         */
        if ($mode != TRUNCATE) {
            /* load content and parse. */
#if ( $this->rssAutoDiscover() === false) { /* Only works with gHttp */
#                trigger_error(eval($this->errStr[5]), E_USER_WARNING);
#                return false;
#            }
            $this->content = '';
            while ( $c=fread($this->fp,1024)) {
                $this->content.=$c;
            }     
            $this->content =  strstr($this->content,'<?xml');
            if ($this->content === false || !$this->parse($this->content)) {
                trigger_error(eval($this->errStr[5]), E_USER_WARNING);
                return false;
            }
            
            
            if ($mode == APPEND) {
                $this->position = $this->size+1;
            }
            
        } else {
            $this->iniParse(); /* Create the empty array structure*/
        }
        
        fclose($this->fp);
        return true;
    }

    /*
     *     Try to see if exist the index $varName into $item
     */
    function getRssVar($varName, $item) {
        global $rssTagsName;

        if (!is_array($item)) return false;
        if ( isset($rssTagsName[$varName]) ) {
            /* If $varName is an array */
            foreach($rssTagsName[$varName] as $v) 
                if ( isset($item[$v]) ) return $item[$v];
            return false;
        }
        return isset($item[$varName]) ? $item[$varName] : false;
    }
    
    /*
    **    Wrapper interface
    **
    */
    function stream_open($path, $mode, $options, &$opened_path)
    {
        if (!$this->classContructed) 
            $this->gRSStreamWreaper(); /* Calling the Class constructor. */

        /*
         *    Cleaning the rss:// for leave the RSS location.
         *    Example:
         *        rss://http://www.cesarodas.com/feed/ 
         *        http://www.cesarodas.com/feed/
         */
        $path = substr($path, strlen('rss://'));

        $this->path = $path; /* Saving the path.  */
        $this->position = 0; /* Reseting position. */
        $this->mode = $mode ; /* Saving the open mode.  */

        /*
         *    Parse the URL 
         */
        $url = parse_url($path);
        /*
         *    Get the open $mode.
         */
        if ($fmode=$this->getOpenedMode() == UNKNOWN) {
            trigger_error(eval($this->errStr[1]), E_USER_WARNING);
            return false;
        }        
        
        /*
         *    If the RSS is opened for write and it is not a local file, this
         *    is an error.
         */
        if ( 
            $fmode != READ && 
            isset($url['scheme']) && 
            $url['scheme']!='file') 
        {
            trigger_error(eval($this->errStr[0]), E_USER_WARNING);
            return false;
        }
        return $this->OpenFile();
    }

    function stream_read($count)
    {
        global $title;
        global $author;
        global $date;
        global $description;
        global $link;
        
        if ($this->position >= $this->size) return false; 
        
        if ($this->position == 0) {
            global $language;
            global $generator;
            global $encoding;
            
            $tmp = & $this->rssArr['channel'];
            
            $title =  $this->getRssVar('gTitle', $tmp);
            $link = $this->getRssVar('gLink', $tmp);
            $description = $this->getRssVar('gContent', $tmp);
            $date = $this->getRssVar('gPubDate', $tmp);
            $generator = $this->getRssVar('generator', $tmp);
            $language = $this->getRssVar('language', $tmp);
            $encoding = $this->getRssVar('encoding', $tmp);
            
        } else {
            $tmp = & $this->rssArr['item'][ $this->position ];
    
            $title = $this->getRssVar('gTitle', $tmp);
            $author = $this->getRssVar('gAuthor', $tmp);
            $date = $this->getRssVar('gPubDate', $tmp);
            $link =  $this->getRssVar('gLink', $tmp);
            $description = $this->getRssVar('gContent', $tmp);
        }        
        $this->position++;
        
        return true;
    }
	
	function stream_stat() {
		return array(
			'size' => $this->size-1,
			'blksize' => 4096,
			'blocks' => 8
			
		);
	}
	
	function url_stat ($path, $flags ){
		$f = $this->stream_open($path,'r', $flags, $f);
		if ($f === false) return false;
		return $this->stream_stat();
	}
	
    function stream_write($data)
    {
        global $title;
        global $author;
        global $date;
        global $description;
        global $link;
        
        
        if ($this->position == 0) {
            global $language;
            global $generator;
            global $encoding;
            
            $tmp = & $this->rssArr['channel'];
                
            $tmp['title']=$title;
            $tmp['link']=$link;
            $tmp['description']=$description;
            $tmp['pubdate']=$date;
            $tmp['generator'] = "gFeed v1.0 (http://cesars.users.phpclasses.org/gfeed)";
            $tmp['language']=$language;
            $tmp['encoding']= isset($encoding) ? $encoding : 'UTF-8';
        } else {
            $tmp = &$this->rssArr['item'][ $this->position ];
            $tmp = array(); /* reseting values */
            $tmp['title']=$title ;
            $tmp['author'] = $author;
            $tmp['pubdate']  = $date;
            $tmp['link'] = $link;
            $tmp['description'] = $description;
        }        

        $this->position++;
        return true;
    }

    function stream_tell()
    {
        return $this->position;
    }

    function stream_eof()
    {
        return $this->position >= $this->size;
    }

    function stream_seek($offset, $whence)
    {
            switch ($whence) {
            	case SEEK_SET:
                        if ($offset < $this->size && $offset >= 0) {
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
               		if ($this->size + $offset >= 0) {
                    	$this->position = $this->size + $offset;
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
        if ($this->getOpenedMode() == READ) return true;
        $file = &$this->path;    
        $mode = &$this->mode;
        
        $xml = $this->unParse();
        
        $this->fp = fopen($file, 'w');
        if ($this->fp === false) {    
            trigger_error(eval($this->errStr[4]), E_USER_WARNING);
            return false;        
        }        
        fwrite($this->fp, $xml);
        fclose($this->fp);    
        return true;
    }
    
}

stream_wrapper_register("rss", "gRSStreamWreaper") or die("Failed to register protocol");



?>
