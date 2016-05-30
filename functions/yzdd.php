<?php 
/****
布尔教育 高端PHP培训
培  训: http://www.itbool.com
论  坛: http://www.zixue.it
****/

function question($keyword,$fromUsername){
	$mysql = new Mysql();
	if($keyword == '答题'){
		$data = array('openid'=>$fromUsername);
		$mysql->Exec('record',$data);
		//取出第一道题目与答案赋给$get
		$get = $mysql->getRow("select * from questions order by id limit 1;");
		//初始$contentStr并输出第一道题目
		$contentStr = "一站到底在线答题现在开始:\n第1题:";
		$contentStr .= $get['question'] ."\nA.".$get['optionA']."\nB.".$get['optionB']."\nC.".$get['optionC']."\nD.".$get['optionD'];
		//将用户答题数初始化为0
		$mysql->query("update record set question = 1,question_y=0,question_n=0 where openid = '".$fromUsername."';");
		return $contentStr;
	}else{
		//将$keyword 转为大写
		$keyword = strtoupper($keyword);
	}
	//如果回复的是a-f,则先判断用户是否已初始化($wi为用户答题数,为-1则用户没有发送过[答题])
	//$wi 为当前所答题目序号.($maxi为题目的最大值)
	if( $mysql->getOne('select question from record where openid = "'.$fromUsername.'";') != false){
		$wi = $mysql->getOne('select question from record where openid = "'.$fromUsername.'";');//当前题目数
		$maxi = $mysql->getOne("select count(*) from questions;"); //题目总数
		//$xx为用户答题信息,是一个数组
		//Array ( [openid] => abc123abc123 [question] => 1 [question_y] => 0 [question_n] => 0 )
		$xx = $mysql->getRow('select * from record where openid="'.$fromUsername.'"');
		//$answer为出用户当前答题的答案
		$answer = $mysql->getOne("select answer from questions where id = $wi");
		//$questioni为当前答题数加1
		$questioni = $wi+1;
		//判断用户答题数是否等于题目数,如果大于等于则判断最后一题的正误并输出答题多少并计算答对答错多少题
 		$zhengque = $xx['question_y'];
 		$cuowu = $xx['question_n'];
 		@$zql = $zhengque/($zhengque+$cuowu)*100;
		if($wi == $maxi){
			//$qy1 为评语
			$py1 = '';
			//判断最后一题正误
			if($keyword == $answer){
				$contentStr = "恭喜您,回答正确!\n您已答完所有题目,答题系统结束。\n";
				$mysql->query("update record set question_y = question_y + 1 where openid='".$fromUsername."';");
				$xx = $mysql->getRow('select * from record where openid="'.$fromUsername.'"');
				$zhengque = $xx['question_y'];
				//判断答对多少题决定评语
				if($zhengque <9){$py1 = '哎呀呀...智商堪忧啊!告诉小伙伴们,让他们也试试/:,@P';}else 
				if($zhengque <16){$py1 = '就算你及格吧...还需要努力!不如拿它考考小伙伴们?/:B-)';}else 
				if($zhengque <20){$py1 = '哇!好厉害...!/::B不如拿它考考小伙伴们?/:B-)';}else{$py1 = '不多说,智商逆天...!你咋不上天呢?/::!';}
 				@$zql = round($zhengque/($zhengque+$cuowu)*100);
				@$contentStr .= "题目个数:".$wi."个\n正确个数:".$zhengque."个\n错误个数:".$cuowu."个\n正确率:".$zql."%\n";
			}else{
				$contentStr = "很抱歉,回答错误!\n您已答完所有题目,答题系统结束。\n";
				$mysql->query("update record set question_n = question_n + 1 where openid='".$fromUsername."';");
				$xx = $mysql->getRow('select * from record where openid="'.$fromUsername.'"');
 				$cuowu = $xx['question_n'];
 				if($zhengque <9){$py1 = '哎呀呀...智商堪忧啊!告诉小伙伴们,让他们也试试/:,@P';}else 
				if($zhengque <16){$py1 = '就算你及格吧...还需要努力!不如拿它考考小伙伴们?/:B-)';}else 
				if($zhengque <20){$py1 = '哇!好厉害...!/::B不如拿它考考小伙伴们?/:B-)';}
 				@$zql = round($zhengque/($zhengque+$cuowu)*100);
				@$contentStr .= "题目个数:".$wi."个\n正确个数:".$zhengque."个\n错误个数:".$cuowu."个\n正确率:".$zql."%\n";
			}
			$contentStr .= $py1;
			//将用户从答题系统里删除
			$mysql->query("delete from record where openid='".$fromUsername."';");
			return $contentStr;
		}else{
			//$timu为下一题的题目
			$timu = $mysql->getRow("select * from questions where id = ".$questioni);
			//小于最大题目数则判断正误,并返回下一题
			if($keyword == $answer){
				//答题正确则返回
				$mysql->query("update record set question_y = question_y + 1 where openid='".$fromUsername."';");
				$contentStr =  "恭喜您,回答正确\n第".$questioni."道题:";
				$contentStr .= $timu['question'] ."\nA.".$timu['optionA']."\nB.".$timu['optionB']."\nC.".$timu['optionC']."\nD.".$timu['optionD'];
				$mysql->query("update record set question = question+1 where openid = '".$fromUsername."';");
				return $contentStr;
			}else{
				//答题错误则返回
				if(preg_match("/^[A-Da-d]$/", $keyword)){
					$mysql->query("update record set question_n = question_n + 1 where openid='".$fromUsername."';");
					$contentStr = "很抱歉,回答错误\n第".$questioni."道题:";
					$contentStr .= $timu['question'] ."\nA.".$timu['optionA']."\nB.".$timu['optionB']."\nC.".$timu['optionC']."\nD.".$timu['optionD'];
					$mysql->query("update record set question = question+1 where openid = '".$fromUsername."';");
					return $contentStr;
				}
			}
		}

	}else{
		//如果用户没有发过[答题]就发a-d则输出"请发送答题以启动答题系统"
		$msgType = "text";
		$contentStr = "请先发送【答题】以启动答题系统!";
		return $contentStr;
	}
}

?>