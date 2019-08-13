<?php
class DigitalWindow_AwinTracking_Model_System_Config_Backend_Cookie extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $cookieLength = $this->getValue();
       if($cookieLength <= 0 || !is_numeric($cookieLength) || is_float($cookieLength)){
            Mage::throwException("Cookie length not saved, value should be positive numeric. Cookie length reverted.");
        }elseif($cookieLength < 30){ 
            Mage::getSingleton('core/session')->addNotice ('Cookie period under 30 days is not recommended.');
            return parent::save();
        }else{
            return parent::save();
        }
    }
}
?>