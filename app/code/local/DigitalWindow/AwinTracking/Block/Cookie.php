<?php
	class DigitalWindow_AwinTracking_Block_Cookie extends Mage_Core_Block_Template
	{
		public function setCookie()
		{
			$cookieDays = (int) Mage::getStoreConfig("AwinTracking_options/section_three/cookie_length");
			if (!isset($cookieDays)){
				$cookieDays = 30;
			}
			$keyParam = Mage::getStoreConfig("AwinTracking_options/section_three/param_key");
			if (!isset($keyParam)){
				$keyParam = 'source';
			}

			$param = Mage::app()->getRequest()->getParam($keyParam);

				if (isset($param))
				{
					$date_of_expiry = time() + (24*60*60*$cookieDays);
					setcookie($keyParam, $param, $date_of_expiry, "/" );
				} 
			}
			
	}
	
?>