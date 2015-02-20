<?php

class SyllabusObj extends FactoryObj
{
	
	public function __construct($syllabusid=null) 
	{
		parent::__construct("syllabusid","syllabi",$syllabusid);
	}
	
	public function pre_load()
	{
		if(!isset($this->syllabusid) and isset($this->filename,$this->classid)) {
			$conn = Yii::app()->db;
			$query = "
				SELECT 		syllabusid 
				FROM		{{syllabi}}
				WHERE		classid = :classid
				AND			filename = :filename
				LIMIT		1;
			";
			$command = $conn->createCommand($query);
			$command->bindParam(":classid",$this->classid);
			$command->bindParam(":filename",$this->filename);
			$this->syllabusid = $command->queryScalar();
		}
	}
	
	public function pre_save()
	{
		if(!isset($this->who_uploaded)) {
			$this->who_uploaded = Yii::app()->user->name;
		}
		if(!isset($this->date_uploaded)) {
			$this->date_uploaded = date("Y-m-d H:i:s");
		}
		if(!isset($this->version)) {
			$this->version = 1;
		}
	}
	
	public function valid()
	{
		$class = $this->get_classobj();
		if(!$class or !isset($class->course) or !$class->course->loaded) {
			return false;
		}
		$filename = 'C:/archive/'.$class->course->prefix.'/'.$this->filename;
		return (is_file($filename) and filesize($filename)>0 and is_readable($filename));
	}
	
	public function upload_file($file) 
	{
		$class = $this->get_classobj();
		if(!$class) {
			return false;
		}
		
		$this->type = str_replace(".","",substr($file["name"],-4,4));
		if(!$this->allowed_types($this->type)) {
			return !$this->set_error("The syllabus must be a doc, docx, pdf, htm/html, or a txt file. All other file types are unsupported right now.");
		}
		
		if($file["error"]!=0) {
			return !$this->set_error("The upload rendered an error code of ".$syllabus["error"]);
		}
		
		if($file["size"]==0) {
			return !$this->set_error("File uploaded is probably corrupted. Filesize was ".StdLib::display_filesize($file["size"]).". Please try again.");
		}
		
		$class = $this->get_classobj();
		$this->filename = $this->generate_syllabus_name().".".$this->type;
		
		$target_dir = "C:/archive/".$class->course->prefix;
		if(!is_dir($target_dir)) {
			mkdir($target_dir);
		}
		$target_location = $target_dir."/".$this->filename;
		
		
		// File already exists
		if($this->valid()) {
			$this->load();
			if($this->loaded) {
				$this->version += 1;
				$this->filename_backup = $target_location.".old";
				rename($target_location,$this->filename_backup);
			}
		}
		
		if(!move_uploaded_file($file["tmp_name"], $target_location)) {
			print "Could not upload file";
			die();
			return !$this->set_error("Could not move uploaded file. Please try again.");
		}
		
		
		return $this->valid();
	}
	
	public function rollback()
	{
		if(isset($this->classid,$this->filename_backup,$this->filename)) {
			$target_location = "C:/archive/".$class->course->prefix."/".$this->filename;
			if(is_file($target_location) and is_file($this->filename_backup)) {
				unlink($target_location);
				rename($this->filename_backup,$target_location);
			}
		}
	}
	
	public function generate_syllabus_name()
	{
		$class = $this->get_classobj();
		if(!$class) {
			return false;
		}
		
		$section = explode(",",$class->section);
		$section = $section[0];
		// term_year_prefix_sect100.pdf
		return $class->term."_".$class->year."_".$class->course->num."_sect".$section;
	}
	
	public function parse_syllabus_name()
	{
		list($this->term,$this->year,$this->num,$end) = explode("_",$this->filename);
		$end = explode(".",$end);
		$this->section = str_replace("sect","",$end[0]);
		if($this->section == ""){
			$this->section = "000";
		}
		$this->type = $end[1];
	}
	
	public function get_classobj()
	{
		if(!isset($this->classid)) {
			return !$this->set_error("The class id must be set to add a syllabus.");
		}
		if(isset($this->class) and $this->class->loaded and $this->class->classid == $this->classid) {
			return $this->class;
		}
		$this->class = new ClassObj($this->classid);
		
		return $this->class;
	}
	
	public function allowed_types($type)
	{
		switch($type) {
			case "pdf":
			case "doc":
			case "docx":
			case "htm":
			case "html":
			case "txt": return true;
			default: return false;
		}
	}
	
}
