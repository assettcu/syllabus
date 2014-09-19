<?php
/*
 * Password hashing with PBKDF2.
 * Author: havoc AT defuse.ca
 * www: https://defuse.ca/php-pbkdf2.htm
 */

// These constants may be changed without breaking existing hashes.
define("PBKDF2_HASH_ALGORITHM", "sha256");
define("PBKDF2_ITERATIONS", 1000);
define("PBKDF2_SALT_BYTES", 24);
define("PBKDF2_HASH_BYTES", 24);

define("HASH_SECTIONS", 4);
define("HASH_ALGORITHM_INDEX", 0);
define("HASH_ITERATION_INDEX", 1);
define("HASH_SALT_INDEX", 2);
define("HASH_PBKDF2_INDEX", 3);

class UserObj extends FactoryObj
{

	public function __construct($userid=null) 
	{
		parent::__construct("username","users",$userid);
	}

	public function pre_save() {
		if($this->get_state() == "new") {
			$this->attempts = 0;
			$this->active = 1;
			if(!isset($this->permission_level)) {
				$this->permission_level = 1;
			}
			if(isset($this->adsync) and $this->adsync=="on") {
				$this->password_hash = "";
			}
			$this->date_added = date("Y-m-d H:i:s");
		}
	}
	
	public function post_save() {
		if($this->have_perms_changed()) {
			$this->update_permissions();
		}
		$syslog = new SyslogObj();
		if($this->get_state() == "new") {
			$syslog->action = "add user successful";
		} else {
			$syslog->action = "save user successful";
		}
		$syslog->notes = "username: ".$this->username;
		$syslog->save();
	}
	
	public function post_load()
	{
		$this->load_permissions();
	}

	function create_hash($password) 
	{
	    // format: algorithm:iterations:salt:hash
	    $salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTES, MCRYPT_DEV_URANDOM));
	    return PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" .  $salt . ":" .
	        base64_encode($this->pbkdf2(
	            PBKDF2_HASH_ALGORITHM,
	            $password,
	            $salt,
	            PBKDF2_ITERATIONS,
	            PBKDF2_HASH_BYTES,
	            true
	        ));
	}
	
	function validate_password($password, $good_hash) 
	{
	    $params = explode(":", $good_hash);
	    if(count($params) < HASH_SECTIONS)
	       return false;
	    $pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
	    return $this->slow_equals(
	        $pbkdf2,
	        $this->pbkdf2(
	            $params[HASH_ALGORITHM_INDEX],
	            $password,
	            $params[HASH_SALT_INDEX],
	            (int)$params[HASH_ITERATION_INDEX],
	            strlen($pbkdf2),
	            true
	        )
	    );
	}
	
	// Compares two strings $a and $b in length-constant time.
	function slow_equals($a, $b) 
	{
	    $diff = strlen($a) ^ strlen($b);
	    for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
	    {
	        $diff |= ord($a[$i]) ^ ord($b[$i]);
	    }
	    return $diff === 0;
	}
	
	/*
	 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
	 * $algorithm - The hash algorithm to use. Recommended: SHA256
	 * $password - The password.
	 * $salt - A salt that is unique to the password.
	 * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
	 * $key_length - The length of the derived key in bytes.
	 * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
	 * Returns: A $key_length-byte key derived from the password and salt.
	 *
	 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
	 *
	 * This implementation of PBKDF2 was originally created by https://defuse.ca
	 * With improvements by http://www.variations-of-shadow.com
	 */
	function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false) 
	{
	    $algorithm = strtolower($algorithm);
	    if(!in_array($algorithm, hash_algos(), true))
	        die('PBKDF2 ERROR: Invalid hash algorithm.');
	    if($count <= 0 || $key_length <= 0)
	        die('PBKDF2 ERROR: Invalid parameters.');
	
	    $hash_length = strlen(hash($algorithm, "", true));
	    $block_count = ceil($key_length / $hash_length);
	
	    $output = "";
	    for($i = 1; $i <= $block_count; $i++) {
	        // $i encoded as 4 bytes, big endian.
	        $last = $salt . pack("N", $i);
	        // first iteration
	        $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
	        // perform the other $count - 1 iterations
	        for ($j = 1; $j < $count; $j++) {
	            $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
	        }
	        $output .= $xorsum;
	    }
	
	    if($raw_output)
	        return substr($output, 0, $key_length);
	    else
	        return bin2hex(substr($output, 0, $key_length));
	}

	function login() 
	{
		$syslog = new SyslogObj();
		$syslog->username = $this->username;
		$syslog->ipaddress = $_SERVER['REMOTE_ADDR'];
		$syslog->action = "login successful";
		$syslog->notes = "";
		if(!$syslog->save()) {
			var_dump($syslog->get_error()); die();
		}
		
		$this->reset_attempts();
	}
	
	function reset_attempts() 
	{
		$conn = Yii::app()->db;
		$query = "
			UPDATE		{{users}}
			SET			attempts = 0
			WHERE		username = :username;
		";
		$transaction = $conn->beginTransaction();
		try {
			$command = $conn->createCommand($query);
			$command->bindParam(":username",$this->username);
			$command->execute();
			$transaction->commit();
		} catch(Exception $e) {
			$transaction->rollback();
			var_dump($e); die();
		}
	}
	
	function iterate_attempts() 
	{
		$conn = Yii::app()->db;
		$query = "
			UPDATE		{{users}}
			SET			attempts = attempts + 1
			WHERE		username = :username;
		";
		$transaction = $conn->beginTransaction();
		try {
			$command = $conn->createCommand($query);
			$command->bindParam(":username",$this->username);
			$command->execute();
			$transaction->commit();
		} catch(Exception $e) {
			$transaction->rollback();
			var_dump($e); die();
		}
	}
	
	function load_permissions()
	{
		$this->permissions = array();
		$conn = Yii::app()->db;
		$query = "
			SELECT		permid
			FROM		{{user_permissions}}
			WHERE		username = :username;
		";
		$command = $conn->createCommand($query);
		$command->bindParam(":username",$this->username);
		$result = $command->queryAll();
		
		if(!$result or empty($result)) {
			$this->permissions = array();
			return false;
		}
		foreach($result as $row) {
			$this->permissions[] = new PermissionObj($row["permid"]);
		}
		
		$this->permhash = $this->gen_permhash();
		
		return $this->permissions;
	}
	
	public function run_check() {
		if($this->get_state() == "new") {
			if(!isset($this->username) or $this->username == "") {
				return !$this->set_error("The username must be set and cannot be empty");
			}
			if($this->loaded) {
				return !$this->set_error("The username ".$this->username." already exists in the database.");
			}
			if(!isset($this->email) or $this->email == "") {
				return !$this->set_error("Email must be set and cannot be empty.");
			}
			if(Yii::app()->user->getState("_user")->permission_level < $this->permission_level) {
				return !$this->set_error("You do not have sufficient privilege to give this user this permission level.");
			}
		} else {
			if(!$this->loaded) {
				return false;
			}
			if(!isset($this->adsync) and isset($this->password1,$this->password2)) {
				if($this->password1 == "") {
					return !$this->set_error("Password cannot be empty.");
				}
				if($this->password1 != $this->password2) {
					return !$this->set_error("Passwords must match.");
				}
			}
		}
		return true;
	}
	
	public function has_permission($obj) 
	{
		$adminflag = false;
		if(get_class($obj)=="ClassObj") {
			if(!isset($obj->course)) $obj->get_course();
			$course = $obj->course->prefix;
		} else if(get_class($obj)=="CourseObj"){
			$course = $obj->prefix;
		} else if(is_string($obj) and strlen($obj)==4){
			$course = $obj;
		}
		foreach($this->permissions as $perm) {
			$courses = explode(",",$perm->permission);
			if(in_array($course,$courses)) {
				return ($perm->level != 0);
			}
			if($perm->level == 2) {
				$adminflag = true;
			}
		}
		return $adminflag;
	}
	
	public function shares_permissions_with($userobj) 
	{
		if(!$userobj->loaded) return false;
		foreach($this->permissions as $perm) {
			if($perm->level==2) return true;
		}
		foreach($userobj->permissions as $user_perm) {
			foreach($this->permissions as $this_perm) {
				$courses1 = explode(",",$this_perm->permission);
				$courses2 = explode(",",$user_perm->permission);
				$int = array_intersect($courses1, $courses2);
				if(!empty($int)) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function get_allowed_perms()
	{
		if(!isset($this->permissions)) {
			$this->load_permissions();
		}
		$allowed = array();
		foreach($this->permissions as $perm) {
			if($perm->level >= 1) {
				if($perm->permission=="") continue;
				$allowed[] = $perm->permission;
			}
		}
		return implode(",",$allowed);
	}
	
	
	public function get_restricted_perms()
	{
		if(!isset($this->permissions)) {
			$this->load_permissions();
		}
		$restricted = array();
		foreach($this->permissions as $perm) {
			if($perm->level == 0) {
				if($perm->permission=="") continue;
				$restricted[] = $perm->permission;
			}
		}
		return implode(",",$restricted);
	}
	
	public function gen_permhash()
	{
		$hash = "";
		if(!isset($this->permissions)) {
			$this->load_permissions();
		}
		foreach($this->permissions as $perm) {
			$hash .= md5(serialize($perm));
		}
		$hash = md5($hash);
		return $hash;
	}
	
	public function have_perms_changed()
	{
		if(!isset($this->permhash)) return true;
		$hash = $this->gen_permhash();
		return ($hash != $this->permhash);
	}
	
	public function update_permissions()
	{
		if(!isset($this->permissions)) return false;
		$conn = Yii::app()->db;
		$query = "
			DELETE FROM			{{user_permissions}}
			WHERE				username = :username;
		";
		$transaction = $conn->beginTransaction();
		try {
			$command = $conn->createCommand($query);
			$command->bindParam(":username",$this->username);
			$command->execute();
			$transaction->commit();
		} catch(Exception $e) {
			$this->set_error($e);
			$transaction->rollBack();
		}
		
		foreach($this->permissions as $perm) {
			$perm->save();
		}
	}
}

?>