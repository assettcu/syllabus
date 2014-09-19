<?php

class InstructorObj extends FactoryObj
{
	public $name = "";
    
    
	public function __construct($instructorid=null) 
	{
		parent::__construct("instrid","instructors",$instructorid);
	}

	public function pre_save()
	{
		if(!isset($this->instrid) and isset($this->name) and !isset($this->firstname,$this->lastname)) {
			$parts = explode(" ",$this->name,3);
			if(count($parts)==3) {
				list($this->firstname,$this->middlename,$this->lastname) = $parts;
			} else {
				list($this->firstname,$this->lastname) = $parts;
			}
		}
	}

}
