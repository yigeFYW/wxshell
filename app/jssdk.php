<?php
require ("../library/init.php");
$jssdk = new JSSDK();
$res = $jssdk->getSignPackage();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>朋友圈语音!</title>
    <link rel="stylesheet" href="http://wx.hhsblog.cn/public/css/bootstrap.min.css">
    <script src="http://wx.hhsblog.cn/public/js/jquery.min.js"></script>
    <script src = "http://wx.hhsblog.cn/public/js/bootstrap.min.js"></script>
    <style>
        body{
            background-image: url('./images/bg.png');
            font-size: 18px;
            font-family: '微软雅黑','黑体',sans-serif;
        }
        #body{
            margin-top: 3%;
        }
        .col-xs-2 .col-xs-4{
            margin: 20px 5px;
        }
        #a{
            text-align: center;
        }
        h4{
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container" id="body">
        <h1 style="text-align:center;">时尚录音机</h1>
        <br>
        <div class="row" style="display:block;" id="title">
            <div class="col-xs-1"></div>
            <div class="col-xs-10">
                <div class="form-group has-success">
                    <label class="control-label" for="inputSuccess1">分享朋友圈标题:(上传之前需修改)</label>
                    <input type="text" class="form-control" id="inputSuccess1" value="我的时尚朋友圈语音!">
                </div>
            </div>
            <div class="col-xs-1"></div>
        </div>
        <br>
        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10">
                <button class="btn btn-block btn-lg btn-success" id="ly">点击录音</button>
            </div>
            <div class="col-xs-1"></div>
        </div>
        <br>
        <br>
        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10">
                <button class="btn btn-block btn-lg btn-warning" id="st">点击试听</button>
            </div>
            <div class="col-xs-1"></div>
        </div>
        <br>
        <br>
        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10">
                <button class="btn btn-block btn-lg btn-info" id="cl">重新录制</button>
            </div>
            <div class="col-xs-1"></div>
        </div>
        <br>
        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10">
                <p id="p">需要上传才能分享哦!</p>
                <button class="btn btn-block btn-lg btn-danger" id="sc">点击上传</button>
            </div>
            <div class="col-xs-1"></div>
        </div>
        <br>
        <h3 id="a">最长不超过60s!</h3>
    </div>
    <footer>
        <h4>技术支持:一个放羊娃</h4>
    </footer>
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
            'onVoiceRecordEnd',
            'startRecord',
            'stopRecord',
            'playVoice',
            'uploadVoice',
            'onVoicePlayEnd',
            'onMenuShareTimeline'
        ]
    });
    var local_Id;
    var server_Id;
    /*$("#inputSuccess1").keyup(function(){
        title = $("#inputSuccess1").val();
    });*/
    
    wx.ready(function(){
        wx.onVoiceRecordEnd({
            // 录音时间超过一分钟没有停止的时候执行
            complete: function (res) {
                local_Id = res.localId;
                $("#ly").html('点击录音');
                $("#a").html('超过60s自动停止');
            }
        });
        wx.onVoicePlayEnd({
            //试听播放完毕执行
            success: function (res) {
                $('#st').html('点击试听');
            }
        });
    });
</script>
<script>
    var title_url;
    $("#ly").click(function(){
        //点击录音,判断是否点下去
        if($("#ly").html() == "点击录音"){
            //开始录音
            $("#ly").html("正在录音...点击停止!");
            $("#a").html("录音中......");
            wx.startRecord();
        }else if($("#ly").html() == "正在录音...点击停止!"){
            //停止录音
            $('#ly').html('录音已完成!');
            $("#a").html("记得点击试听哦");
            wx.stopRecord({
                success: function (res) {
                    local_Id = res.localId;
                }
            });
        }
    });

    $('#cl').click(function(){
        //点击重新录制
        $('#ly').html('点击录音');
        $('#a').html("请重新点击录音键");
        var ti = document.getElementById("inputSuccess1");
        title_url = ti.value;
        wx.stopRecord({
            success: function (res) {
                local_Id = res.localId;
            }
        });
    });

    $('#st').click(function(){
        wx.playVoice({
            localId: local_Id // 需要播放的音频的本地ID，由stopRecord接口获得
        });
    });
    
    $("#sc").click(function(){
        wx.uploadVoice({
            localId: local_Id,
            isShowProgressTips: 1,
            success: function (res){
                server_Id = res.serverId;
                $("#a").html("上传成功!");
                $("#p").html("上传成功,点击右上角分享到朋友圈吧!");
                $('#title').css("display","block");
                 //上传之后修改分享接口属性
                var url1 = "http://wx.hhsblog.cn/app/video.php?state="+server_Id;
                //获取title
                var ti = document.getElementById("inputSuccess1");
                title_url = ti.value;
                //var title = document.getElementById('inputSuccess1').value;
                wx.onMenuShareTimeline({
                    title: title_url, // 分享标题
                    link : url1, // 分享链接
                    imgUrl: "http://wx.hhsblog.cn/public/images/video.png", // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
            }
        });
    });
</script>
</html>