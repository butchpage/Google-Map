<?php
/*
  Plugin Name: Google Map
  Plugin URI: 
  Description: Google Map shortcode
  Version: 0.0.1
  Author:
  Author URI:
  License: GPLv2
*/
/*  Copyright YEAR   PLUGIN-AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

        // custom google maps code

    // Geocode an address: return array of latiude & longitude
    function bpgm_gmap_geocode( $address ) {
      // Make Google Geocoding API URL 
      $map_url = 'http://maps.google.com/maps/api/geocode/json?address=';
      $map_url .= urlencode( $address ).'&sensor=false';

      // send GET request
      $request = wp_remote_get( $map_url );

      // get the JSON object
      $json = wp_remote_retrieve_body( $request );

      // Make sure the request was successful or return false
      if( empty( $json ) )
        return false;

      // Decode the JSON object
      $json = json_decode( $json );

      // Get coordinates
      $lat  = $json->results[0]->geometry->location->lat; // latitude
      $long = $json->results[0]->geometry->location->lng; // longitude
     
      // Return array of latitude & longitude
      return compact( 'lat', 'long' );
    }

    // Convert a plain text address into latitude & longitude coordinates
    // Retrieved from meta data if possible, or get fresh then cache otherwise
    function bpgm_gmap_get_coords( $address = '2991 E White Star Ave. Anaheim CA 92806' ) {
    // Current post id
    global $id;

    // Check if we already have this coordinates in the database
    $saved = get_post_meta( $id, 'bpgm_gmap_addresses' );
    foreach( (array) $saved as $_saved ) {
      if( isset( $_saved[ 'address' ] ) && $_saved[ 'address' ] == $address ) {
        extract( $_saved );
        return compact( 'lat', 'long' );
      }
    }

    // Coordinates not cached: let's fetch them from Google
    $coords = bpgm_gmap_geocode( $address );
    if( !$coords )
      return false;

    // Cache result in a post meta data
    add_post_meta( $id, 'bpgm_gmap_addresses', array(
        'address' => $address,
        'lat' => $coords['lat'],
        'long' => $coords['long']
      )
    );

    extract( $coords );
    return compact( 'lat', 'long' );

    }

?>