<?php

namespace H5PXAPIKATCHU;

/**
 * Pseudo xAPI
 * The goal of this class is not to comply to the xAPI specification, but to
 * create output that's useful for the user.
 *
 * https://github.com/adlnet/xAPI-Spec/blob/master/xAPI-Data.md
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

    error_log( print_r( $this->get_actor(), true ) );
  }

  /**
   * Get raw xAPI data.
   */
  public function get_raw_xapi() {
    return $this->$raw;
  }

  /**
   * Get flattened actor data from xAPI statement.
   */
  public function get_actor() {
    if ( ! array_key_exists( 'actor', $this->data ) ) {
      return '';
    }
    $actor = $this->data['actor'];

    $object_type = ( array_key_exists( 'objectType', $actor ) ) ? $actor['objectType'] : '';
    $inverse_functional_identifier = $this->flatten_inverse_functional_identifier ( $actor );
    $name = ( array_key_exists( 'name', $actor )) ? $actor['name'] : '';
    $members = ( array_key_exists( 'member', $actor) ) ? $this->flatten_members( $actor['member'] ) : '';

    // Identified Group or Anonymous Group (we don't need to distinguish here)
    if ( $object_type === 'Group' ) {
      $name = ($name !== '') ? $name . ' (' . __( 'Group' , 'H5PXAPIKATCHU' ) . ')' : $name;
    }

    //Agent
    if ( $object_type === 'Agent' || $object_type === '') {
      // Not really neccessary, but according to xAPI specs agents have no member data
      $members = '';
    }

    return [
      'inverse_functional_identifier' => $inverse_functional_identifier,
      'name' => $name,
      'members' => $members
    ];
  }

  public function get_verb() {
    // id = DBID
    // [display] => extract language code
    // USE display (id)
  }

  public function get_object() {
    // id = DBID
    // [activity] = USE [name][: description] extract language code (id)
    // [choices] => should be evaluated
    // [correctResponsesPattern] => should be evaluated ...
    //
  }

  public function get_result() {
    // id = AUTO_INCREMENT
    // [response]
    // [score raw]
    // [score scaled]
    // USE score_raw [(score scaled %)]
    // [completed]
    // [success]
    // [duration]
  }

  /**
   * Flatten xAPI member object.
   */
  private function flatten_members ( $members ) {
    if ( ! is_array( $members ) || empty( $members ) ) {
      return '';
    }

    $output = array();
    foreach ($members as $member) {
      array_push( $output, $this->flatten_agent( $member ) );
    }
    return implode( $output, ', ' );
  }

  /**
   * Flatten xAPI agent object.
   */
  private function flatten_agent ( $agent ) {
    if ( ! is_array( $agent ) || empty( $agent ) ) {
      return '';
    }

    $name = ( array_key_exists( 'name', $agent ) ) ? $agent['name'] : '';
    $ifi = $this->flatten_inverse_functional_identifier( $agent );

    if ( $name !== '' && $ifi !== '' ) {
      $name = ' (' . $name . ')';
    }

    return $ifi . $name ;
  }

  /**
   * Flatten xAPI InverseFunctionalIdentifier object.
   */
  private function flatten_inverse_functional_identifier ( $actor ) {
    if ( ! is_array( $actor ) || empty( $actor ) ) {
      return '';
    }

    $inverse_functional_identifier = array();
    if ( array_key_exists( 'mbox', $actor ) ) {
      array_push( $inverse_functional_identifier, __( 'email', 'H5PXAPIKATCHU' ) . ': ' . $actor['mbox'] );
    }
    if ( array_key_exists( 'mbox_sha1sum', $actor ) ) {
      array_push( $inverse_functional_identifier, __( 'email hash', 'H5PXAPIKATCHU' ) . ': ' . $actor['mbox_sha1sum'] );
    }
    if ( array_key_exists( 'openid', $actor ) ) {
      array_push( $inverse_functional_identifier, __( 'openid', 'H5PXAPIKATCHU' ) . ': ' . $actor['openid'] );
    }
    if ( array_key_exists( 'account', $actor ) ) {
      array_push( $inverse_functional_identifier, __( 'account', 'H5PXAPIKATCHU' ) . ': ' . $this->flatten_account( $actor['account'] ) );
    }
    return ( empty( $inverse_functional_identifier ) ) ? '' : implode( $inverse_functional_identifier, ', ' );
  }

  /**
   * Flatten xAPI account object.
   */
  private function flatten_account ( $account ) {
    if ( ! is_array( $account ) || empty( $account ) ) {
      return '';
    }

    $name = ( array_key_exists( 'name', $account ) ) ? $account['name'] : '';
    $homepage = ( array_key_exists( 'homePage', $account ) ) ? $account['homePage'] : '';

    if ( $name !== '' && $homepage !== '' ) {
      $homepage = ' (' . $homepage . ')';
    }

    return $name . $homepage;
  }
}
