<?php

class DownloadObj extends FactoryObj
{
	public function __construct($downloadid=null) 
	{
		parent::__construct("downloadid","downloads",$downloadid);
	}
	
	public function pre_save()
	{
		if(!isset($this->downloadid,$this->date_downloaded)) {
			$this->date_downloaded = date("Y-m-d H:i:s");
		}	
	}
}

?>