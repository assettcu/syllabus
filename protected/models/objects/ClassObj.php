<?php

class ClassObj extends FactoryObj
{
	
	public function __construct($classid=null) 
	{
		parent::__construct("classid","classes",$classid);
	}
	
	public function pre_save()
	{
		if(is_array($this->instructors)) {
			$this->instructors = $this->encode_instructors();
		}
		if(!isset($this->who_uploaded)) {
			$this->who_uploaded = Yii::app()->user->name;
		}
		if(!isset($this->date_uploaded)) {
			$this->date_created = date("Y-m-d H:i:s");
		}
	}
	
	public function post_save()
	{
		if(is_string($this->instructors)) {
			$this->instructors = $this->decode_instructors();
		}
	}

	public function pre_load()
	{
		if(!isset($this->classid) and isset($this->course,$this->section)) {
			$conn = Yii::app()->db;
			$query = "
				SELECT		classid
				FROM		{{classes}}
				WHERE		courseid = :courseid
				AND			section LIKE :section
			";
			$command = $conn->createCommand($query);
			$command->bindParam(":courseid",$this->course->courseid);
			$command->bindValue(":section","%".$this->section."%");
			$this->classid = $command->queryScalar();
		}
	}

	public function post_load()
	{
		$this->course = $this->get_course();
		if(is_string($this->instructors)) {
			$this->instructors = $this->decode_instructors();
		}
		$this->syllabi = $this->get_syllabi();
	}
	
	private function encode_instructors() {
		$instrs = "";
		if(isset($this->instructors) and !empty($this->instructors) and is_array($this->instructors)) {
			$temp = "";
			foreach($this->instructors as $instructor) {
				$temp[] = $instructor->instrid;
			}
			$instrs = implode(",",$temp);
		}
		return $instrs;
	}
	
	private function decode_instructors() {
		$instrs = array();
		if(isset($this->instructors) and is_string($this->instructors)) {
			$instructors = explode(",",$this->instructors);
			foreach($instructors as $instrid) {
				$instrs[] = new InstructorObj($instrid);
			}
		}
		return $instrs;
	}

	public function get_course()
	{
		if(isset($this->courseid)) {
			return new CourseObj($this->courseid);
		}
	}
	
	public function get_syllabi()
	{
		$conn = Yii::app()->db;
		$query = "
			SELECT		syllabusid
			FROM		{{syllabi}}
			WHERE		classid = :classid
			ORDER BY	date_uploaded DESC;
		";
		$command = $conn->createCommand($query);
		$command->bindParam(":classid",$this->classid);
		$result = $command->queryAll();
		
		$this->syllabi = array();
		if(!$result or empty($result)) return array();
		
		foreach($result as $row) {
			$this->syllabi[] = new SyllabusObj($row["syllabusid"]);
		}
		
		return $this->syllabi;
	}
	
	public function has_syllabus()
	{
		if(!isset($this->syllabi)) $this->get_syllabi();
		if(empty($this->syllabi)) return false;
		$syllabus = $this->syllabi[0];
		return ($syllabus->valid());
	}
	
	public function print_instructors()
	{
		$instructors = array();
		if(is_string($this->instructors)) {
			$this->instructors = $this->decode_instructors();
		}
		foreach($this->instructors as $instructor) {
			if(!$instructor->loaded) continue;
			if($instructor->firstname == "" and $instructor->lastname == "") {
				list($instructor->firstname,$instructor->lastname) = explode(" ",$instructor->name,2);
				$instructor->save();
			}
			$instructors[] = "<a href='".Yii::app()->createUrl("search")."?s=".$instructor->name."'>$instructor->name</a>";
		}
		return implode(", ",$instructors);
	}
	
	public function get_first_syllabus()
	{
		if(!isset($this->syllabi)) $this->get_syllabi();
		return array_pop($this->syllabi);
	}
	
	public function run_check()
	{
		# Check if Course exists
		$course = new CourseObj($this->courseid);
		if(!$course->loaded) {
			return !$this->set_error("Course does not exist.");
		}
		# No empty instructors
		/** Removed
		if(empty($this->instructors)) {
			return !$this->set_error("Instructors cannot be empty.");
		}
		 *
		 */
		$this->instructors = $this->decode_instructors();
		# Check if Instructor(s) exists
		foreach($this->instructors as $instructor) {
			if(!$instructor->loaded) {
				return !$this->set_error("Could not load instructor with id: ".$instructor->instrid);
			}
		}
		$this->instructors = $this->encode_instructors();
		# No empty sections
		if(empty($this->section)) {
			return !$this->set_error("Sections cannot be empty.");
		}
		# Sections are formatted as ###
		$sects = explode(",",$this->section);
		foreach($sects as $sect) {
			if(!preg_match("/^[0-9]{3}$/",$sect)) {
				return !$this->set_error("Sections must be 3 digits in its format seperated by a comma.");
			}
		}
		# Years must be reasonable ####
		if(!preg_match("/^[0-9]{4}$/",$this->year)) {
			return !$this->set_error("Years must be 4 digits in its format.");
		}

		return true;
	}

	public function has_primary_syllabus()
	{
		if(!isset($this->syllabi)) $this->get_syllabi();
		if(empty($this->syllabi)) return false;
		
		foreach($this->syllabi as $syllabus) {
			if($syllabus->primary == 1) return true;
		}
		return false;
	}
	
	public function get_primary_syllabus()
	{
		if(!isset($this->syllabi)) $this->get_syllabi();
		if(empty($this->syllabi)) return null;
		
		foreach($this->syllabi as $syllabus) {
			if($syllabus->primary == 1) return $syllabus;
		}
		return null;
	}
}
