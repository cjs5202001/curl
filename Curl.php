<?php
namespace jackenhttp;

interface interfaceCurl{
    public static function url($url);
    public function send($data);
    public function header($list);
    public function time($time);
    public function post();
    public function get();
    public function put();
    public function delete();
}
class Curl implements interfaceCurl{
    /**
     * 单例，CURL对象
     *
     * @var object
     */
    private static $instance;

    /**
     * 发送构造
     *
     * @param string $url 远程地址
     * @param string $send 发送的数据，数组键值对:key：字段，value：数据；也可任意数据
     * @param list $header 设置发送头
     * @param string $time 设置默认30秒超时
     */
    private $parameter = array(
        'url' => '', 
        'send' => '',
        'header' =>'', 
        'time' =>30 
    );

    /**
     * 重置参数
     * 
     */
    private function reset(){
        $this->parameter['send'] = '';
        $this->parameter['header'] = '';
    }
    
    /**
     * 静态方法，类实例化连接调用
     *
     * @access public
     * @param string $url 设置url
     * return pdo object
     */
    public static function url($url){
        if (!self::$instance instanceof self) { 
            self::$instance = new Curl($url);
        }
        self::$instance->parameter['url'] = $url;
        return self::$instance;
    }

    /**
     * 请求
     *
     * @access public
     * @param array $data 请求参数 
     */
    public function send($data=''){
        $this->parameter['send'] = $data;
        return $this;
    }

    /**
     * 设置http header参数
     *
     * @access public
     * @param array $list header参数
     */
    public function header($list){
        $this->parameter['header'] = $list;
        return $this;
    }

    /**
     * 设置超时时间
     *
     * @access public
     * @param int $time 默认30秒
     */
    public function time($time=''){
        if(!empty($time)){
            $this->parameter['time'] = $time;
        }
        return $this;
    }
    
    /**
     * POST方式
     */
    public function post(){ 
        return $this->execute('POST');
    }
    
    /**
     * GET方式
     */
    public function get(){ 
        return $this->execute('GET');
    }
    
    /**
     * PUT方式
     */
    public function put(){
        return $this->execute('PUT');
    }
    
    /**
     * DELETE方式
     */
    public function delete(){
        return $this->execute('DELETE');
    }
    
    private function execute($type){
        $ch = curl_init();
        if($type == 'GET' && $this->parameter['send']) { 
            curl_setopt($ch, CURLOPT_URL,$this->parameter['url'].'?'.$this->urlStr($this->parameter['send']));
        } else{
            curl_setopt($ch, CURLOPT_URL,$this->parameter['url']);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);   // https请求 不验证hosts
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->parameter['time']); 
        curl_setopt($ch, CURLOPT_HEADER, true); //返回http头
        if($this->parameter['header']){ //发送指定header头
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->parameter['header']);
        }
    
        switch ($type){ 
            case "GET": 
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;  
            
            case "POST": 
            curl_setopt($ch, CURLOPT_POST,true);   
            $this->sendData($ch);
            break;  
            
            case "PUT":  
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");   
            $this->sendData($ch);
            break;  
            
            case "DELETE": 
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");   
            $this->sendData($ch);
            break;  
        }
        
        $result=curl_exec ($ch);
        if($result === false ){
            //无法访问，或者网络异常等 code为0时表示异常，error返回异常原因
            $data =  (object) array('code' => 0,'ResponseHeader'=>'','ResponseInfo'=>'','body'=>'','error'=>curl_error($ch));
        }else{
            $data = $this->curlHeader($ch,$result);
        }
        curl_close ($ch);
        $this->reset();
        return $data;
    }

    private function sendData($ch){
        $fields = $this->urlStr($this->parameter['send']);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields)));
        curl_setopt($ch, CURLOPT_POSTFIELDS,$fields);
    }

    /**
     * 格式化URL参数
     * @return string $fields
     */
    private function urlStr($fields){
        return $fields = (is_array($fields)) ? http_build_query($fields) : $fields; 
    }

    private function curlHeader($ch,$result){
        $res = curl_getinfo($ch);
        $header = substr($result, 0, $res['header_size']);
        $header = explode("\r\n", $header);
        $body = substr($result, $res['header_size']);
        $response = array(
            'code' => $res['http_code'],
            'ResponseHeader'=>$header,
            'ResponseInfo'=>$res,
            'Authorization'=>$this->Authorization($header),
            'body'=>$body
        );
        return (object) $response;
    }

    /**
     * 获取响应的Authorization
     * @return string
     */
    private function Authorization($data){
        $Bearer = 'Authorization: Bearer';
        foreach($data as $key){
            if( strstr($key, $Bearer) ){
                $list = explode($Bearer,$key);
                return $list['1'];
            }
        }
        return '';
    }
}