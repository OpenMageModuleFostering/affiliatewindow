<?php
class DigitalWindow_AwinTracking_Model_System_Config_Backend_KeyParam extends Mage_Core_Model_Config_Data
{
public function save()
    {
        $keyParam = $this->getValue();
       if(!ctype_alpha($keyParam)){
	            Mage::throwException("Key parameter not saved, value should not be empty or contain numerics. Key parameter reverted.");
	        }else{ 
	            return parent::save();
	        }
    	}
	}
?>