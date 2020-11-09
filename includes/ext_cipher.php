<?php

   class Cipher {
      var $key,$sslkey,$td,$iv,$ivlen;
      var $method = "AES-256-CBC";
      
      function __construct($textkey) {
         if (!empty($textkey)) $this->init($textkey);
      }
   
      function init($textkey) {
         $this->ivlen = openssl_cipher_iv_length($this->method);
         $str = openssl_random_pseudo_bytes($this->ivlen);
         $this->iv = substr(hash('sha256',$str),0,$this->ivlen);
         $this->sslkey = hash('sha256',$textkey);
      }
      
      function encrypt($input,$nobase64=0) {
         $encrypted = openssl_encrypt($input,$this->method,$this->sslkey,0,$this->iv);
         $encrypted = $this->iv.$encrypted;
         $erg = empty($nobase64) ? base64_encode($encrypted) : $encrypted;
         return $erg;
      }
      
      function decrypt($input,$binarymode=0,$nobase64=0) {
         $input = empty($nobase64) ? base64_decode($input) : $input;
         $iv = substr($input,0,$this->ivlen);
         $input = substr($input,$this->ivlen);
         $decrypted = openssl_decrypt($input,$this->method,$this->sslkey,0,$iv);
         if (!$binarymode) $decrypted = rtrim($decrypted, "\0");
         return $decrypted;
      }   
   }

?>