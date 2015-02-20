<?php

class PermissionObj extends FactoryObj
{
	public function __construct($permid=null)
	{
		parent::__construct("permid","user_permissions",$permid);
	}
}
