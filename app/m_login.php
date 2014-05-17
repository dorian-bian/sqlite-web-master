<?php
require_once P_PATH.'/app/lib/user.php';

class m_login extends m_base {
    public function render($content=''){
        self::$title[] = 'Login';
        
        $data = array();
        $is_success =FALSE;
        if(IS_POST){
            $c = new Client();
            $name = $c->post('user', 't=str');
            $pass = $c->post('pass', 't=str');
            
            $is_success =FALSE;
            
            if($c->has_errors){
                $data['has_error'] = TRUE;
                $data['errors'] = $c->errors;
                
            }else{
                if(!user_login($name, $pass, isset($_POST['remember']))){
                    $c->set_error('pass', 'The password is wrong .');
                }
                if($c->has_errors){
                    $data['has_error'] = TRUE;
                    $data['errors'] = $c->errors;
                }else{
                    $is_success = TRUE;
                }
            }
        }else{
            if(isset($_GET['out'])){
                user_logout();
            }else{
                if(user_check(FALSE)){
                    $is_success = TRUE;
                    Template::$site['args'] += array('sec-token' => $_SESSION['sec-token']);
                }
            }
        }
        
        if($is_success){
            $q = array();
            foreach($_GET as $key=>$val){
                if(strpos($key, 'rel-')!==FALSE){
                    $q[substr($key, 4)] = $val;
                }
            }
            $q['sec-token'] = $_SESSION['sec-token'];
            $t = $_SERVER['PHP_SELF'].'?'.http_build_query($q);
            header('Location:'.$t);
        }else{
            return self::merge(NULL, $data, 'login.tpl');
        }
    }
}
?>
