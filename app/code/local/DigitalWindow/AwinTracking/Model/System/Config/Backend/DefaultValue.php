<?php
class DigitalWindow_AwinTracking_Model_System_Config_Backend_DefaultValue extends Mage_Core_Model_Config_Data
{
public function save()
    {
        $defaultValue = $this->getValue();
       if(!ctype_alpha($defaultValue)){
            Mage::throwException("Default value not saved, value should not be empty or contain numerics. Default value reverted.");
        }else{ 
            return parent::save();
        }
    }
	}
?>