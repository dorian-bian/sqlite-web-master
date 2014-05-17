<?php
function user_check($check_token=TRUE){
    $is_valid = isset($_SESSION['sec_user']) || user_login_cookie();
    
    if(SEC_PATH && $is_valid){
        if(!isset($_SESSION['sec-token'])) $_SESSION['sec-token'] = md5(time());
        $is_valid =  !$check_token || (isset($_GET['sec-token']) && $_GET['sec-token']==$_SESSION['sec-token']);
    }
    return $is_valid;
}

function user_password($pass, $salt=NULL){
     $salt = $salt ? $salt: substr(sha1(rand()),0,8);
     return array('pass'=>substr(sha1($pass.$salt), 0, 32), 'salt'=>$salt);
}

function user_check_password($pass){
    $ticket = user_password($pass, $_SESSION['salt']);
    return $ticket['pass'] == $_SESSION['pass'];
}

function user_login($name, $pass, $persist=TRUE){
    if(SEC_USER==$name){
        $item['sec_user'] = $name;
        $item['sec_salt'] = SEC_SALT;
        $item['sec_pass'] = SEC_PASS;
        $ticket = user_password($pass, $item['sec_salt']);
        if($ticket['pass'] == $item['sec_pass']){
            $_SESSION = array_merge($_SESSION, $item);
            if($persist){
                $cookie_pass = sha1($name.sha1($item['sec_pass'].$item['sec_salt']));
                
                setcookie('sec_user', SEC_USER, SEC_LAST);
                setcookie('sec_pass', $cookie_pass, SEC_LAST);
            }
            user_check();
            return TRUE;
        }
    }
    return FALSE;
}
function user_login_cookie(){
    if(isset($_COOKIE['sec_user']) && isset($_COOKIE['sec_pass']) && 
            SEC_USER==$_COOKIE['sec_user']){
        $item['sec_pass'] = SEC_PASS;
        $item['sec_salt'] = SEC_SALT;
        
        $cookie_pass = sha1($_COOKIE['sec_user'].sha1($item['sec_pass'].$item['sec_salt']));
        if($cookie_pass === $_COOKIE['sec_pass']){
            $_SESSION = array_merge($_SESSION, $item);
            
            setcookie('sec_user', SEC_USER, SEC_LAST);
            setcookie('sec_pass', $cookie_pass, SEC_LAST);
            return TRUE;
        }
    }
    return FALSE;
}

function user_logout(){
    setcookie('sec_user', '', time() -1);
    setcookie('sec_pass', '', time() -1);
    session_destroy();
    
    $_SESSION = array();
}
?>
