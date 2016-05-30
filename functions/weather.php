<?php 

function weather($wd,$jd){
	$api = "http://api.map.baidu.com/telematics/v3/weather?location={$jd},{$wd}&output=json&ak=07c8d27bbe614cfeec7383b722c6ccb4";
	$tian = json_decode(file_get_contents($api),true)['results'][0];
	$dizhi = $tian['currentCity'];
	$pm25 = $tian['pm25'];
	$chuanyi = $tian['index'][0]['des'];
	$zwx = $tian['index'][5]['des'];
	$gm = $tian['index'][3]['des'];
	$tq = $tian['weather_data'];
	$chengshi = "亲~您在$dizhi";
	$jt = '今天是'.$tq[0]['date'].$tq[0]['weather'].' '.$tq[0]['wind'].',全天温度'.$tq[0]['temperature'];
    $zhishu="pm2.5指数:".$pm25."\n紫外线指数:$zwx$gm";
    $mt = $tq[1]['date'].' '.$tq[1]['weather']. ' '.'温度:'.$tq[1]['temperature'].$tq[1]['wind'];
    $ht = $tq[2]['date'].' '.$tq[2]['weather']. ' '.'温度:'.$tq[2]['temperature'].$tq[2]['wind'];
    $dht = $tq[3]['date'].' '.$tq[3]['weather']. ' '.'温度:'.$tq[3]['temperature'].$tq[3]['wind'];
    $contentStr = "$chengshi \n$jt\n$zhishu\n$mt\n$ht\n$dht";
    return $contentStr;
}
?>