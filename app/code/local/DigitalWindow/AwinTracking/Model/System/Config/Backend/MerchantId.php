<?php
class DigitalWindow_AwinTracking_Model_System_Config_Backend_MerchantId extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $merchantId = $this->getValue();
       if($merchantId <= 0 || !is_numeric($merchantId) || is_float($merchantId)){
            Mage::throwException("Merchant ID not saved, value should be numeric. Mastertag not set.");         
        }else{ 
            return parent::save();
        }
    }
}
?>