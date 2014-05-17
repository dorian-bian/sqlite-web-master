<?php
require_once P_PATH.'/app/lib/content.php';

class m_content_list extends m_base {
    public $operators = array(
        'equal' => array('name'=>'equal', 'title'=> '=', 'format'=>'%s = %s'),
        'less' => array('name'=>'less', 'title'=> '<', 'format'=>'%s < %s'),
        'less_equal' => array('name'=>'less_equal', 'title'=> '<=', 'format'=>'%s <= %s'),
        'greater' => array('name'=>'greater', 'title'=> '>', 'format'=>'%s > %s'),
        'greater_equal' => array('name'=>'greater_equal', 'title'=> '>=', 'format'=>'%s >= %s'),
        'not_equal' => array('name'=>'not_equal', 'title'=> '!=', 'format'=>'%s != %s'),
        'like' => array('name'=>'like', 'title'=> 'LIKE', 'format'=>'%s LIKE %s'),
        'not_like' => array('name'=>'not_like', 'title'=> 'NOT LIKE', 'format'=>'%s NOT LIKE %s'),
        'in' => array('name'=>'in', 'title'=> 'IN (...)', 'format'=>'%s IN (%s)'),
        'not_in' => array('name'=>'not_in', 'title'=> 'NOT IN (...)', 'format'=>'%s NOT IN (%s)'),
        'between' => array('name'=>'between', 'title'=> 'BETWEEN ... AND ...', 'format'=>'%s BETWEEN %s AND %s'),
        'not_between' => array('name'=>'not_between', 'title'=> 'NOT BETWEEN ... AND ...', 'format'=>'%s NOT BETWEEN %s AND %s'),
        'is_null' => array('name'=>'is_null', 'title'=> 'IS NULL', 'format'=>'%s IS NULL'),
        'is_not_null' => array('name'=>'is_not_null', 'title'=> 'IS NOT NULL', 'format'=>'%s IS NOT NULL')
    );
    
    public function render($content=''){
        self::$title[] = 'Content List';
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        
        if(IS_POST){
            $rowids = isset($_POST['rowids']) ? $_POST['rowids'] : array();
            $action = isset($_POST['action']) ? $_POST['action'] : NULL;
            try{
                switch($action){
                case 'delete':
                    content_delete(self::$source['name'], $rowids);
                    break;
                case 'update':
                    content_save_items(self::$source['name'], $rowids, $_POST['items'], $_POST['metas']);
                    break;
                case 'filter':
                    $filter = $_POST['filter'];
                    $_GET['filter'] = $filter;
                    $query = $_SERVER['PHP_SELF'].'?'.http_build_query($_GET);
                    header("Location: $query");
                    return;
                    break;
                }
                $is_success = TRUE;
            }catch(Exception $e){
                $error = $e->getMessage();
                $trace = $e->getTraceAsString();
            }
        }
        
        $filter = isset($_GET['filter']) ? $_GET['filter'] : array(
            'cols' => array_fill(0, 3, array('key'=>'', 'op'=>'equal', 'val'=>'0')),
            'extra'=> ''
        );
        
        $filter_data = array();
        $operators = $this->operators;
        
        foreach($filter['cols'] as $i=>$item){
            $key = trim($item['key']);
            if($item['key'] && isset($operators[$item['op']])){
                $format = $operators[$item['op']]['format'];
                switch($item['op']){
                case 'between':
                case 'not_between':
                    $cond_values = explode(',', $item['val'], 2);
                    $cond_values[] = NULL;
                    $filter_data[] = sprintf($format, $item['key'], $cond_values[0], $cond_values[1]);
                    break;
                case 'in':
                case 'not_in':
                    $filter_data[] = sprintf($format, $item['key'], $item['val']);
                    break;
                case 'is_null':
                case 'is_not_null':
                    $filter_data[] = sprintf($format, $item['key']);
                    break;
                default:
                    $filter_data[] = sprintf($format, $item['key'], $item['val']);
                    break;
                }
            }
        }
        
        if(strlen(trim($filter['extra'])) > 0) $filter_data[] = $filter['extra'];
        $filter_text =  implode(' AND ', $filter_data);
        
        $columns = table_columns(self::$source['name']);
        
        $content_data = array();
        $pagination = 1;
        $count = 0;
        
        
        $page = isset($_GET['p']) && $_GET['p'] > 0 ? $_GET['p'] : 1;
        if($page > 1) array_unshift(self::$title, 'Page '.$page);
        
        $order_key = isset($_GET['order-key']) ? $_GET['order-key'] : 'rowid';
        $order_dir = isset($_GET['order-dir']) ? $_GET['order-dir'] : 'DESC';
        
        try {
            $order_key_q = Database::instance()->quote($order_key);
            $content_data = content_list(self::$source['name'], '_rowid_ as rowid, *',$filter_text, 
                "$order_key_q $order_dir", NULL, CONTENT_PAGE_SIZE, $page);
                
            $count = content_count(self::$source['name'], $filter_text, NULL);
            $pagination = build_pagination($page, ceil($count/CONTENT_PAGE_SIZE), '%d','1');
        
        }catch(Exception $e){
            $error = $e->getMessage();
            $trace = $e->getTraceAsString();
        }
        
        $data = array();
        $data['is_success'] = $is_success;
        $data['error'] = $error;
        $data['trace'] = $trace;
        
        $data['is_editable'] = self::$source['type']=='table';
        $data['source'] = self::$source['name'];
        $data['operators'] = $operators;
        
        $data['filter-text'] = $filter_text;
        $data['filter-cols'] = $filter['cols'];
        $data['filter-extra'] = $filter['extra'];
        
        $data['order-key'] = $order_key;
        $data['order-dir'] = $order_dir=='DESC' ? 'ASC' : 'DESC';
        $data['order-ico'] = $order_dir=='DESC' ? 'icon-arrow-down' : 'icon-arrow-up';
        
        $data['columns'] = $columns;
        $data['columns_count'] = count($columns) + 3;
        $data['blob-image'] = isset($_GET['blob-image']) ? $_GET['blob-image'] : '';
        $data['total-rows'] = $count;
        $data['total-cols'] = count($columns); 
        $data['content_data'] = $content_data;
        $data['self-url'] = urlencode($_SERVER['REQUEST_URI']);
        $data['pagination'] = $pagination;
        
        return self::merge('m_source', $data, 'content-list.tpl');
    }
}
?>
