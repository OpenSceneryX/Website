<?php
/**
 * Add 'q' to available query string parameters
 */
function add_query_vars_filter( $vars ) {
  $vars[] = "q";
  return $vars;
}

add_filter( 'query_vars', 'add_query_vars_filter' );
