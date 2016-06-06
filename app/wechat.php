<?php
/***
 * 作者:一个放羊娃
 * 电话:18695275660
 * 微信:huanghe6233
 * 个人博客:hhsblog.cn
 * 2016年4月7日
 ***/
class wechatCallbackapiTest{
    //验证消息
    public function valid(){
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    //检查签名
    private function checkSignature(){
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    //响应消息
    public function responseMsg(){
        @$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if(!empty($postStr)){
            //$this->logger("R".$postStr);//存储日志
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            $time = time();
            //判断用户发来信息的类型,交给各处理方法进行处理返回xml数据输出
            switch ($RX_TYPE){
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;

                case "text":
                    $result = $this->receiveText($postObj);
                    break;

                case "image":
                    $result = $this->receiveImg($postObj);
                    break;

                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;

                case "video":
                    $result = '';
                    break;

                case "link":
                    $result = '';
                    break;

                default:
                    $result = "unknow msg type: ".$RX_TYPE;
                    break;
            }
            echo $result;
        }else{
            echo "";
            exit;
        }
    }

    //接收事件消息(MsgType为event)
    private function receiveEvent($object){
        $content = "";//定义传入信息为空
        switch ($object->Event){
            case 'subscribe':
                //用户关注执行
                $wx = new Weixin();
                $mysql = new Mysql();
                $arr = $wx->get_user_info($object->FromUserName);//调用接口查询用户详细信息
                //将用户信息存入数据库
                //判断是否有此人  有的话将Y_N改为1
                if($mysql->getOne("select count(*) from WX_up_user where openid='".$object->FromUserName."'")){
                    $data = array('Y_N'=>1,'sub_time'=>time(),'headimgurl'=>$arr['headimgurl']);
                    $mysql->Exec('WX_up_user',$data,'update',"openid='".$object->FromUserName."'");
                }else{
                    $data = array('openid'=>$arr['openid'],'nickname'=>$arr['nickname'],'sex'=>$arr['sex'],'province'=>$arr['province'],'city'=>$arr['city'],'country'=>$arr['country'],'sub_time'=>time(),'headimgurl'=>$arr['headimgurl']);
                    $mysql->Exec('WX_up_user',$data);
                }

                if($object->EventKey){
                    $key = ltrim($object->EventKey,'qrscene_');
                    if($key == 1){
                        $send = "欢迎银川的朋友";
                    }else if($key ==2){
                        $send = "欢迎北京的朋友";
                    }
                }
                $result = $this->sendText($object,$send);
                break;
            case 'unsubscribe':
                //用户取消关注执行
                $mysql = new Mysql();
                $data = array('Y_N'=>0,'N_time'=>time());
                $mysql->Exec('WX_up_user',$data,'update',"openid='".$object->FromUserName."'");
                break;
            case 'SCAN':
                $key = $object->EventKey;
                if($key == 1){
                    $send = "欢迎银川的朋友关注!";
                }else{
                    $send = "欢迎北京的朋友关注";
                }

                $result = $this->sendText($object,$send);
                break;
        }
        //如果是菜单点击事件:
        if($object->Event == 'CLICK'){
            switch($object->EventKey){
                case 'question':
                    include(ROOT.'/functions/yzdd.php');
                    $keyword = '答题';
                    $res = question($keyword,$object->FromUserName);
                    $result = $this->sendText($object,$res);
                    break;
                case "joke":
                    include(ROOT."/functions/joke.php");
                    new Weixin();
                    $result = $this->sendText($object,joke());
                    break;
                case "game":
                    $send = array( 0=>array('Title'=>'加小球游戏','Description'=>'有点难!','PicUrl'=>'http://wx.hhsblog.cn/app/games/bunengsi/icon.png','Url'=>'http://wx.hhsblog.cn/app/games/bunengsi/index.html'),1=>array('Title'=>'查开房小游戏','Description'=>'经典好玩!用来整蛊好朋友不错哦!','PicUrl'=>'http://wx.hhsblog.cn/app/games/ckf/ckf.png','Url'=>"http://wx.hhsblog.cn/app/games/ckf/index.htm"));
                    $result = $this->sendNews($object,$send);
                    break;
            }
        }
        return $result;//将传出信息向上传递
    }

    //接收文本信息(关键字处理)
    private function receiveText($object){
        $keyword = trim($object->Content);//判断传入信息的关键字
        switch($keyword){
            case '笑话':
                include(ROOT."/functions/joke.php");
                new Weixin();
                $result = $this->sendText($object,joke());
                break;
            case "测试":

                break;
            case "js":

                break;
            //没有匹配到关键字执行下面语句
            case '授权':

                break;
            case '邮件':

                break;
            default:
                $send = array( 0=>array('Title'=>'纯手工开发の公众号!','Description'=>"回复图片检测颜值！\n回复您的位置可得到当地天气情况!\n回复【笑话】得到一条笑话！\n回复【答题】进入一站到底系统！",'PicUrl'=>"http://wx.hhsblog.cn/public/images/pinpai.png",'Url'=>'http://wx.hhsblog.cn?openid='.$object->FromUserName));
                $result = $this->sendNews($object,$send);
                break;
        }
        //需要判断多个关键字或复杂逻辑的判断
        if($keyword == "答题"||preg_match("/^[A-Da-d]$/", $keyword)){
            include(ROOT.'/functions/yzdd.php');
            $res = question($keyword,$object->FromUserName);
            $result = $this->sendText($object,$res);
        }
        return $result;
    }

    //接受位置信息
    private function receiveLocation($object){
        $mysql = new Mysql();
        $wd = $object->Location_X;
        $jd = $object->Location_Y;
        $openid = $object->FromUserName;
        $label = $object->Label;
        //存储用户地址信息
        $data = array('openid'=>$openid,'LocationX'=>$wd,'LocationY'=>$jd,'label'=>$label,'time'=>time());
        $mysql->Exec('WX_user_location',$data);
        $content = array('jd'=>$jd,'wd'=>$wd);
        $result = $this->sendText($object,$content);
        return $result;
    }

    //接受图片信息
    private function receiveImg($object){
        //将图片下载下来并将地址存入数据库
        $wx = new Weixin();
        $mysql = new Mysql();
        $filename = $wx->down_media($object->MediaId);
        $data = array('openid'=>$object->FromUserName,'pathname'=>$filename,'media_id'=>$object->MediaId,'type'=>'.jpg','time'=>time());
        $mysql->Exec('WX_download_file',$data);
        include(ROOT.'/functions/face.php');
        $ren = face($object->PicUrl);
        if(is_string($ren)){
            $result = $this->sendText($object,"未检测到脸,请重新上传!");
        }else{
            $id=$mysql->getLastId();
            if((count($ren) == 3) && (count($ren[2]) ==2) ){
                for($i=0;$i<2;$i++){
                    $data = array('id'=>$id,'openid'=>$object->FromUserName,'pathname'=>$filename,'media_id'=>$object->MediaId,'time'=>time(),'race'=>$ren[$i]['race'],'gender'=>$ren[$i]['gender'],'age'=>$ren[$i]['age'],'glass'=>$ren[$i]['glass'],'smiling'=>$ren[$i]['smiling'],'yanzhi'=>$ren[$i]['yanzhi'],'py'=>$ren[2]['py'],'xs'=>$ren[2]['xs']);
                    $mysql->Exec('WX_face',$data);
                }
            }else{
                for($i=0;$i<count($ren);$i++){
                    $data = array('id'=>$id,'openid'=>$object->FromUserName,'pathname'=>$filename,'media_id'=>$object->MediaId,'time'=>time(),'race'=>$ren[$i]['race'],'gender'=>$ren[$i]['gender'],'age'=>$ren[$i]['age'],'glass'=>$ren[$i]['glass'],'smiling'=>$ren[$i]['smiling'],'yanzhi'=>$ren[$i]['yanzhi']);
                    $mysql->Exec('WX_face',$data);
                }
            }
            $a = array( 0=>array('Title'=>'查看检测结果!','Description'=>'看看!','PicUrl'=>'http://wx.hhsblog.cn/public/images/yz.png','Url'=>"http://wx.hhsblog.cn/app/face.php?openid=".$object->FromUserName."&id=".$id));
            $result = $this->sendNews($object,$a);
        }
        return $result;
    }

    //发送文字消息
    public function sendText($object,$content){
        $textTpl = 	"<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";
        //非文本消息需要文本消息回复时判断
        if(is_array($content)){
            if(isset($content['jd'])){
                include(ROOT.'/functions/weather.php');
                $content = weather($content['wd'],$content['jd']);
            }
        }
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //发送图文消息
    /*
    传入的$content格式为
    $content = array( 0=>array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''));
    如果是多图文信息
    $content = array( 0=>array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''),1=>array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''));
    返回XML
    */
    public function sendNews($object,$content){
        //$content = array( 0=>array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''));
        $item = "<item>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
               	</item>";
        $item_str = '';
        foreach($content as $v){
            $item_str .= sprintf($item,$v['Title'],$v['Description'],$v['PicUrl'],$v['Url']);
        }
        $textNew = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <Content><![CDATA[]]></Content>
                    <ArticleCount>%s</ArticleCount>
                    <Articles>
                        ".$item_str."
                    </Articles>
                    </xml>";
        $result = sprintf($textNew, $object->FromUserName, $object->ToUserName, time(), count($content));
        return $result;
    }

    //发送音乐消息
    //$content = array('Title'=>'','Description'=>'','MusicUrl'=>'','HQMusicUrl'=>'','ThumbMediaId'=>'');
    public function sendMusic($object,$content){
        $thumb = '';
        if(!empty($content['ThumbMediaId'])){
            $thumb = "<ThumbMediaId><![CDATA[".$content['ThumbMediaId']."]]></ThumbMediaId>";
        }
        $textMusic = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[music]]></MsgType>
			<Music>
			<Title><![CDATA[%s]]></Title>
			<Description><![CDATA[%s]]></Description>
			<MusicUrl><![CDATA[%s]]></MusicUrl>
			<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
			<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
			".$thumb."
			</Music>
			</xml>";
        $title = $content['Title'];
        $description = $content['Description'];
        $musicUrl = $content['MusicUrl'];
        $hqmusicurl = $content['HQMusicUrl'];
        $result = sprintf($textMusic, $object->FromUserName, $object->ToUserName, time(), $title,$description,$musicUrl,$hqmusicurl);
        return $result;
    }

    //回复视频信息
    //$content = array('Title'=>'','Description'=>'','MediaId'=>'');
    public function sendVideo($object,$content){
        $textVideo = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[video]]></MsgType>
		<Video>
		<MediaId><![CDATA[%s]]></MediaId>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		</Video> 
		</xml>";
        $title = $content['Title'];
        $description = $content['Description'];
        $mediaid = $content['MediaId'];
        $result = sprintf($textVideo, $object->FromUserName, $object->ToUserName, time(), $mediaid ,$title,$description);
        return $result;
    }
    //回复图片信息
    public function sendPic($object,$mediaid){
        $textPic = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[image]]></MsgType>
		<Image>
		<MediaId><![CDATA[%s]]></MediaId>
		</Image>
		</xml>";
        $result = sprintf($textPic, $object->FromUserName, $object->ToUserName, time(), $mediaid);
        return $result;
    }
    //回复语音信息
    public function sendVoice($object,$mediaid){
        $textVoice = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[voice]]></MsgType>
		<Voice>
		<MediaId><![CDATA[%s]]></MediaId>
		</Voice>
		</xml>";
        $result = sprintf($textVoice, $object->FromUserName, $object->ToUserName, time(), $mediaid);
        return $result;
    }

    //转发到客服
    public function customer($object,$cus){
        $textcus = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[transfer_customer_service]]></MsgType>
        <TransInfo>
            <KfAccount><![CDATA[%s]]></KfAccount>
        </TransInfo>
        </xml>";
        $result = sprintf($textcus,$object->FromUserName, $object->ToUserName, time(),$cus);
        return $result;
    }
}
?>