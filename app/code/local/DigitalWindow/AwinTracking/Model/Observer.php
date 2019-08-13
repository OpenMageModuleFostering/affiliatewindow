<?php

class DigitalWindow_AwinTracking_Model_Observer {
    public function fireTracking(Varien_Event_Observer $observer){

        $feedName = Mage::getStoreConfig("AwinTracking_options/section_two/feed_name");
        $scheduledTime = Mage::getStoreConfig("AwinTracking_options/section_two/my_date");
        Mage::log(
                "FiredTrack", null, 'sale-logs.log'
        );
    }

    public function schedule()
    {
        $_product = array();
        $product_data = array();
        $arr = array();
        $web_id = Mage::getStoreConfig('AwinTracking_Datafeed/section_one/website');
        $fileName = Mage::getStoreConfig('AwinTracking_Datafeed/section_one/feed_name');
        $fileName = $fileName.'.csv';
        $collection = Mage::getModel('catalog/product')->getCollection()->addWebsiteFilter($web_id)->addAttributeToSelect('*');
        $collection->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        foreach ($collection as $product) 
        {
            $stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $isInStock = $stock_item->getIsInStock();
            $product_data["pid"]=$product->getId();
            $product_data['qty'] = $stock_item->getQty();;
            if ($product_data['qty'] > 0)
            {
                $product_data['stock_status']=1;
            }else {
                $product_data['stock_status']=0;
            }
            if ($product->isSalable() == 1)
            {
                $product_data['isforsale']=1;
            }else {
                $product_data['isforsale']=0;
            }
            $product_data["product_name"]=$product->getName();
            $product_data['product_description']=$product->getDescription();
            $product_data["price"]=$product->getPrice();
            $product_data['store_price']=$product->getFinalPrice();
            $categoryIds = $product->getCategoryIds();
            if (count($categoryIds))
            {
                $firstCategoryId = $categoryIds[0];
                $_category = Mage::getModel('catalog/category')->load($firstCategoryId);
                $product_data['category'] = $_category->getName();
            }else {
                $product_data['category'] = "";
            }
		
			try{
				if($product->getImageUrl()){
				$product_data['image_url'] = $product->getImageUrl();
			}
			}catch(Exception $e){
				$product_data['image_url'] = "No Image found in Magento Catalogue.";
			}
			

            $product_data['product_url']=str_replace('pr_magento_exporter.php/','',$product->getProductUrl());
            array_push($arr, $product_data);
        }
        $headers['product_id'] = 'product_id';
        $headers['quantity'] = 'quantity';
        $headers['in_stock'] = 'in_stock';
        $headers['isforsale'] = 'is_for_sale';
        $headers['product_name'] = 'product_name';
        $headers['description'] = 'description';
        $headers['price'] = 'price';
        $headers['store_price'] = 'store_price';
        $headers['merchant_category'] = 'merchant_category';
        $headers['image_url'] = 'image_url';
        $headers['product_url'] = 'product_url'; 
        $file = fopen($fileName, 'w');
        fputcsv($file, $headers);
        foreach ($arr as $line) 
        {
            fputcsv($file, $line);
        }
        fclose($file);
    }
}
?>