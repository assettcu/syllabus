<?php

class SyslogObj extends FactoryObj
{
	public function __construct($logid=null) {
		parent::__construct("logid","syslog",$logid);
	}
	
	public function pre_save() {
		if(!isset($this->date_logged) or $this->date_logged == "") {
			$this->date_logged = date("Y-m-d H:i:s");
		}
		if(!isset($this->ipaddress)) {
			$this->ipaddress = $_SERVER["REMOTE_ADDR"];
		}
		if(!isset($this->username)) {
			$this->username = Yii::app()->user->name;
		}
	}
}
