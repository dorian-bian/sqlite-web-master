<?php
# APC - WinCache - Xcache
class Cache {
    static $type = 'apc';
    public function __construct($type){
        $cache = NULL;
        switch(strtolower($type)){
        case 'apc': 
            $cache = new CacheAPC();
            break;
        case 'memcache': 
            $cache = new CacheMemcache();
            break;
        case 'redis': 
            $cache = new CacheRedis();
            break;
        case 'wincache': 
            $cache = new CacheWincache();
            break;
        case 'xcache': 
            $cache = new CacheXcache();
            break;
        }
    }
    
    public function instance(){
        static $cache = NULL;
        if($cache===NULL) $cache = new Cache(self::$type);
        return $cache;
    }
}

interface ICacheHandler {
    public function get($key);
    public function set($key, $val, $ttl);
    public function del($key);
    public function has($key);
    public function inc($key, $step);
    public function dec($key, $step);
}


class CacheAPC implements ICacheHandler {
    public function get($key){
        return apc_fetch($key);
    }
    public function set($key, $val, $ttl=0){
        return apc_store($key, $val, $ttl);
    }
    public function del($key){
        return apc_delete($key);
    }
    public function has($key){
        return apc_exists($key);
    }
    public function inc($key, $step){
        return apc_inc($key, $step);
    }
    public function dec($key, $step){
        return apc_dec($key, $step);
    }
}


class CacheWincache implements ICacheHandler {
    public function get($key){
        return wincache_ucache_get($key);
    }
    public function set($key, $val, $ttl=0){
        return wincache_ucache_set($key, $val, $ttl);
    }
    public function del($key){
        return wincache_ucache_delete($key);
    }
    public function has($key){
        return wincache_ucache_exists($key);
    }
    public function inc($key, $step){
        return wincache_ucache_inc($key, $step);
    }
    public function dec($key, $step){
        return wincache_ucache_dec($key, $step);
    }
}

class CacheXcache implements ICacheHandler {
    public function get($key){
        return xcache_get($key);
    }
    public function set($key, $val, $ttl=0){
        return xcache_set($key, $val, $ttl);
    }
    public function del($key){
        return xcache_unset($key);
    }
    public function has($key){
        return xcache_isset($key);
    }
    public function inc($key, $step){
        return xcache_inc($key, $step);
    }
    public function dec($key, $step){
        return xcache_dec($key, $step);
    }
}

?>
