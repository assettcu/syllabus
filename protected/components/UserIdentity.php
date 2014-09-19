<?php

class UserIdentity extends CUserIdentity
{
	 public $errorCode;
	
	 public function authenticate() 
	 {
		$this->errorCode=self::ERROR_NONE;

		$authenticated = false;
		$username = $this->username;
		$password = $this->password;

		$adauth = new ADAuth("adcontroller");
		
		$user = new UserObj($username);
		if(!$adauth->authenticate($username, $password)){
			if(!$user->loaded) {
				$this->errorCode=2;
			} else {
				if(!$user->validate_password($password,$user->password_hash)) {
					$this->errorCode=3;
				}
			}
		}
		
		if($this->errorCode==0) {
			$user->login();
			Yii::app()->user->setState("_user",$user);
		} else {
			$syslog = new SyslogObj();
			$syslog->username = $this->username;
			$syslog->ipaddress = $_SERVER['REMOTE_ADDR'];
			$syslog->action = "login attempt unsuccessful";
			$syslog->notes = "Error number: ".$this->errorCode;
			if(!$syslog->save()) {
				var_dump($syslog->get_error()); die();
			}
			if($this->errorCode!=4){
				$user->iterate_attempts();
			}
		}

		return !$this->errorCode;
	 }
}