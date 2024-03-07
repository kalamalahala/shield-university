<?php
// Include WordPress
define( 'WP_USE_THEMES', false );
require( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

global $wpdb;
$search_term = $_GET['term'];

// sanitize
$search_term = $wpdb->esc_like( $search_term );

$query = "
    SELECT post_title
    FROM $wpdb->posts
    WHERE post_title LIKE '%$search_term%'
    AND post_type = 'article'
    AND post_status = 'publish'
    ORDER BY post_title ASC
    LIMIT 5
";

$search_results = $wpdb->get_results( $query, ARRAY_A );

$results = array();

foreach ( $search_results as $result ) {
    $results[] = array(
        'label' => $result['post_title'],
        'value' => $result['post_title'],
    );
}

// Reset the post data
wp_reset_postdata();

// Return the results as JSON
echo json_encode( $results );

// Stop WordPress from returning a 404
status_header( 200 );

// End the script
exit;