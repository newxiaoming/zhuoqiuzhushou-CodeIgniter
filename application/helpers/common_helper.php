<?php if(!defined('BASEPATH')) exit('No direct script access allowed');


/**
 * 请求方式 ：POST
 * 模拟登陆
 * @param 请求URL $url
 * @param $cookie
 * @param 请求参数  $post
 */
if(!function_exists('curl_post')){
    function curl_post($url, $post, $cookie,$headers = []) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);//登录提交的地址
        curl_setopt($curl, CURLOPT_HEADER, 0);//是否显示头信息
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);//是否自动显示返回的信息
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie); //设置Cookie信息保存在指定的文件中
        curl_setopt($curl, CURLOPT_POST, 1);//post方式提交
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));//要提交的信息
        if(!empty($headers))
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        // curl_setopt($curl, CURLOPT_PROXY, 'http://127.0.0.1:8888');
        $rs =  curl_exec($curl);//执行cURL
        curl_close($curl);//关闭cURL资源，并且释放系统资源
        return $rs;
    }
}


/**
 * 请求方式 ：GET
 * 模拟登陆
 * @param 请求URL $url
 * @param $cookie
 * @param 请求参数  $data
 */
if(!function_exists('curl_get')){
    function curl_get($url, $data = '', $cookie = '', $set_cookie = FALSE,$is_check_https = FALSE) {
        $cookie_opt = $set_cookie?CURLOPT_COOKIEJAR:CURLOPT_COOKIEFILE;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(!empty($cookie))
        {
            curl_setopt($ch, $cookie_opt, $cookie); //读写取cookie
        }
        
        if($is_check_https)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE); //这个是重点,规避ssl的证书检查。
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE); // 跳过host验证
        }else 
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //这个是重点,规避ssl的证书检查。
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 跳过host验证
        }
//         curl_setopt($ch, CURLOPT_PROXY, 'http://127.0.0.1:8888');
        $rs = curl_exec($ch); //执行cURL抓取页面内容
        curl_close($ch);
        return $rs;
    }
}

/**
 * 请求方式 ：GET
 * 模拟登陆
 * @param 请求URL $url
 * @param $cookie
 * @param 请求参数  $data
 */
if(!function_exists('get_content')){
    function get_content($url, $cookie) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie); //读取cookie
        // curl_setopt($ch, CURLOPT_PROXY, 'http://127.0.0.1:8888');
        $rs = curl_exec($ch); //执行cURL抓取页面内容
        curl_close($ch);
        return $rs;
        }
}


?>