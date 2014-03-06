<?php
require_once dirname(__FILE__) . "/../base/ValidateBase.php";

class IsRequired extends ValidateBase
{
	public function init()
	{
		$this->setMessage("{DISPLAYNAME} is certainly {INPUT}_{wada}_{__FUSINO_AJAXID}");
	}

	public function main($value)
	{
		$this->addParam("wada", "test");
		return false;
	}

	public function release()
	{
		
	}
}
?>
