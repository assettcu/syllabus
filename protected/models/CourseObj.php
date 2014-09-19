<?php

class CourseObj extends FactoryObj
{
	
	public function __construct($courseid=null) 
	{
		parent::__construct("courseid","courses",$courseid);
	}
	
	public function pre_load()
	{
		if(!isset($this->courseid) and isset($this->prefix,$this->num) and $this->prefix!="" and $this->num!=""){
			$conn = Yii::app()->db;
			$query = "  
				SELECT		courseid
				FROM		{{courses}}
				WHERE		prefix 		= :prefix
				AND			num 		= :num;
			";
			$command = $conn->createCommand($query);
			$command->bindParam(":prefix",$this->prefix);
			$command->bindParam(":num",$this->num);
			$this->courseid = $command->queryScalar();
		}
	}

	public function num_classes()
	{
		$conn = Yii::app()->db;
		$query = "  
			SELECT		COUNT(*)
			FROM		{{classes}}
			WHERE		courseid = :courseid;
		";
		$command = $conn->createCommand($query);
		$command->bindParam(":courseid",$this->courseid);
		return $command->queryScalar();
	}

	public function get_classes()
	{
		$conn = Yii::app()->db;
		$query = "  
			SELECT		classid
			FROM		{{classes}}
			WHERE		courseid = :courseid
			ORDER BY	year DESC, term = 'Spring', term LIKE '%Summer%', term = 'Fall', section ASC;
		";
		$command = $conn->createCommand($query);
		$command->bindParam(":courseid",$this->courseid);
		$result = $command->queryAll();
		
		$this->classes = array();
		foreach($result as $row) {
			$this->classes[$row["classid"]] = new ClassObj($row["classid"]);
		}
		
		return $this->classes;
	}
	
    public function num_syllabi()
    {
        $conn = Yii::app()->db;
        $query = "  
            SELECT      COUNT(*)
            FROM        {{syllabi}} AS S
            INNER JOIN  {{classes}} AS CL
            ON          S.classid = CL.classid
            WHERE       CL.courseid = :courseid;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":courseid",$this->courseid);
        return $command->queryScalar();
    }

    public function print_span_years()
    {
        $conn = Yii::app()->db;
        $query = "  
            SELECT      year
            FROM        {{classes}}
            WHERE       courseid = :courseid
            ORDER BY    year ASC
            LIMIT 1;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":courseid",$this->courseid);
        $minyear = $command->queryScalar();
        
        $query = "  
            SELECT      year
            FROM        {{classes}}
            WHERE       courseid = :courseid
            ORDER BY    year DESC
            LIMIT 1;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":courseid",$this->courseid);
        $maxyear = $command->queryScalar();
        
        echo $minyear."-".$maxyear;
        return;
    }

}
