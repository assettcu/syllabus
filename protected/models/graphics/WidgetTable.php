<?php

class WidgetTable 
{
	public $has_footer;
	public $data;
	
	public function __construct() 
	{
		$this->data = array();
		$this->has_footer = false;
	}	
	
	public function set_headers($headers)
	{
		if(!is_array($headers)) return false;
		
		$this->headers = $headers;
		
		return true;
	}
	
	public function render()
	{
		$table = "<table>";
		
		$table .= "<thead><tr>";
		foreach($this->headers as $hindex => $htext) {
			$table .= "<th>".$htext."</th>";
		}
		$table .= "</tr></thead>";
		
		$table .= "<tbody>";
		foreach($this->data as $rownum => $obj) {
			$table .= "<tr>";
			foreach($this->headers as $hindex => $htext) {
				if(is_array($obj->{$hindex})) {
					$output = implode(", ",$obj->{$hindex});
				} else {
					$output = $obj->{$hindex};
				}
				$table .= "<td class='data-item'>".$output."</td>";
			}
			$table .= "</tr>";
		}
		$table .= "</tbody>";
		
		if($this->has_footer) {
			$table .="<tfoot>";
			$table .= "</tfoot>";
		}
		
		$table .= "</table>";
		echo $table;
	}
	
	public function add_data_objects($objs)
	{
		$this->data = array_merge($this->data,(array)$objs);
	}
}
