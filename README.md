# A simple php Http Client

* Usage

```
    $options = [] ;
    $http = new mrjnamei\Curl($options);
    
    // http get 
    $url = "http://www.google.com";
    $res = $http->get($url,["name" => "jack"]); // $res is the response 
    
    $res = $http->post($url,["name" => "jack"]); // post request
  
```

* construct options 
```
    $options = [
        'base_path' => 'http://www.google.com/' , //base path 
        'headers' => [
               'Content-Type' => 'text/html;charset=utf-8',
        ],
        'CURLOPT_CONNECTTIMEOUT' => 30 , //timeout 
        'CURLOPT_SSL_VERIFYPEER' => true , // use ssl 
        'CURLOPT_CAINFO'        => 'path/to/cert.pem',
        ........
    ];
```


