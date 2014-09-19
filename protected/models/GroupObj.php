<?php

class GroupObj {
	
	private $error_msg = "";
	private $state = "";
	public $loaded = false;
	
	public function __construct($object_frame=null) 
	{
		if(is_null($object_frame)) return false;
		$this->object_frame = $object_frame;
		$this->object_classname = get_class($object_frame);
		$this->load();
	}
	
	protected function pre_save()
	{
		/** This function is meant to be overloaded **/
	}

	protected function post_save()
	{
		/** This function is meant to be overloaded **/
	}

	protected function pre_load()
	{
		/** This function is meant to be overloaded **/
	}

	protected function post_load()
	{
		/** This function is meant to be overloaded **/
	}
	
	public function load() 
	{
		$this->pre_load();
		
		if(is_null($this->object_frame)) {
			$this->post_load();
			return false;
		}
		
		$obj_frame = clone $this->object_frame;
		$uid = $obj_frame->uniqueid;
		
		$conn = Yii::app()->db;
		$query = "
			SELECT		$uid
			FROM		{{".$obj_frame->table."}}
			".$this->load_where()."
			".$this->load_limit()."
			".$this->load_order()."
		";
		$command = $conn->createCommand($query);
		$result = $command->queryAll();
		if(!$result) {
			$this->post_load();
			return false;
		}
		
		$this->{$obj_frame->table} = array();
		if(empty($result)) {
			$this->loaded = true;
			$this->post_load();
			return true;
		}
		
		foreach($result as $index=>$row) {
			$this->{$obj_frame->table}[$index] = new  $this->object_classname($row[$obj_frame->uniqueid]);
		}
		$this->loaded = true;
		$this->post_load();
		return true;
	}
	
	public function load_where()
	{
		$where = "WHERE 1=1";
		return $where;
	}
	
	public function load_limit()
	{
		$limit = "";
		// $limit = "LIMIT 1,30";
		return $limit;
	}
	
	public function load_order()
	{
		$order = "";
		return $order;
	}
	
	public function get_type()
	{
		return $this->object_classname;
	}
	
	
	public function filter($filters,$threshold=-1)
	{
		# Need to include inclusive
		if(!array($filters)) return false;
		$filtered = array(); $end_filtered = array();
		foreach($this->{$this->object_frame->table} as $obj) {
			$score = 0;
			foreach($filters[0] as $index => $filter) {
				if(is_numeric($index)) {
					$vars = get_object_vars($obj);
					foreach($vars as $var) {
						if(preg_match("/".$filter."/",$var)) {
							$score++;
							if(!in_array($obj->{$obj->uniqueid},$filtered)) {
								$filtered[$obj->{$obj->uniqueid}] = $obj;
							}
						}
					}
				} else {
					if(strstr($index,".")) {
						list($objvar,$index) = explode(".",$index,2);
						if(!isset($obj->{$objvar}->$index)) continue;
						if(preg_match("/".$filter."/",$obj->{$objvar}->$index)) {
							$score++;
							if(!in_array($obj->{$obj->uniqueid},$filtered)) {
								$filtered[$obj->{$obj->uniqueid}] = $obj;
							}
						}
					} else {
						if(!isset($obj->{$index})) continue;
						if(preg_match("/".$filter."/",$obj->{$index})) {
							$score++;
							if(!in_array($obj->{$obj->uniqueid},$filtered)) {
								$filtered[$obj->{$obj->uniqueid}] = $obj;
							}
						}
					}
				}
			}
			
			if(isset($filtered[$obj->{$obj->uniqueid}])) {
				if($threshold <= 0 or ($threshold > 0 and $score >= $threshold)) {
					$filtered[$obj->{$obj->uniqueid}]->_score = $score;
				} else {
					unset($filtered[$obj->{$obj->uniqueid}]);
				}
			}
		}
		switch($orderby) {
			default: usort($end_filtered,array("GroupObj","sort_by_score")); break;
		}
		return $filtered;
	}
	
	public function sort_by_score($a,$b)
	{
		if(!isset($a->_score,$b->_score) or $a->_score == $b->_score) return 0;
		return ($a->_score < $b->_score) ? 1 : -1;
	}
}
