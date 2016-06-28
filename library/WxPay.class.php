<?php

//通用函数类
class CommonUtil{

    //连接url与参数
    public function genAllUrl($toUrl,$paras){
        $allUrl = null;
        if($toUrl == null){
            die("URL is null");
        }
        if(strripos($toUrl,"?")){
            $allUrl = $toUrl."?".$paras;
        }else{
            $allUrl = $toUrl."&".$paras;
        }
        return $allUrl;
    }

    //生成随机16位字符串
    public function create_noncestr($length = 16){
        $str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = str_shuffle($str);
        return substr($str,0,$length);
    }

    //去除空白字符串
    public static function trimString($value){
        $ret = null;
        if($ret != null){
            $ret = $value;
            if(strlen($ret) == 0){
                $ret = null;
            }
        }
        return $ret;
    }

    //将参数字典序排序,使用URL键值对的格式拼接成字符串
    public function formatQueryParaMap($paraMap,$urlencode){
        $buff = "";
        ksort($paraMap);
        foreach($paraMap as $k=>$v){
            if(null != $v && "null" != $v && "sign" != $k){
                if($urlencode){
                    $v = $urlencode($v);
                }
                $buff .= $k."=".$v."&";
            }
        }
        $reqPar = null;
        if(strlen($buff) > 0){
            $reqPar = substr($buff,0,strlen($buff) -1);
        }
        return $reqPar;
    }

    //将参数字典序排序,使用URL键值对的格式拼接成字符串,字符串转小写
    public function formatBizQueryParaMap($paraMap,$urlencode){
        $buff = "";
        ksort($paraMap);
        foreach($paraMap as $k=>$v){
            if($urlencode){
                $v = $urlencode($v);
            }
            $buff .= strtolower($k)."=".$v."&";
        }
        $reqPar = null;
        if(strlen($buff) > 0){
            $reqPar = substr($buff,0,strlen($buff) -1);
        }
        return $reqPar;
    }

    //数组转XML
    public function arrayToXml($arr){
        $xml = "<xml>";
        foreach($arr as $k=>$v){
            if(is_numeric($v)){
                $xml .= "<".$k.">".$v."</".$k.">";
            }else{
                $xml .= "<".$k."><![CDATA[".$v."]]></".$k.">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
}

//MD5签名类
class MD5SignUtil{
    //连接参数,md5签名后再转成大写
    public function sign($content,$key){
        try {
            if (null == $key) {
                throw new SDKRuntimeException("财付通签名key不能为空!" . "<br>");
            }
            if (null == $content) {
                throw new SDKRuntimeException("财付通签名内容不能为空" . "<br>");
            }
            $signStr = $content . "&key=" . $key;
            return strtoupper(md5($signStr));
        }catch(SDKRuntimeException $e){
            die($e->errorMessage());
        }
    }

    //验证md5签名
    public function verifySignature($content,$sign,$md5key){
        $signStr = $content . "&key=" . $md5key;
        $calculateSign = strtolower(md5($signStr));
        $tenpaySign = strtolower($sign);
        return $calculateSign == $tenpaySign;
    }
}

//异常处理类
class SDKRuntimeException extends Exception{
    //返回错误
    public function errorMessage(){
        return $this->getMessage();
    }
}

//微信支付类
class WxPayHelper{
    public $parameters;//配置参数

    public function __construct(){

    }

    //设置参数
    public function setParameter($parameter,$parameterValue){
        $this->parameters[CommonUtil::trimString($parameter)] = CommonUtil::trimString($parameterValue);
    }

    //获取参数
    public function getParameter($parameter){
        return $this->parameters[$parameter];
    }

    //生成16位随机字符串
    protected function create_noncestr($length = 16){
        $str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = str_shuffle($str);
        return substr($str,0,$length);
    }

    //检查配置参数
    public function check_parameters(){
        if($this->parameters["bank_type"] == null || $this->parameters["body"] == null || $this->parameters["partner"] == null || $this->parameters["out_trade_no"] == null || $this->parameters["total_fee"] == null || $this->parameters["fee_type"] == null || $this->parameters["notify_url"] == null || $this->parameters["spbill_create_ip"] == null || $this->parameters["input_charset"] == null){
            return false;
        }
        return true;
    }

    //生成package订单详情
    protected function get_cft_package(){
        try{
            if(null == PARTNERKEY || "" == PARTNERKEY){
                throw new SDKRuntimeException("密钥不能为空"."<br>");
            }
            $commonUtil = new CommonUtil();
            ksort($this->parameters);
            $unSignParaString = $commonUtil->formatQueryParaMap($this->parameters,false);
            $paraString = $commonUtil->formatQueryParaMap($this->parameters,true);
            $md5SignUtil = new MD5SignUtil();
            return $paraString."&sign=".$md5SignUtil->sign($unSignParaString,$commonUtil->trimString(PARTNERKEY));
        }catch (SDKRuntimeException $e){
            die($e->errorMessage());
        }
    }

    //生成签名
    protected function get_biz_sign($bizObj){
        foreach($bizObj as $k=>$v){
            $bizParameters[strtolower($k)] = $v;
        }
        try{
            if(APPKEY == ""){
                throw new SDKRuntimeException("APPKEY为空!"."<br>");
            }
            $bizParameters['appkey'] = APPKEY;
            ksort($bizParameters);
            $commonUtil = new CommonUtil();
            $bizString = $commonUtil->formatBizQueryParaMap($bizParameters,false);
            return sha1($bizString);
        }catch (SDKRuntimeException $e){
            die($e->errorMessage());
        }
    }

    //生成jsapi支付请求json
    public function create_biz_package(){
        try{
            if($this->check_parameters() == false){
                throw new SDKRuntimeException("生成package参数缺失!"."<br>");
            }
            $nativeObj['appid'] = MYID;
            $nativeObj['package'] = $this->get_cft_package();
            $nativeObj['timeStamp'] = strval(time());
            $nativeObj['nonceStr'] = $this->create_noncestr();
            $nativeObj['paySign'] = $this->get_biz_sign($nativeObj);
            $nativeObj['signType'] = SIGNTYPE;
            return json_encode($nativeObj);
        }catch (SDKRuntimeException $e){
            die($e->errorMessage());
        }
    }
}















