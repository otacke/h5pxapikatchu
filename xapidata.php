<?php

namespace H5PXAPIKATCHU;

/**
 * Pseudo xAPI
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class XAPIDATA {

  private $json;
  private $data;

  /**
   * Constructor
   */
  public function __construct( $xapi ) {
    // Change from JavaScript
    $xapi = str_replace('\"', '"', $xapi);
    $this->json = $xapi;

  	$this->data = json_decode($xapi, true);
  }

  // TODO: get functions that form the output as needed
}
