<?php 
error_reporting(0);
class errorLog{
	public function __construct(){
		$this->iserr();
	}
	public function iserr(){
		set_exception_handler([$this,'ex']);
		set_error_handler([$this,'err']);
		register_shutdown_function([$this,'rsf']);
	}
	public function rsf(){
		$rs = error_get_last();
		if($rs['type'] == 1 || $rs['type'] == 4 ||  $rs['type'] == 16){}
		$this->errlog($rs['type'],$rs['message'],$rs['file'],$rs['line']);
	}
	public function ex($ex){
		$type = $ex->getCode();
		$msg = $ex->getMessage();
		$file = $ex->getFile();
		$line = $ex->getLine();
		$this->errlog($type,$msg,$file,$line);
	}
	public function err($type,$msg,$file,$line){
		$this->errlog($type,$msg,$file,$line);
	}
	//错误信息收集并统一处理
	public function errlog($type,$msg,$file,$line){
		$errstr = date('Y-m-d H:i:s',time())."\r\n";
		$errstr .= "错误级别:".$type."--文件:".$file."--第".$line."行!\r\n";
		$errstr .= "错误信息:".$msg."\r\n\r\n";
		error_log($errstr,3,ROOT."/log/error.log");
	}
}
new errorLog();


?>