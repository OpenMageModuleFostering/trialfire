<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 *
 * A basic identify() controller to render a Trialfire.identify() call.
 *
 * Does not output the call by default. Use setShouldIdentify(true) to output the identify call.
 */
class Trialfire_Tracker_Block_Identify extends Mage_Core_Block_Template {

  private $_shouldIdentify = false;
  private $_userId = null;
  private $_userTraits = array();

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('trialfire/tracker/identify.phtml');
  }

  public function getShouldIdentify() {
    return $this->_shouldIdentify;
  }

  public function setShouldIdentify($shouldIdentify) {
    $this->_shouldIdentify = $shouldIdentify;
    return $this;
  }

  public function getUserId() {
    return is_null($this->_userId) ? 'null': "'{$this->_userId}'";
  }

  public function setUserId($userId) {
    $this->_userId = $userId;
    return $this;
  }

  public function getUserTraits() {
    return $this->helper('trialfire_tracker')->getJsonArray($this->_userTraits);
  }

  public function setUserTraits($userTraits) {
    $this->_userTraits = $userTraits;
    return $this;
  }

}
?>