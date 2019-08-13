<?php
class DigitalWindow_AwinTracking_Model_System_Config_Backend_FeedName extends Mage_Core_Model_Config_Data
{
	public function save()
    {
		$feedName = trim($this->getValue());
		if (preg_match("[\W+]", $feedName) || empty($feedName))
		{
			Mage::throwException("Feed name cannot be empty or contain special characters");
		}else{ 
			return parent::save();
		}
	}
}
?>