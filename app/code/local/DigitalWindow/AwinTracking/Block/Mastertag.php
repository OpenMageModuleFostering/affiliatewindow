<?php
class DigitalWindow_AwinTracking_Block_Mastertag extends Mage_Core_Block_Template
{
	public function getMid(){
		$merchantId = (int) Mage::getStoreConfig('AwinTracking_options/section_two/merchant_id');
		return $merchantId;
	}

	public function getJourneyTagConfig(){
		$merchantId = $this->getMid();
		if($merchantId != 0){
			$script = "<script type=\"text/javascript\" src=\"https://dwin1.com/".$merchantId.".js\"> </script>"."\n";
			return $script;
		}
		else{
			return NULL;
		}
	}
}
?>