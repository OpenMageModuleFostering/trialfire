<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 */
class Trialfire_Tracker_Block_Event_Catalog_Category_View extends Trialfire_Tracker_Block_Track {

  protected function _construct() {
    parent::_construct();

    $category = Mage::registry('current_category');
    
    $this->setTrackName('Viewed Category');
    $this->setTrackProperties(array(
      'id' => $category->getId(),
      'name' => $category->getName()
    ));
  }

}
?>