<?php
/***********************************************************************
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(40) collate utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL default '0',
  `ctime` int(11) NOT NULL,
  `client_ip` varchar(20) collate utf8_bin NOT NULL,
  `browser` varchar(150) collate utf8_bin NOT NULL,
  `data` text collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
************************************************************************/

class Session{
    public $lifetime;
    public $user_id;
    
    function __construct($user_id = 0){
        $this->lifetime = get_cfg_var('session.gc_maxlifetime');
        $this->user_id = $user_id;

        session_set_save_handler(
            array(&$this, 'open'),
            array(&$this, 'close'),
            array(&$this, 'read'),
            array(&$this, 'write'),
            array(&$this, 'destroy'),
            array(&$this, 'gc')
        );

        register_shutdown_function('session_write_close');
        session_start();
        $_SESSION['id'] = $user_id;
    }

    public function regenerate_id(){
        $old = session_id();
        session_regenerate_id();
        $this->_destroy($old);
    }

    public function open($save_path,$session_name){
        return true;
    }

    public function close(){
        return true;
    }

    public function read($id){
        $result = $dbx->select_value('sessions',
            'data','id=:id',array('id'=>$id));
        if($result===false){
            return '';
        }else{
            $dbx->update('sessions',array('ctime'=>time()),'id=:id',array('id'=>$id));
            return $result;
        }
    }

    public function write($id,$data){
        $data['user_id'] = $this->user_id;
        $data['data'] = $data;
        $data['client_ip'] = get_ip();
        $data['browser'] = $_SERVER['HTTP_USER_AGENT'];
        $data['ctime'] = time();
        
        if($dbx->select_value('sessions','COUNT(*)','id=:id',array('id'=>$id))>0){
            $dbx->update('sessions',
                        $data,'id=:id',array('id'=>$id));
        }else{
            $data['id'] = $id;
            $result = $dbx->insert('sessions',$data);
        }
        return true;
    }

    public function destroy($id){
        global $dbx;
        $result = $dbx->delete('sessions', 'id=:id',array('id'=>$id));
                    
        return ($result>0 ? true : false);
    }

    public function gc($lifetime){
        global $dbx;
        $dbx->delete('sessions', 'ctime<:lifetime',array('lifetime'=>time()-$lifetime));
    }
}
?>
