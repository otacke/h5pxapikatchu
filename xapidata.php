<?php

namespace H5PXAPIKATCHU;

/**
 * Pseudo xAPI
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class XAPIDATA {

  private $raw;
  private $data;

  /**
   * Constructor
   */
  public function __construct( $xapi ) {
    // Change from JavaScript
    $xapi = str_replace('\"', '"', $xapi);
    $this->raw = $xapi;

  	$this->data = json_decode($xapi, true);
  }

  /**
   * Get raw xAPI data.
   */
  public function get_raw_xapi() {
    return $this->$raw
  }

  // TODO: get functions that form the output as needed
  public function get_actor() {

  }

  public function get_verb() {

  }

  public function get_object() {

  }

  public function get_result() {

  }

  // TODO: generic funtions for forming, e. g. array to string
}
