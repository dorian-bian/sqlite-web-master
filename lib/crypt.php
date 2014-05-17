<?php
class Crypt{
    $cipher = 'blowfish';
    $mode = 'cfb';
    
    function __construct($cipher='blowfish', $mode='cfb'){
        $this->cipher = $cipher;
        $this->mode = $mode;
    }
    
    function encrypt($key, $plain_text){
        $td = mcrypt_module_open($this->cipher, '', $this->mode, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        
        mcrypt_generic_init($td, $key, $iv);
        $crypt_text = mcrypt_generic($td, $plain_text);
        mcrypt_generic_deinit($td);
        
        return trim(strtr(base64_encode($iv.$crypt_text),'+/=', '-_,'),',,');
    }
    
    function decrypt($key, $crypt_text){
        $crypt_text = base64_decode(strtr($crypt_text.',,','-_,','+/='));
        $td = mcrypt_module_open($this->cipher, '', $this->mode, '');
        $iv_size = mcrypt_enc_get_iv_size($td);
        $iv = substr($crypt_text, 0, $iv_size);
        $crypt_text = substr($crypt_text, $iv_size);
        
        mcrypt_generic_init($td, $key, $iv);
        $plain_text = mdecrypt_generic($td, $crypt_text);
        mcrypt_generic_deinit($td);

        return $plain_text;
    }
}
?>
