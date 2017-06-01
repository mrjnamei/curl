<?php

/**
 * @author mrjnamei<vardump@foxmail.com>
 * A simple http client
 */
namespace mrjnamei\curl;

class Curl
{

    /**
     * @var basePath
     */
    protected $base_path;

    /**
     * @var request headers
     */
    protected $headers = array();

    /**
     * default timeout
     * @var int
     */
    protected $timeout = 30;

    /**
     * @var options
     */
    protected $options = [];


    protected $client = null;


    public function __construct($options = array())
    {
        $this->config($options);
    }

    /**
     * config self
     * @param array $options
     */
    private function config($options = array())
    {
        if (isset($options['base_path'])) {
            $this->base_path = rtrim(trim($options['base_path']),"/");
        }

        if (isset($options['headers']) && is_array($options['headers'])) {
            $this->headers = array_merge($this->options, $options['headers']);
        }

        $this->options = $options;
    }

    /**
     * @param $base_path
     */
    public function setBasePath($base_path)
    {
        $this->base_path = $base_path;
    }

    /**
     * @return basePath
     */
    public function getBasePath()
    {
        return $this->base_path;
    }

    /**
     * setHeaders
     * @param array $options
     */
    public function setHeaders($headers = array())
    {
        if (is_array($headers)) {
            $this->headers = $headers;
        } else {
            $this->headers [] = $headers;
        }
    }

    /**
     * @return array headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return null|resource
     */
    protected function getClient()
    {
        $this->client = curl_init();
        $this->setDefaultOptions();
        return $this->client;
    }

    private function setDefaultOptions()
    {
        curl_setopt($this->client, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        // 默认不显示输出
        curl_setopt($this->client, CURLOPT_HEADER, isset($this->options['CURLOPT_HEADER']) ? $this->options['CURLOPT_HEADER'] : false);
        // 默认获取头信息
        curl_setopt($this->client, CURLINFO_HEADER_OUT, isset($this->options['CURLINFO_HEADER_OUT']) ? $this->options['CURLINFO_HEADER_OUT'] : true);
        // 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);
        // 默认不检查curl证书信息 .如果需要证书，需要传入绝对路径
        curl_setopt($this->client, CURLOPT_SSL_VERIFYPEER, isset($this->options['CURLOPT_SSL_VERIFYPEER']) ? $this->options['CURLOPT_SSL_VERIFYPEER'] : false);
        //curl fileinfo
        if(isset($this->options['CURLOPT_CAINFO'])){
            curl_setopt($this->client, CURLOPT_CAINFO ,$this->options['CURLOPT_CAINFO'] );
        }
        // 最大链接数
        curl_setopt($this->client, CURLOPT_MAXCONNECTS, isset($this->options['CURLOPT_MAXCONNECTS']) ? $this->options['CURLOPT_MAXCONNECTS'] : false);
        // 检查服务器证书
        curl_setopt($this->client ,CURLOPT_SSL_VERIFYHOST ,isset($this->options['CURLOPT_SSL_VERIFYHOST']) ? $this->options['CURLOPT_SSL_VERIFYHOST'] : 2);
    }

    /**
     * 设置cookie
     * @param array $cookies
     */
    public function setCookie($cookies = array()){
        $cookie = array();
        foreach( (array) $cookies as $key => $val ){
            $cookie [] = "{$key}={$val}";
        }
        $cookie = implode(";" ,$cookie) ;
        curl_setopt($this->client ,CURLOPT_COOKIE , $cookie);
    }

    /**
     * 设置cookie文件
     */
    public function setCookieFile($file)
    {
        curl_setopt($this->client ,CURLOPT_COOKIEFILE ,$file) ;
    }


    /**
     * add headers
     * @param array $headers
     */
    public function addHeader($headers = array())
    {
        if (is_array($headers)) {
            $this->headers = array_merge($this->headers, $headers);
        } else {
            $this->headers [] = $headers;
        }
    }

    /**
     * 设置请求地址
     */
    private function setUrl($url){
        curl_setopt($this->client ,CURLOPT_URL ,$url);
    }


    private function makeQuery($params = array()){
        if(is_array($params)){
            $p = array();
            foreach ($params as $key => $val){
                $p [] = "{$key}=" . urlencode($val);
            }
            $field = implode('&' , $p);
        }else{
            $field = $params ;
        }
        return $field ;
    }


    private function setRequestHeader(){
        if(is_null($this->headers)){
            return ;
        }
        $h = array();
        foreach ($this->headers as $key => $val){
            $h [] = $key . ":" . $val ;
        }
        curl_setopt($this->curl ,CURLOPT_HTTPHEADER ,$h ) ;
    }

    private function request(){

        $this->setRequestHeader();

        $res = curl_exec($this->client);
        $error = curl_errno($this->client);
        curl_close($this->client);
        if($error != CURLE_OK ){
            return false ;
        }
        return $this->response($res);
    }

    private function response($res){
        if (strpos($res, "\r\n\r\n") !== false) {
            list($_, $body) = explode("\r\n\r\n", $res, 2);
        }
        $http_code = $this->getHttpCode();
        if($http_code != 200 ){
            return false ;
        }
        return $body ;
    }


    public function getHttpCode()
    {
        return curl_getinfo($this->curl ,CURLINFO_HTTP_CODE);
    }

    public function getInfo()
    {
        return curl_getinfo($this->curl);
    }

    /**
     * @param $url
     * @param null $params
     * @return bool
     */
    public function get($url , $params = null)
    {
        $this->getClient();
        if(! is_null($this->base_path)){
            $url = $this->base_path . "/" . $url ;
        }
        if(!is_null($params)){
            $url .= "?" . $this->makeQuery($params);
        }
        $this->setUrl($url);
        return $this->request() ;
    }

    public function post($url ,$params)
    {
        $this->getClient();
        if(! is_null($this->base_path)){
            $url = $this->base_path . "/" . $url ;
        }
        if(!is_null($params)){
            $params .= "?" . $this->makeQuery($params);
        }
        $this->setUrl($url);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
        return $this->request();
    }
}