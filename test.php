<?php
require('Curl.php');

use jackenhttp\Curl;

//友好的格式化输出
function dump($var, $exit = true) {
    echo '<pre>';
    print_r ( $var );
    echo '</pre>';
    if ($exit) {
        die ();
    }
}

/**
1：请求方法：get；post；put；delete （必填）

2：设置方法：

url：远程请求地址；（必填）

send：发送内容（多个字段发送采用：数组键值对，其他可以任何方式，如json,xml等，GET方式可以直接接在后面，也可以使用此参数）（选填）

header：设置发送头，数组 （选填）

time：超时时间，我这里默认30秒（选填）
*/

//GET方式，发送a=123
$curl = Curl::url('127.0.0.1/4.php?a=123')->get(); 

//也可以用send方法发送数据
$curl = Curl::url('127.0.0.1/4.php')->send(['a'=>123])->get();

//设置 3秒超时
$curl = Curl::url('127.0.0.1/4.php?a=123')->time(3)->get(); 

//发送POST，字段a
$curl = Curl::url('127.0.0.1/4.php')->send(['a'=>123])->post();

//发送POST json
$data = json_encode(['a'=>123],JSON_UNESCAPED_UNICODE);
$header= array('Content-Type: application/json'); //设置header，可设置多个，具体参考：CURLOPT_HTTPHEADER
$curl = Curl::url('127.0.0.1/4.php')->send($data)->header($header)->post();

//发送PUT，字段a
$curl = Curl::url('127.0.0.1/4.php')->send(['a'=>123])->put();

//发送DELETE，字段a
$curl = Curl::url('127.0.0.1/4.php')->send(['a'=>123])->delete();

//响应
dump($curl); //所有响应

$curl->code; //HTTP 响应状态码 0表示异常，可通过$curl->error，查看原因，其他为标准HTTP状态码
$curl->body; //响应内容
$curl->ResponseHeader;//响应头 数组
$curl->ResponseInfo; //其他响应信息 数组 此数据为 curl_getinfo 信息
$curl->Authorization; //获取响应的Authorization: Bearer



