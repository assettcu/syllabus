<?php

class UserObj extends FactoryObj
{

    public $departments = array();

	public function __construct($userid=null) 
	{
		parent::__construct("username","users",$userid);
	}
	
	public function post_load()
	{
		# $this->load_departments();
	}
    
    public function load_departments()
    {
        $results = Yii::app()->db->createCommand()
            ->select("department")
            ->from("user_departments")
            ->where("username = :username",array(":username"=>$this->username))
            ->queryAll();
        
        if(empty($results)) {
            $departments = $this->pull_AD_departments();
        }
        else {
            foreach($results as $row) {
                $departments[] = $row["department"];
            }
        }
        
        $deptcodes = array();
        foreach($departments as $dept) {
            $response = StdLib::external_call("http://compass.colorado.edu/ascore/api/deptcodes",array("query"=>$dept));
            if(!is_null($response) and $response !== FALSE) {
                $deptcodes[] = @$response["id"];
            }
            else {
                $deptcodes[] = $dept;
            }
        }
        
        $this->departments = $deptcodes;
    }

    private function pull_AD_departments() {
        $adauth = new ADAuth("directory");
        $info = $adauth->lookup_user($this->username);
        $departments = array();
        if($info["count"] == 1) {
            $ou = @$info[0]["ou"];
            if(isset($ou) and $ou["count"] > 0) {
                for($a=0;$a<$ou["count"];$a) {
                    $exists = (Yii::app()->db->createCommand()
                        ->select("COUNT(*)")
                        ->from("user_departments")
                        ->where("username = :username AND department = :department",
                            array(
                                ":username"     => $this->username,
                                ":department"   => $ou[$a]
                            )
                        )
                        ->queryScalar() == 1);
                    if(!$exists) {
                        Yii::app()->db->createCommand()
                            ->insert("user_departments",
                                array(
                                    "username"      => $this->username,
                                    "department"    => $ou[$a]
                                )
                            );
                    }
                    $departments[] = $ou[$a];
                }
            }
        }
        return $departments;
    }

    public function atleast_permission($permission) {
        return Yii::app()->db->createCommand()
            ->select("COUNT(*)")
            ->from("permissions, users")
            ->where("
                    permissions.permission = :permission
                AND username = :username 
                AND permissions.level <= (SELECT `level` FROM permissions WHERE permissions.permission = users.permission_level LIMIT 1)",
                array(
                    ":username" => $this->username,
                    ":permission" => $permission
                )
            )
            ->queryScalar() == 1;
    }

    public function has_permission($prefix) {
        # Administrators or ASSETT employees always have permission
        if($this->atleast_permission("administrator") or in_array("A&S ASSETT",$this->departments)) {
            return true;
        }
        foreach($this->departments as $department) {
            if($department == $prefix) {
                return true;
            }
            if(StdLib::external_call("http://compass.colorado.edu/ascore/api/deptcontains",array("dept"=>$department,"lookfor"=>$prefix))) {
                return true;   
            }
        }
        return false;
    }

	function login() {
		
        # Insert login into the table
        Yii::app()->db->createCommand()->insert("login",array(
            "username" => $this->username,
            "date_loggedin" => date("Y-m-d H:i:s")
        ));
        
        # Reset attempts
		$this->reset_attempts();
	}
	
	function reset_attempts() 
	{
        Yii::app()->db->createCommand()->update(
		    "users",
		    array("attempts"  => 0),
            "username = :username",
            array(":username"=>$this->username)
        );
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
		$departments = Yii::app()->db->createCommand()
            ->select("department")
            ->from("user_permissions")
            ->where("username = :username",array(":username"=>$this->username))
            ->limit(1)
            ->queryScalar();
		
		return explode(",",$departments);
	}
	
	public function run_check() {
		return true;
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