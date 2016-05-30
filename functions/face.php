<?php 


function distance($x1,$y1,$x2,$y2){
    return sqrt(pow(($x2-$x1),2) + pow(($y2-$y1),2));
}

function face($pic){
	$str = '';
	$api = "http://apicn.faceplusplus.com/v2/detection/detect?api_key=434adf72a775d75f6322720ca7751e61&api_secret=4tUa-zfiqwuiDYMKzMAm8TZMWCW17rKA&url=$pic&attribute=gender,age,race,glass,smiling";	
	$cont = file_get_contents($api);
	$cont = json_decode($cont,'ture')['face'];
	if(count($cont) == 0){
		$str = "未检测到脸,请重新上传!";
		return $str;
	}
	$ren = array();
	for($i=0;$i<count($cont);$i++){
		$face_id = $cont[$i]['face_id'];
		$race = $cont[$i]['attribute']['race']['value'];//种族,White,Black,Asina
		$gender = $cont[$i]['attribute']['gender']['value'];//性别,Male Famale
		$age = $cont[$i]['attribute']['age']['value'];//年龄
		$glass = $cont[$i]['attribute']['glass']['value'];//眼镜,None/Dark/Normal
		$smiling = intval($cont[$i]['attribute']['smiling']['value']);//笑容,实数
		$minage = $age - $cont[$i]['attribute']['age']['range'] + 5;//最小年龄
		$maxage = $age + $cont[$i]['attribute']['age']['range'];//最大年龄
		if($race === 'Asian' ){
			$race= "亚洲人";
		}else if ($race === 'White'){
			$race= "白种人";
		}else if ($race === 'Black'){
			$race= "黑人";
		}
		if($gender === 'Male' ){
		$gender= "帅哥";
		}else {
			$gender= "美女";
		}
		$age = $minage."-".$maxage."岁";
		if($glass === 'None'){
			$glass= "没戴眼镜";
		}else if ($glass === 'Dark'){	
			$glass= "墨镜";
		}else if ($glass === 'Normal' ){
			$glass= "普通眼镜";
		}
		$yanzhi = yanzhi($face_id);
		//$smiling = "这个微笑我给".$smiling."分/:strong\n";
		$ren[] = array("race"=>$race,"gender"=>$gender,"age"=>$age,"glass"=>$glass,"smiling"=>$smiling,"yanzhi"=>$yanzhi);
	}

	if( count($cont) == 2 ){
		//判断相似度
		$face_id1 = $cont[0]['face_id'];
		$face_id2 = $cont[1]['face_id'];
		$api2 = "https://apicn.faceplusplus.com/v2/recognition/compare?api_secret=4tUa-zfiqwuiDYMKzMAm8TZMWCW17rKA&api_key=434adf72a775d75f6322720ca7751e61&face_id2=$face_id2&face_id1=$face_id1";
		$xiangsi = file_get_contents($api2);
		$xiangsi = json_decode($xiangsi,'ture');
		//print_r($xiangsi);
		$xs = intval($xiangsi['similarity']);
		$eyes = intval($xiangsi['component_similarity']['eye']);
		$eyebrow = intval($xiangsi['component_similarity']['eyebrow']);
		$mouth = intval($xiangsi['component_similarity']['mouth']);
		$nose = intval($xiangsi['component_similarity']['nose']);
		$zs = round(($xs+$eyes+$eyebrow+$mouth+$nose)/5);
		if($cont[0]['attribute']['gender']['value'] !== $cont[1]['attribute']['gender']['value']){
			//性别不同则执行
			if($zs<40){
				$py = '花好月圆';
			}else if($zs<50){
				$py = '相濡以沫';
			}else if($zs<60){
				$py = '情真意切';
			}else if($zs<70){
				$py = '郎才女貌';
			}else if($zs<80){
				$py = '心心相印';
			}else if($zs<90){
				$py = '浓情蜜意';
			}else{
				$py = '龙凤胎吧';
			}
			//$str = "【夫妻相指数】\n得分:".$zs."\n评语:".$py;
		}else{//性别相同则执行
			if($cont[0]['attribute']['gender']['value'] === 'Male'){
				//2个同为男
				if($zs<40){
				$py = '酒肉兄弟';
				}else if($zs<50){
					$py = '拜把兄弟';
				}else if($zs<60){
					$py = '手足之情';
				}else if($zs<70){
					$py = '式好之情';
				}else if($zs<80){
					$py = '玉友金昆';
				}else if($zs<90){
					$py = '两肋插刀';
				}else{
					$py = '不是双胞胎就同一个人';
				}
				//$str = "【基友指数】\n得分:".$zs."\n评语:".$py;
			}else{//2个同为女
				if($zs<40){
				$py = "最好的闺蜜/:strong";
				}else if($zs<50){
					$py = '似水如鱼';
				}else if($zs<60){
					$py = '形影不离';
				}else if($zs<70){
					$py = '如影随形';
				}else if($zs<80){
					$py = '恩恩爱爱';
				}else if($zs<90){
					$py = '不分彼此';
				}else{
					$py = '不是双胞胎就同一个人';
				}
				//$str = "【姐妹指数】\n得分:".$zs."\n评语:".$py;
			}
		}
		$ren[2] = array('py'=>$py,'xs'=>$zs);
	}
	return $ren;
}



function yanzhi($face_id){
	$key = "434adf72a775d75f6322720ca7751e61";
	$secret = "4tUa-zfiqwuiDYMKzMAm8TZMWCW17rKA";
	$api3 = "http://api.faceplusplus.com/detection/landmark?api_secret=$secret&api_key=$key&face_id=$face_id";
	$cont1 = file_get_contents($api3);
	$cont1 = json_decode($cont1,true);
	$face = $cont1['result'][0]['landmark'];
	
	//求出两眉毛中心点坐标
	$x = ($face['left_eyebrow_right_corner']['x']+$face['right_eyebrow_left_corner']['x'])/2;
	$y = ($face['left_eyebrow_right_corner']['y']+$face['right_eyebrow_left_corner']['y'])/2;
	
	//两眉毛中心到鼻子的高度
	$c2 = distance($x,$y,$face['nose_tip']['x'],$face['nose_tip']['y']);
	//眼角距离
	$c3 = distance($face['left_eye_right_corner']['x'],$face['left_eye_right_corner']['y'],$face['right_eye_left_corner']['x'],$face['right_eye_left_corner']['y']);
	
	//鼻子宽度
	$c4 = distance($face['nose_left']['x'],$face['nose_left']['y'],$face['nose_right']['x'],$face['nose_right']['y']);
	
	//脸宽
	$c5 = distance($face['contour_left1']['x'],$face['contour_left1']['y'],$face['contour_right1']['x'],$face['contour_right1']['y']);
	
	//鼻子下方到下巴的高
	$c6 = distance($face['contour_chin']['x'],$face['contour_chin']['y'],$face['nose_contour_lower_middle']['x'],$face['nose_contour_lower_middle']['y']);
	
	//眼睛的宽度
	$c7_left = distance($face['left_eye_left_corner']['x'],$face['left_eye_left_corner']['y'],$face['left_eye_right_corner']['x'],$face['left_eye_right_corner']['y']);
	$c7_right = distance($face['right_eye_left_corner']['x'],$face['right_eye_left_corner']['y'],$face['right_eye_right_corner']['x'],$face['right_eye_right_corner']['y']);

	//嘴巴的大小
	$c8 = distance($face['mouth_left_corner']['x'],$face['mouth_left_corner']['y'],$face['mouth_right_corner']['x'],$face['mouth_right_corner']['y']);

	//嘴巴处face的大小
	$c9 = distance($face['contour_left6']['x'],$face['contour_left6']['y'],$face['contour_right6']['x'],$face['contour_right6']['y']);
	//计算颜值大小
	$yourmark = 100;
	$mustm = 0;
	//眼角距离为脸宽的1/5.
	$mustm += abs(($c3/$c5)*100-25);
	//鼻子宽度为脸宽的1/5.
	$mustm += abs(($c4/$c5)*100-25);
	//眼睛的宽度,应为同一水平脸部宽度的1/5.
	$mustm += abs(((($c7_left+$c7_right)/2)/$c5)*100-25);
	//嘴巴的宽度,应为同一脸部宽度的1/2.
	$mustm += abs(($c8/$c9)*100 - 50);
	//下巴到鼻子下方的高度 == 眉毛中点到鼻子最低处的距离
	$mustm += abs($c6-$c2);
	
	$sco = $yourmark - intval($mustm) + 5;
	return $sco;
}


print_r(face("http://d.hiphotos.baidu.com/image/pic/item/562c11dfa9ec8a13f075f10cf303918fa1ecc0eb.jpg"));
?>