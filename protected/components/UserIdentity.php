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
			$this->errorCode=3;
		}
        $info = $adauth->lookup_user();
		if($info["count"] == 1) {
		    $user->fullname = $info[0]["displayname"][0];
		}
        if(!$user->save()) {
            StdLib::vdump($user->get_error());
        }
        
		if($this->errorCode!=0) {
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