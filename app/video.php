<?php
require("../library/init.php");
$jssdk = new JSSDK();
$res = $jssdk->getSignPackage();
$url = "http://wx.hhsblog.cn/app/jssdk.php";
//$_GET['state'] = '01XQ7BB2QS6yxYpUNGvDwRG_pcaG24prSrAk5z4SyEEZgldXUy3eOZFoOflBWpXS';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>时尚朋友圈语音!</title>

    <link rel="stylesheet" href="http://wx.hhsblog.cn/public/css/bootstrap.min.css">
    <script src="http://wx.hhsblog.cn/public/js/jquery.min.js"></script>
    <style>
        .h21{
            text-align: center;
            margin:0 auto;
        }
    </style>
</head>
<body>
    <br>
    <br>
    <div class="container-fluid">
        <h2 class="h21">时尚朋友圈语音!</h2>
        <p class="h21">还有更多功能!长按二维码关注哦!</p>
        <br>
        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10">
                <img src="./images/WW.png" alt="" class="img-thumbnail" width="100%">
            </div>
            <div class="col-xs-1"></div>
        </div>
        <br>
        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10">
                <button class="btn btn-lg btn-info btn-block" id="ting">重新听!</button>
            </div>
            <div class="col-xs-1"></div>
        </div>
        <br>
        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10">
                <a href="<?php echo $url;?>" class="btn btn-lg btn-danger btn-block" id="luyin">去录音!</a>
            </div>
            <div class="col-xs-1"></div>
        </div>
    </div>
    <br>
    <br>
    <p class="h21">技术支持:一个放羊娃</p>
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    
    wx.config({
        debug:false,
        appId:"<?php echo $res['appId'];?>",
        timestamp:"<?php echo $res['timestamp'];?>",
        nonceStr: "<?php echo $res['nonceStr'];?>",
        signature:  "<?php echo $res['signature'];?>",
        jsApiList:[
            'downloadVoice',
            'onVoiceRecordEnd',
            'startRecord',
            'stopRecord',
            'uploadVoice',
            'playVoice',
            'onVoicePlayEnd',
            'onMenuShareTimeline'
        ]
    });
</script>
<script>
    var server_Id = '<?php echo $_GET['state'];?>';
    var local_Id;
    wx.ready(function(){
        wx.downloadVoice({
            serverId: server_Id, // 需要下载的音频的服务器端ID，由uploadVoice接口获得
            isShowProgressTips: 1, // 默认为1，显示进度提示
            success: function (res) {
                local_Id = res.localId; // 返回音频的本地ID
                wx.playVoice({
                    localId: res.localId // 需要播放的音频的本地ID，由stopRecord接口获得
                });
            }
        });
    });
    $("#ting").click(function(){
        wx.playVoice({
            localId: local_Id // 需要播放的音频的本地ID，由stopRecord接口获得
        });
    });
</script>
</html>