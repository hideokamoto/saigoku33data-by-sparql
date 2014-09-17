<?php
/**
 * @package saigoku33data-by-sparql
 * @version 1.0
 */
/*
Plugin Name: saigoku33data-by-sparql
Plugin URI: http://wordpress.org/plugins/test-use-sparql-for-saigoku-33/
Description: 西国３３所のデータを表示させるプラグイン
Author: Hidetaka Okamoto
Version: 1.0
Author URI: http://wp-kyoto.net/
*/

//Here is using PHP & jQuery

add_shortcode('db-temple', 'db_temple_shortcode');
add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );


function theme_name_scripts() {
    wp_enqueue_style( 'saigoku33data', plugins_url( 'saigoku33data-style.css' , __FILE__ ) );
}

function db_temple_shortcode($place){
    $default_atts = array(
        'text' => ''
    );
    $merged_atts = shortcode_atts( $default_atts, $place);
    extract( $merged_atts);

    $html  = '<ul class="temple">loading...</ul>';
    $html .= "<script type='text/javascript'>
        jQuery(document).ready(function($){
        $.getJSON('" . get_sparql_data($text) . "',function(data) {
        $('.temple').html('');
            for(var i=0;i<36;i++){
                $('.temple').append(
                    '<li><figure><img src=" . "'+data.results.bindings[i].thumb.value+'" . "><h1>'+data.results.bindings[i].name.value+'</h1></figure><dl><dt>住所</dt><dd>'+data.results.bindings[i].address.value+'</dd><dt>説明</dt><dd>'+data.results.bindings[i].cont.value+'</dd></dl></li>'
                    );
        }
        })
});
</script>";
    return $html;
}

function get_sparql_data($text){
$place = $text;
if (!empty($place)) {
    $place = esc_html($place);
} else {
    $place = ".*";
}
$sparql_base_url ="PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>PREFIX dcterms:<http://purl.org/dc/terms/>select distinct * where {?link dcterms:subject <http://ja.dbpedia.org/resource/Category:西国三十三所>;rdfs:comment ?cont;dbpedia-owl:thumbnail ?thumb;dbpedia-owl:address ?address;rdfs:label ?name.FILTER (REGEX (?name, '{$place}'))}";
$sparql_base_url = urlencode($sparql_base_url);
$sparql_url = "http://ja.dbpedia.org/sparql?default-graph-uri=http%3A%2F%2Fja.dbpedia.org&query={$sparql_base_url}&format=application%2Fsparql-results%2Bjson&timeout=0";

    return $sparql_url;
}

?>