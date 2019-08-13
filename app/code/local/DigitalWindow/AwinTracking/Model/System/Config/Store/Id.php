<?php
class DigitalWindow_AwinTracking_Model_System_Config_Store_Id
{
  /**
   * Provide available options as a value/label array
   *
   * @return array
   */
  
  /*
  public function toOptionArray()
  {
    return array(
      array('value'=>1, 'label'=>'One'),
      array('value'=>2, 'label'=>'Two'),
      array('value'=>3, 'label'=>'Three'),            
      array('value'=>4, 'label'=>'Four')                     
    );
  }*/
  public function toOptionArray()
  {
    $i = 0;
    $webArray = array();
    $websites = Mage::app()->getWebsites();
    foreach ($websites as $websiteInfo)
    {

      $webCode = $websiteInfo->getCode();
      $webName = $websiteInfo->getName();
      $webId =$websiteInfo->getId();
      $webArray[$i] = array("value"=>$webId, "label"=>$webName);
      $i++;
    }
    return $webArray;
  }
}