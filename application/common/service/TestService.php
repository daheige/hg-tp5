<?php
namespace service;

class TestService extends BaseService
{
    public function push($url = '', $params = [])
    {
        //第三个参考可以携带header头或cookie进行请求
        $curl_obj = $this->post($url, $params, [CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
        // $result   = $this->getInterfaceData($curl_obj);
        $result = $this->getData($curl_obj);
        return $result ? $result : null;
    }

}
