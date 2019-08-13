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
			$defaultValue = Mage::getStoreConfig("AwinTracking_options/section_three/default_value");
			if (!isset($defaultValue)){
				$defaultValue = 'na';
			}
			$param = Mage::app()->getRequest()->getParam($keyParam);

			if ((int) Mage::getStoreConfig("AwinTracking_options/section_three/enable_dedupe") == 0)
			{
				$date_of_expiry = time() + (24*60*60*30);
				setcookie($keyParam, "aw", $date_of_expiry, "/" );
			}
			else
			{
				if (isset($param))
				{
					$date_of_expiry = time() + (24*60*60*$cookieDays);
					setcookie($keyParam, $param, $date_of_expiry, "/" );
				}
				else
				{
					$date_of_expiry = time() + (24*60*60*$cookieDays);
					setcookie($keyParam, $defaultValue, $date_of_expiry, "/" );
				}
			}
		}
	}
?>