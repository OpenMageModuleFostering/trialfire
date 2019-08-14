<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 *
 * A basic track() controller to render a Trialfire.track() call.
 *
 * Outputs the call by default. Use setShouldTrack(false) to suppress the track call.
 */
class Trialfire_Tracker_Block_Track extends Mage_Core_Block_Template {

  /**
   * Template variant: track
   * The basic track template invokes Trialfire.track() with given event name and properties.
   */
  private $_shouldTrack = true;
  private $_trackName = '';
  private $_trackProperties = array();

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('trialfire/tracker/track.phtml');
  }

  public function getShouldTrack() {
    return $this->_shouldTrack;
  }

  public function setShouldTrack($shouldTrack) {
    $this->_shouldTrack = $shouldTrack;
    return $this;
  }

  public function getTrackName() {
    return $this->_trackName;
  }

  public function setTrackName($trackName) {
    $this->_trackName = $trackName;
    return $this;
  }

  public function getTrackProperties() {
    return $this->helper('trialfire_tracker')->getJsonArray($this->_trackProperties);
  }

  public function setTrackProperties($trackProperties) {
    $this->_trackProperties = $trackProperties;
    return $this;
  }

  /**
   * Template variant: track_observe
   * Adds some properties to hook into DOM events using jQuery for a conditional track.
   */
  private $_observeSelector = '';
  private $_observeEvent = 'mouseover';

  public function getObserveSelector() {
    return $this->_observeSelector;
  }

  public function setObserveSelector($observeSelector) {
    $this->_observeSelector = $observeSelector;
    return $this;
  }

  public function getObserveEvent() {
    return $this->_observeEvent;
  }

  public function setObserveEvent($observeEvent) {
    $this->_observeEvent = $observeEvent;
    return $this;
  }

}
?>