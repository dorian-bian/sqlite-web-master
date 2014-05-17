<?php
require_once P_PATH.'/app/lib/user.php';

class m_pass extends m_base {
    public function render($content=''){
        self::$title[] = 'Password Generator';
        
        $user = 'admin';
        $pass = 'admin';
        $salt = sprintf('%04X%04X', mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        
        $data = array();
        if(IS_POST){
            $c = new Client();
            $user = $c->post('user', 't=str&min=3&max=24', 'The length of user must be between 3 and 24.');
            $pass = $c->post('pass', 't=str&min=3&max=24', 'The length of pass must be between 3 and 24.');
            if($c->has_errors){
                $data['has_error'] = TRUE;
                $data['errors'] = $c->errors;
                $code = '';
            }else{
                $token = user_password($pass, $salt);
                $pass = $token['pass'];
            }
        }else{
            $token = user_password($pass, $salt);
            $pass = $token['pass'];
        }
        
        if(!IS_POST || !$c->has_errors){
            $code =
                "define('SEC_USER', '$user');\n".
                "define('SEC_SALT', '$salt');\n".
                "define('SEC_PASS', '$pass');\n";
                
            $data['code'] = htmlspecialchars($code);
        }
        
        $data['title'] = implode(' - ', array_reverse(self::$title));
        $data['user'] = $user;
        $data['pass'] = IS_POST ? $_POST['pass'] : 'admin';
        return self::merge(NULL, $data, 'pass.tpl');
    }
}
?>
