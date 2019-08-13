<?php
class DigitalWindow_AwinTracking_Block_Awintracking extends Mage_Core_Block_Template
{
    
    public function getOrder() // Get order object
    {		
        $order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
        return $order;
    }
    
    public function getAdvertiserId() // Get Advertiser Id
    {
        $merchantId = (int) Mage::getStoreConfig('AwinTracking_options/section_two/merchant_id');
        return $merchantId;
    }
    
    public function getTestMode() // Get Config Test Mode Value
    {
        $testMode = (int) Mage::getStoreConfig('AwinTracking_options/section_one/test_mode');
        return $testMode;
    }
    
    public function getCurrency() // Get Currency Code
    {
        $order         = $this->getOrder();
        $currency_code = $order->getOrderCurrency()->getCurrencyCode();
        return $currency_code;
    }
    
    public function getChannel() // Get source parameter from cookie
    {	
		
		if ((int) Mage::getStoreConfig("AwinTracking_options/section_three/enable_dedupe") == 1){
			$keyParam = Mage::getStoreConfig("AwinTracking_options/section_three/param_key");
        if (isset($keyParam)) {
            $channelParameter = filter_input(INPUT_COOKIE, $keyParam);
            if (isset($channelParameter)) {
                return $channelParameter;
            }else{
				$defaultValue = Mage::getStoreConfig("AwinTracking_options/section_three/default_value");
				return $defaultValue;
				}
			}
        }
		return 'aw';
    }
    
    public function getProducts() // Get all products from order object
    {
        $order           = $this->getOrder();
        $orderedProducts = $order->getAllVisibleItems();
        return $orderedProducts;
    }
    
    public function getGrandTotal() // Get grand total amount
    {
        $order      = $this->getOrder();
        $grandTotal = $order->getGrandTotal();
        return $grandTotal;
    }
    
    public function getShipping() // Get shipping amount
    {
        $order          = $this->getOrder();
        $shippingAmount = $order->getShippingAmount();
        return $shippingAmount;
    }
    
    public function getSubTotal() // Get Subtotal (Subtotal = GrantTotal - Shipping - Tax)
    {
        $order            = $this->getOrder();
        $totalSubShipping = $order->getSubtotal();
        return number_format($totalSubShipping, 2);
    }
    
    public function getAwinAmount() // Get amount for awin tracking (dependant on VAT variable from backend config)
    {
        $order                   = $this->getOrder();
        $includeVatInCalculation = Mage::getStoreConfig('AwinTracking_options/section_two/tax_inclusive');
        $taxAmount               = ($includeVatInCalculation == 0) ? $order->getTaxAmount() : 0;
        $amount                  = ($this->getGrandTotal() - $this->getShipping()) - $taxAmount;
        return number_format($amount, 2);
        
    }
    
    public function getLastOrderId() // Get order Id
    {
        $order       = $this->getOrder();
        $lastOrderId = $order->getIncrementId();
        return $lastOrderId;
    }
    
    public function getVoucher() // Get voucher code
    {
        $order       = $this->getOrder();
        $voucherCode = $order->coupon_code;
        return $voucherCode;
    }
    
    public function getDiscount() // Get discount amount (whole number)
    {
        $order          = $this->getOrder();
        $discountAmount = abs($order->getDiscountAmount());
        return $discountAmount;
    }
    
    public function getDiscountPercent() // Get discount percent using discount amount
    {
        $order            = $this->getOrder();
        $discountAmount   = $this->getDiscount($order);
        $totalSubShipping = $this->getSubTotal($order);
        $discountPercent  = $discountAmount / $totalSubShipping;
        return $discountPercent;
    }
    
    public function getCouponRule() // Get coupon rule (used for determining the coupon type)
    {
        $order         = $this->getOrder();
        $sessionCoupon = $this->getVoucher($order);
        $couponData    = Mage::getModel('salesrule/coupon')->load($sessionCoupon, 'code');
        $rule          = Mage::getModel('salesrule/rule')->load($couponData->getRuleId());
        return $rule;
    }
    
    public function getPLTString() // Return plt string to template
    {
        $productTracking = NULL;
        $order           = $this->getOrder();
        if ((int) Mage::getStoreConfig('AwinTracking_options/section_one/plt') == 1) { // 1 = enabled PLT | 2 = disabled PLT
            $productString   = ''; // Initialize variables
            $category        = '';
            $productPrice    = 0;
            $orderedProducts = $this->getProducts($order); // Get products, coupon rule and action from rule
            $rule            = $this->getCouponRule($order);
            $action          = $rule->getSimpleAction();
            foreach ($orderedProducts as $product) { // Implement PLT logic per order 
                $productPrice = $product->getPrice(); 
                $categoryIds  = $product->getProduct()->getCategoryIds();
                $categoryId   = end($categoryIds); // Get last category id as product category id
                $categoryName = Mage::getModel('catalog/category')->load($categoryId);
                $category     = $categoryName->getName(); // Get product category from Id
                if (isset($action)) {
                    $productTracking .= $this->getProductCoupon($action, $product, $rule, $category); // If coupon action exists 
                } else { // If coupon action does not exist
                    $productString = $this->constructPLTString($this->getAdvertiserId(), $this->getLastOrderId($order), $product->getItemId(), $product->getName(), $productPrice, $product->getQtyOrdered(), $product->getSku(), $category);
                    $productTracking .= $productString . "\n \t";
                }
            }
        }
        return $productTracking;
    }
    
    public function getProductCoupon($action, $product, $rule, $category) // Return plt string depending on coupon
    {
        $productPrice = $product->getPrice();
        switch ($action) 
        {
            case "by_percent": // If coupon is a single product percentage coupon
                $discountPercent = $this->getDiscountPercent($order);
                $productPrice    = $productPrice * (1 - $discountPercent);
                return $this->constructPLTString($this->getAdvertiserId(), $this->getLastOrderId($this->getOrder()), $product->getItemId(), $product->getName(), $productPrice, round($product->getQtyOrdered()), $product->getSku(), $category);
            case "by_fixed": // If coupon is a single product amount coupon
                $discountAmount = $rule->getDiscountAmount(); // Get discount amount from coupon code
                $productPrice   = $productPrice - $discountAmount; // Calculate Product Price
                return $this->constructPLTString($this->getAdvertiserId(), $this->getLastOrderId($this->getOrder()), $product->getItemId(), $product->getName(), $productPrice, round($product->getQtyOrdered()), $product->getSku(), $category);
            case "cart_fixed": // If coupon is a cart amount coupon
                $total              = $this->getSubTotal($order); // Get subtotal value
                $percentOfTotal     = $productPrice / $total; // Calculate product percentage from total 
                $discountForProduct = $percentOfTotal * $this->getDiscount($order); // Find product discount proportion 
                $productPrice       = $productPrice - $discountForProduct; // Subtract discount from product price
                return $this->constructPLTString($this->getAdvertiserId(), $this->getLastOrderId($this->getOrder()), $product->getItemId(), $product->getName(), $productPrice, round($product->getQtyOrdered()), $product->getSku(), $category);
            case "buy_x_get_y": // If coupon is buy x get y free coupon
                $freeProductString        = NULL; // Initialize productStrings
                $paidProductString        = NULL;
                $quantity                 = $product->getQtyOrdered(); // Get product quantity
                $productDiscountAmount    = $product->getDiscountAmount(); // Get discount amount per product
                $numberFreeProducts       = $productDiscountAmount/$productPrice; // Calculate number of free products
                $numberOfPaidProducts     = $product->getQtyOrdered() - $numberOfFreeProducts; // Calculate number of paid products
                if($numberOfFreeProducts != 0) // If number of free products is not 0 i.e > 0 
                {
                    $freeProductString    = $this->constructPLTString($this->getAdvertiserId(), $this->getLastOrderId($this->getOrder()), $product->getItemId(), $product->getName(), 0, $numberOfFreeProducts, $product->getSku(), $category);
                }
                $paidProductString        = $this->constructPLTString($this->getAdvertiserId(), $this->getLastOrderId($this->getOrder()), $product->getItemId(), $product->getName(), $productPrice, $numberOfPaidProducts, $product->getSku(), $category);
                $productString            = $paidProductString.$freeProductString;
                return $productString;
        }
    }
    
    public function constructPLTString($advertiserId, $orderRef, $productId, $productName, $productPrice, $productQuantity, $productSku, $productCategory) // Build return literal PLT string
    {
        $productString = "AW:P|" . $advertiserId . "|" . $orderRef . "|" . $productId . "|" . $productName . "|" . number_format($productPrice, 2) . "|" . $productQuantity . "|" . $productSku. "|DEFAULT|" . $productCategory . "\n \t";
        return $productString; // Return string constructed by parameters provided
    }
    
    public function getImagePixel() // Return image pixel to template
    {
        $imagePixel = NULL;
        $order      = $this->getOrder();
        if ((int) Mage::getStoreConfig('AwinTracking_options/section_one/activate_tracking_code') == 1) {
            $imagePixel = $imagePixel = '<img border="0" height="0" src="https://www.awin1.com/sread.img?tt=ns&amp;tv=2&amp;merchant=' . $this->getAdvertiserId() . '&amp;amount=' . $this->getAwinAmount($order) . '&amp;ch=' . $this->getChannel() . '&amp;cr=' . $this->getCurrency($order) . '&amp;parts=DEFAULT:' . $this->getAwinAmount($order) . '&amp;ref=' . $this->getLastOrderId($order) . '&amp;testmode=' . $this->getTestMode() . '&amp;p1=awinMagento&amp;vc=' . $this->getVoucher($order) . '" style="display: none;" width="0">' . "\n";
            return $imagePixel;
        }
        return $imagePixel;
    }
    
    public function getTracking() // Return javascript to template
    {
        $trackingCode = NULL;
        $order        = $this->getOrder();
        if ((int) Mage::getStoreConfig('AwinTracking_options/section_one/activate_tracking_code') == 1) {
            $trackingCode = '
                            var AWIN = {};
                            AWIN.Tracking = {};
                            AWIN.Tracking.Sale = {};
                            /*** Set your transaction parameters ***/
                            AWIN.Tracking.Sale.amount   =  "' . $this->getAwinAmount($order) . '";
                            AWIN.Tracking.Sale.channel  =  "' . $this->getChannel() . '";
                            AWIN.Tracking.Sale.orderRef =  "' . $this->getLastOrderId($order) . '";
                            AWIN.Tracking.Sale.parts    =  "DEFAULT:' . $this->getAwinAmount($order) . '";
                            AWIN.Tracking.Sale.currency =  "' . $this->getCurrency($order) . '";
                            AWIN.Tracking.Sale.test     =  "' . $this->getTestMode() . '";
                            AWIN.Tracking.Sale.voucher  =  "' . $this->getVoucher($order) . '";
                            AWIN.Tracking.Sale.custom   = ["awinMagento"];' . "\n";
        }
        return $trackingCode;
    }
}


