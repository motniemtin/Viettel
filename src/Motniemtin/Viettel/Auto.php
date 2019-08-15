<?php
namespace Motniemtin\Viettel;

use Motniemtin\Auto\AutoProxy;
use Exception;

class Auto{
      public $headers;
      public $user_agent;
      public $compression;
      public $cookie_file;
      public $proxy;
      public $proxytype;
      public $getheader, $refer;
      public function __construct($cookies = TRUE, $cookie = 'temp/cookies.txt', $compression = 'gzip', $proxy = ''){
          $this->headers;
          $this->headers[0]   = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
          $this->headers[1]   = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3';
          $this->headers[2]   = 'Accept-Encoding: gzip, deflate, br';
          $this->headers[3]   = 'Connection: keep-alive';      
          $this->headers[4]   = 'Accept-Language: vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5,cs;q=0.4,de;q=0.3,es;q=0.2,id;q=0.1,ja;q=0.1,ro;q=0.1,pt;q=0.1,pl;q=0.1';
          $this->headers[5]   = 'Upgrade-Insecure-Requests: 1';
          $this->user_agent   = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.99 Safari/537.36';
          //$this->headers[]  = 'X-Requested-With: XMLHttpRequest';
          $this->compression = $compression;
          $this->proxy       = $proxy;
          $this->cookies     = $cookies;
          if ($this->cookies == TRUE)
              $this->cookie($cookie);
      }
        function file_get_contents_proxy($url){
          // Create context stream
          $context_array = array('http'=>array('proxy'=>$this->proxy,'request_fulluri'=>true));
          $context = stream_context_create($context_array);
          // Use context stream with file_get_contents
          $data = file_get_contents($url,false,$context);
          // Return data via proxy
          return $data;
      }
      public function AWSDownload($url,$filepath,$ref=""){
        $this->clearcookie();
        $process = curl_init ($url);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_BINARYTRANSFER,1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        if ($this->proxy){
          curl_setopt($process, CURLOPT_PROXY, $this->proxy);
          curl_setopt($process, CURLOPT_PROXYTYPE, $this->proxytype);
        }
        //if($ref<>'')curl_setopt($process,CURLOPT_REFERER,$ref); 
        $raw=curl_exec($process);
        curl_close ($process);
        if(file_exists($filepath)){
            unlink($filepath);
        }
        $fp = fopen($filepath,'x');
        fwrite($fp, $raw);
        fclose($fp);
      }
      public function SetAutoProxy(){
        if(file_exists(("temp/proxy.ini"))){
              $proxy=file_get_contents("temp/proxy.ini");
              $proxy=trim($proxy);
              if($proxy==""){
                $this->ErrorProxy();
                throw new Exception('Need load new proxy!');
              }
              $this->SetProxy($proxy);
        }else{
              $autoProxy=new AutoProxy();
              $proxy=$autoProxy->GetProxy();
              $this->SetProxy($proxy);
              file_put_contents("temp/proxy.ini",$proxy);
        }        
      }
      public function ErrorProxy(){
            if(file_exists(("temp/proxy.ini"))){
                  unlink(("temp/proxy.ini"));
            }
      }
      public function SetProxy($proxy){
        //$tmp=explode('//',$proxy);
        //$tmp=$tmp[1];
        $this->clearcookie();
        $this->proxy=$proxy;//$tmp;echo "~~".$tmp."~~";
        if(substr_count($proxy,'socks5')>0){
          $this->proxytype=CURLPROXY_SOCKS5;
          return;
        }
        if(substr_count($proxy,'https')>0){
          $this->proxytype=CURLPROXY_HTTPS;
          return;
        }
        if(substr_count($proxy,'socks4')>0){
          $this->proxytype=CURLPROXY_SOCKS4;
          return;
        }
        $this->proxytype=CURLPROXY_HTTP;
        return;
      }
      private function ReloadHeader(){
        //$this->headers[0]   = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
          $this->headers[0]   = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
          $this->headers[1]   = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3';
          $this->headers[2]   = 'Accept-Encoding: gzip, deflate, br';
          $this->headers[3]   = 'Connection: keep-alive';      
          $this->headers[4]   = 'Accept-Language: vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5,cs;q=0.4,de;q=0.3,es;q=0.2,id;q=0.1,ja;q=0.1,ro;q=0.1,pt;q=0.1,pl;q=0.1';
          $this->headers[5]   = 'Upgrade-Insecure-Requests: 1';
      }
      public function cookie($cookie_file){
          //echo "Curl Cookie:$cookie_file \n<br/>";
          if (file_exists($cookie_file)) {
              $this->cookie_file = $cookie_file;
          } else {
              $tmp = fopen("$cookie_file", 'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
              $this->cookie_file = $cookie_file;
              fclose($tmp);
          }
          //echo realpath($this->cookie_file);
      }
      public function ClearCookie(){
        if (file_exists($this->cookie_file)) {
          unlink($this->cookie_file);
          $this->cookie($this->cookie_file);
        }
      }
      public function Get($url,$ref='',$header=0){
          $this->ReloadHeader();
          $url=str_replace(' ','%20',$url);
          $ref=str_replace(' ','%20',$ref);
          $process = curl_init($url);
          curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
          curl_setopt($process, CURLOPT_HEADER,1);
          curl_setopt($process, CURLOPT_AUTOREFERER, 1);
          curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($process, CURLOPT_MAXREDIRS, 5);
          curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
          //curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
          curl_setopt($process, CURLOPT_ENCODING, $this->compression);
          curl_setopt($process, CURLOPT_TIMEOUT, 30);
          if($ref<>'')curl_setopt($process,CURLOPT_REFERER,$ref); 
          if ($this->proxy){
              curl_setopt($process, CURLOPT_PROXY, $this->proxy);
              curl_setopt($process, CURLOPT_PROXYTYPE, $this->proxytype);
          }
          //curl_setopt($process, CURLOPT_VERBOSE, true);
          curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
          $return = curl_exec($process);
          curl_close($process);
          return $return;
      }
      public function XmlGet($url,$ref='',$iscookie=true) {
          $url=str_replace(' ','%20',$url);
          $ref=str_replace(' ','%20',$ref);
          $this->headers[6]  = 'X-Requested-With: XMLHttpRequest'; 
          $this->headers[1]  = 'Accept: */*';
          $this->headers[0]   = 'Content-Type: application/json; charset=UTF-8';
          //$this->headers[]  = 'X-Requested-With: XMLHttpRequest';
          $url=str_replace(' ','%20',$url);
          $process = curl_init($url);
          curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
          //curl_setopt($process, CURLOPT_HEADER, 1);
          curl_setopt($process, CURLOPT_AUTOREFERER, 1);
          curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($process, CURLOPT_MAXREDIRS, 5);
          curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
          if ($this->cookies == TRUE && $iscookie)
              curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
          if ($this->cookies == TRUE && $iscookie)
              curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
          curl_setopt($process, CURLOPT_ENCODING, $this->compression);
          curl_setopt($process, CURLOPT_TIMEOUT, 30);
          if($ref<>'')curl_setopt($process,CURLOPT_REFERER,$ref); 
          if ($this->proxy){
              curl_setopt($process, CURLOPT_PROXY, $this->proxy);
              curl_setopt($process, CURLOPT_PROXYTYPE, $this->proxytype);
          }
          curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
          $return = curl_exec($process);
          curl_close($process);
          //echo $return;
          //echo $url;
          //exit();
          return $return;
      }
  
      public function Post($url, $data,$ref=''){
          $this->ReloadHeader();
          $url=str_replace(' ','%20',$url);
          $process = curl_init($url);
          $header=$this->headers;
        
          $header[]='Content-Length: '.strlen($data);
          curl_setopt($process, CURLOPT_HTTPHEADER, $header);
          //print_r($header);
          curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
          curl_setopt($process, CURLOPT_ENCODING, $this->compression);
          if($ref<>'')curl_setopt($process,CURLOPT_REFERER,$ref); 
          if ($this->proxy){
              curl_setopt($process, CURLOPT_PROXY, $this->proxy);
              curl_setopt($process, CURLOPT_PROXYTYPE, $this->proxytype);
          }
          curl_setopt($process, CURLOPT_HEADER, false);
          curl_setopt($process, CURLINFO_HEADER_OUT, true);
          curl_setopt($process, CURLOPT_POSTFIELDS, $data);
          curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
          //curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($process, CURLOPT_POST, 1);           
          curl_setopt($process, CURLOPT_TIMEOUT, 30);
          curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false); 
          //curl_setopt($process, CURLOPT_VERBOSE, 1); 
          $return = curl_exec($process);
          //print_r(curl_getinfo($process));
          curl_close($process);
          return $return;
      } 
      public function MultiUpload($url, $fields, $files, $ref=""){
          $url_data = http_build_query($fields);
          $boundary = uniqid();
          $delimiter = '-------------' . $boundary;
          $post_data = $this->build_data_files($boundary, $fields, $files);
          $url=str_replace(' ','%20',$url);
          $ref=str_replace(' ','%20',$ref);
          $this->headers[0]="Content-Type: multipart/form-data; boundary=" . $delimiter;
          $this->headers[]='Content-Length: '.strlen($post_data);
          //echo $url;
          $process = curl_init($url);
          curl_setopt($process, CURLOPT_URL,$url);
          curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
          curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
          //curl_setopt($process, CURLOPT_ENCODING, $this->compression);
          if($ref<>'')curl_setopt($process,CURLOPT_REFERER,$ref); 
          if ($this->proxy){
              curl_setopt($process, CURLOPT_PROXY, $this->proxy);
            curl_setopt($process, CURLOPT_PROXYTYPE, $this->proxytype);
          }
          curl_setopt($process, CURLOPT_POSTFIELDS, $post_data);
          curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($process, CURLOPT_POST, true);        
          curl_setopt($process, CURLOPT_CUSTOMREQUEST, "POST");
          curl_setopt($process, CURLOPT_TIMEOUT, 30);
          $return = curl_exec($process);
          curl_close($process);
          $this->headers[0]   = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
          return $return;
      }
      public function MultiPost($url, $data,$ref='',$filesize=0) {
          $url=str_replace(' ','%20',$url);
          $ref=str_replace(' ','%20',$ref);
          $this->headers[0]='Content-Type: multipart/form-data';
          //echo $url;
          $process = curl_init($url);
          curl_setopt($process, CURLOPT_URL,$url);
          curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
          curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
          //curl_setopt($process, CURLOPT_ENCODING, $this->compression);
          if($ref<>'')curl_setopt($process,CURLOPT_REFERER,$ref); 
          if ($this->proxy){
              curl_setopt($process, CURLOPT_PROXY, $this->proxy);
            curl_setopt($process, CURLOPT_PROXYTYPE, $this->proxytype);
          }
          curl_setopt($process, CURLOPT_POSTFIELDS, $data);
          if($filesize!=0){
            curl_setopt($process, CURLOPT_INFILESIZE, $filesize);
          }
          curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($process, CURLOPT_POST, 1);            
          curl_setopt($process, CURLOPT_TIMEOUT, 30);
          $return = curl_exec($process);
          curl_close($process);
          $this->headers[0]   = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
          return $return;
      } 
      public function XmlPost($url, $data,$ref=''){
          $url=str_replace(' ','%20',$url);
          $ref=str_replace(' ','%20',$ref);
          $this->headers[]  = 'X-Requested-With: XMLHttpRequest';
          $this->headers[1]  = 'Accept: application/json, text/javascript, */*; q=0.01';
          $this->headers[0]   = 'Content-Type: application/json; charset=UTF-8';
          $process = curl_init($url);
          curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
          //curl_setopt($process, CURLOPT_HEADER, 1);
          curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
          curl_setopt($process, CURLOPT_ENCODING, $this->compression);
          curl_setopt($process, CURLOPT_TIMEOUT, 30);
          if ($this->proxy){
              curl_setopt($process, CURLOPT_PROXY, $this->proxy);
            curl_setopt($process, CURLOPT_PROXYTYPE, $this->proxytype);
          }
          curl_setopt($process, CURLOPT_POSTFIELDS, $data);
          curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
          if($ref<>'')curl_setopt($process,CURLOPT_REFERER,$ref); 
          curl_setopt($process, CURLOPT_POST, 1);
          $return = curl_exec($process);
          curl_close($process);
          return $return;
      }
      public function GetImage($fileurl, $local_path){
          $url=str_replace(' ','%20',$url);
          $ref=str_replace(' ','%20',$ref);
          $filename = basename($fileurl);
          $filename = "yesmanga_" . $filename;
          $out      = fopen($local_path . "/" . $filename, 'wb');
          if ($out == FALSE) {
              exit(".mtcerror.=> Loi khong the ghi file $fileurl to $local_path/$filename");
          }
          $process = curl_init($fileurl);
          curl_setopt_array($process, array(
              CURLOPT_RETURNTRANSFER => true, //Causes curl_exec() to return the response
              CURLOPT_HEADER => false, //Suppress headers from returning in curl_exec()
              CURLOPT_HEADERFUNCTION => array(
                  $this,
                  'readHeader'
              )
          ));
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
          curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
          curl_setopt($process, CURLOPT_HEADER, 0);
          curl_setopt($process, CURLOPT_FILE, $out);
          curl_setopt($process, CURLOPT_URL, $fileurl);
          curl_setopt($process, CURLOPT_TIMEOUT, 0);
          curl_setopt($process, CURLOPT_AUTOREFERER, true);
          $return = curl_exec($process);
          if (curl_errno($process)) {
              echo 'Curl error: ' . curl_error($process) . "</br>";
          }
          curl_close($process);
          fclose($out);
          return $local_path . "/" . $filename;
      }
      public function ReadHeader($process, $header_line){
          $this->getheader .= $header_line;
          return strlen($header_line);
      }
      public function Error($error) {
          echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>";
          die;
      }
      public function DownloadFile($fileurl, $filepath){
          $this->ReloadHeader();
          $fileurl=trim($fileurl);
          $fileurl=str_replace(' ','%20',$fileurl);
          $out         = fopen($filepath, 'w+');
          $process     = curl_init($fileurl);
          curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
          curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($process, CURLOPT_MAXREDIRS, 5);
          curl_setopt($process, CURLOPT_HEADER, 1);
          curl_setopt($process, CURLOPT_TIMEOUT, 60);
          curl_setopt($process, CURLOPT_FILE, $out);
          curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
          if ($this->cookies == TRUE)
              curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
          curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
          if ($this->proxy){
              curl_setopt($process, CURLOPT_PROXY, $this->proxy);
            curl_setopt($process, CURLOPT_PROXYTYPE, $this->proxytype);
          }
          $return = curl_exec($process);
          if (curl_errno($process)) {
              echo 'Curl error: ' . curl_error($process) . "</br>";exit();
          }
          curl_close($process);
          return realpath($filepath);
      }
      public function DownloadDistantFile($url, $dest){
        $options = array(
          CURLOPT_FILE => is_resource($dest) ? $dest : fopen($dest, 'w'),
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_URL => $url,
          CURLOPT_FAILONERROR => true, // HTTP code > 400 will throw curl error
        );
        $ch = curl_init();
        curl_setopt_array($process, $options);
        $return = curl_exec($process);
        if ($return === false)
        {
          return false;
        }
        else
        {
          return true;
        }
      }
      public function getRedirect($url,$ref=""){
          $url=str_replace(' ','%20',$url);
          $ref=str_replace(' ','%20',$ref);
          $redirect_url = null;
          $url_parts = @parse_url($url);
          if (!$url_parts)
              return false;
          if (!isset($url_parts['host']))
              return false; //can't process relative URLs
          if (!isset($url_parts['path']))
              $url_parts['path'] = '/';
          $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int) $url_parts['port'] : 80), $errno, $errstr, 30);
          if (!$sock)
              return false;
          $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?' . $url_parts['query'] : '') . " HTTP/1.1\r\n";
          $request .= 'Host: ' . $url_parts['host'] . "\r\n";
          $request .= "Connection: Close\r\n\r\n";
          fwrite($sock, $request);
          $response = '';
          while (!feof($sock))
              $response .= fread($sock, 8192);
          fclose($sock);
          if (preg_match('/^Location: (.+?)$/m', $response, $matches)) {
              if (substr($matches[1], 0, 1) == "/")
                  return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
              else
                  return trim($matches[1]);
          } else {
              return false;
          }
      }
      public function getRemoteSize($url){
          $ch = curl_init($url);
          curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
          curl_setopt($process, CURLOPT_HEADER, TRUE);
          curl_setopt($process, CURLOPT_NOBODY, TRUE);
          $data = curl_exec($process);
          $size = curl_getinfo($process, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
          curl_close($process);
          return $size;
      }
      public function STagMulti($inputtag, $htmlsourse){
          $inputtagexplode = explode(" ", $inputtag);
          $starttag        = $inputtagexplode[0];
          $endtag          = $starttag[0] . "/" . substr($starttag, 1) . ">";
          $result      = NULL;
          //------ 
          $htmlexplode = explode($inputtag, $htmlsourse);
          for ($i = 1; $i < count($htmlexplode); $i++) {
              //get full tags                    
              $newtext     = $inputtag . $htmlexplode[$i];
              $tempexplode = explode(">", $newtext);
              $fulltag     = $tempexplode[0] . ">";
              $savehtml    = $newtext;
              $continue    = true;
              $posed       = 0;
              $tagcount    = 0;
              $firstid     = 0;
              $j           = 0;
              while ($continue == true) {
                  $posstart = strpos($newtext, $starttag);
                  $posend   = strpos($newtext, $endtag);
                  if ($posend === false) {
                      $continue = false;
                  } else {
                      if ($posstart === false) {
                          //tim thay end
                          $newtext = substr($newtext, $posend + strlen($endtag));
                          $posed += $posend + strlen($endtag);
                          $tagcount -= 1;
                      } else {
                          //tim thay start va end
                          if ($posstart < $posend) {
                              //start tim thay truoc
                              $newtext = substr($newtext, $posstart + strlen($starttag));
                              $posed += $posstart + strlen($starttag);
                              if ($tagcount == 0) {
                                  $firstid = $posed;
                              }
                              $tagcount += 1;
                          } else {
                              //end tim thay truoc
                              $newtext = substr($newtext, $posend + strlen($endtag));
                              $posed += $posend + strlen($endtag);
                              $tagcount -= 1;
                          }
                      }
                  }
                  if ($tagcount == 0) {
                      $continue = false;
                      $kq       = substr($savehtml, $firstid + strlen($fulltag) - strlen($starttag), $posed - $firstid - strlen($endtag) - strlen($fulltag) + strlen($starttag));
                      $j += 1;
                      $result[$i] = $kq;
                  }
              }
          }
          if(!is_array($result)){
              return array();
          }
          $result = array_filter($result);
          return array_values($result);
      }
      public function STagOne($inputtag, $htmlsourse){
          $outdata=$this->STagMulti($inputtag, $htmlsourse);
          if(count($outdata)>0){
            return $outdata[0];
          }
          return "";
      }
      public function SCodeMulti($first_str_search,$last_str_search,$string_to_search){            
          $ResultArray=array();               
          $string_to_search=strstr($string_to_search,$first_str_search);
          $resultStr1=explode($first_str_search,$string_to_search);
          for ($i = 0; $i < count($resultStr1); $i++)
          {
              //echo $resultStr1[$i]."<br>!";
              $resultStr2=explode($last_str_search,$resultStr1[$i]);  
              if (count($resultStr2)>0)
              {
                  //echo $resultStr2[0]."</br>";
                  $ResultArray[$i]=$resultStr2[0];
              }
          }
          if(!is_array($ResultArray)){
              return array();
          }
          if(is_array($ResultArray)){
              $ResultArray = array_filter($ResultArray);
              $ResultArray = array_values($ResultArray);
              $ResultArray = array_merge($ResultArray);
              return $ResultArray;
          }else{
              return array();
          }
      }   
      public function SCodeMultiEntities($first_str_search,$last_str_search,$string_to_search){        
              $first_str_search=htmlentities($first_str_search);
              $last_str_search=htmlentities($last_str_search);        
              $string_to_search=htmlentities($string_to_search);    
          $ResultArray=array();               
          $string_to_search=strstr($string_to_search,$first_str_search);
          $resultStr1=explode($first_str_search,$string_to_search);
          for ($i = 0; $i < count($resultStr1); $i++)
          {
              //echo $resultStr1[$i]."<br>!";
              $resultStr2=explode($last_str_search,$resultStr1[$i]);  
              if (count($resultStr2)>0)
              {
                  //echo $resultStr2[0]."</br>";
                  $ResultArray[$i]=html_entity_decode($resultStr2[0]);
              }
          }
          $ResultArray=array_filter($ResultArray);                
          return array_values($ResultArray);
          }    
      public function SCodeOne($first_str_search,$last_str_search,$string_to_search){
          if($string_to_search =='' || $first_str_search =='' || $last_str_search=='')return '';
          if(substr_count($string_to_search, $first_str_search)<=0)return '';
          if(substr_count($string_to_search, $last_str_search)<=0)return '';
          $ResultArray=array();               
          $string_to_search=strstr($string_to_search,$first_str_search);
          $resultStr1=explode($first_str_search,$string_to_search);
          for ($i = 0; $i < count($resultStr1); $i++)
          {
              //echo $resultStr1[$i]."<br>!";
              $resultStr2=explode($last_str_search,$resultStr1[$i]);  
              if (count($resultStr2)>0)
              {
                  //echo $resultStr2[0]."</br>";
                  $ResultArray[$i]=$resultStr2[0];
              }
          }
          if(isset($ResultArray[1])){
              return $ResultArray[1];
          }else{
              return '';
          }            
      }           
      public function BuildPostData($boundary, $fields, $files){
        $data = '';
        $eol = "\r\n";
        $delimiter = '-------------' . $boundary;
        foreach ($fields as $name => $content) {
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
                . $content . $eol;
        }
        if($files!=null){
          foreach ($files as $name => $content) {
              $data .= "--" . $delimiter . $eol
                  . 'Content-Disposition: form-data; name="content"; filename="' . $name . '"' . $eol
                  //. 'Content-Type: image/png'.$eol
                  . 'Content-Transfer-Encoding: binary'.$eol
                  ;
              $data .= $eol;
              $data .= $content . $eol;
          }
          $data .= "--" . $delimiter . "--".$eol;
        } 
        return $data;
    }
}
