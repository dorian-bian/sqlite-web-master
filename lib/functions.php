<?php
function get_ip(){
    if(isset($_SERVER)){
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])){
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        }else{
            $realip = $_SERVER['REMOTE_ADDR'];
        }
    }else{
        if(getenv('HTTP_X_FORWARDED_FOR')){
            $realip = getenv( 'HTTP_X_FORWARDED_FOR');
        }elseif(getenv('HTTP_CLIENT_IP')){
            $realip = getenv('HTTP_CLIENT_IP');
        }else{
            $realip = getenv('REMOTE_ADDR');
        }
    }
    return $realip;
}

function build_pagination($current=1, $total=1, $url_format='%d.html', $first_url='index.html', $num_numbers=10){
    $prev = $current==1 ? 1 : $current-1;
    $next = $current==$total ? $total: $current+1;

    $i_min = $current-ceil($total/2);
    $i_min = $i_min > 0 ? $i_min : 1;

    $i_max = min($i_min + $num_numbers, $total+1);
    
    $nums = array();
    for($i=$i_min; $i<$i_max; $i++){
       $nums[] = array(
            'num' => $i,
            'url' => $i==1 ? $first_url : sprintf($url_format, $i)
       );
    }
    
    return array(
        array(
            '__num__' => $nums,
            'next' => $next==1 ? $first_url : sprintf($url_format,$next),
            'prev' => $prev==1 ? $first_url : sprintf($url_format,$prev),
            'first' => $first_url,
            'last' => $total==1 ? $first_url : sprintf($url_format,$total),
            'current' => $current,
            'total' => $total
        )
    );
}

function build_options($options, $value, $selected='selected="selected"'){
    foreach($options as &$option){
        if($option['value']==$value){
            $option['selected'] = $selected;
        }
    }
    return $options;
}

function uuid_v4() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
?>
