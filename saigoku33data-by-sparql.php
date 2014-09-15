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

add_action('admin_menu', 'sparql_hooks');
add_action('save_post', 'save_sparql');
add_action('wp_footer','insert_custom_js');
add_shortcode('db-temple', 'db_temple_shortcode');

function db_temple_shortcode(){
    return '<ul class="temple">loading...</ul>';
}

function get_sparql_data(){

$place = get_post_meta(get_the_ID(), '_sparql', true);
if (!empty($place)) {
    $place1 = esc_html($place);
    $place = $place1;
} else {
    $place = ".*";
}
$sparql_base_url ="PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>PREFIX dcterms:<http://purl.org/dc/terms/>select distinct * where {?link dcterms:subject <http://ja.dbpedia.org/resource/Category:西国三十三所>;rdfs:comment ?cont;dbpedia-owl:thumbnail ?thumb;dbpedia-owl:address ?address;rdfs:label ?name.FILTER (REGEX (?name, '{$place}'))}";
$sparql_base_url = urlencode($sparql_base_url);
$sparql_url = "http://ja.dbpedia.org/sparql?default-graph-uri=http%3A%2F%2Fja.dbpedia.org&query={$sparql_base_url}&format=application%2Fsparql-results%2Bjson&timeout=0";

    return $sparql_url;
}

function sparql_hooks() {
    add_meta_box('sparql', 'SPARQL QUERY', 'sparql_input', 'post', 'normal', 'high');
    add_meta_box('sparql', 'SPARQL QUERY', 'sparql_input', 'page', 'normal', 'high');
}
function sparql_input() {
    global $post;
    echo '<input type="hidden" name="sparql_noncename" id="sparql_noncename" value="'.wp_create_nonce('custom-js').'" />';
    echo '<textarea name="sparql" id="sparql" rows="5" cols="30" style="width:100%;">'.get_post_meta($post->ID,'_sparql',true).'</textarea>';
}
function save_sparql($post_id) {
    if (!wp_verify_nonce($_POST['sparql_noncename'], 'custom-js')) return $post_id;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
    $sparql = $_POST['sparql'];
    update_post_meta($post_id, '_sparql', $sparql);
}

function insert_custom_js() {
    if (is_page() || is_single()) {
        if (have_posts()) : while (have_posts()) : the_post();
            echo "<script type='text/javascript'>
jQuery(document).ready(function($){
        $.getJSON('" . get_sparql_data() . "',function(data) {
        $('.temple').html('');
            for(var i=0;i<36;i++){
                $('.temple').append(
                    '<li><img src=" . "'+data.results.bindings[i].thumb.value+'" . "><h1>'+data.results.bindings[i].name.value+'</h1><dl><dt>住所</dt><dd>'+data.results.bindings[i].address.value+'</dd><dt>説明</dt><dd>'+data.results.bindings[i].cont.value+'</dd></dl></li>'
                    );
        }
        })
});
</script>";
        endwhile; endif;
        rewind_posts();
    }
}
?>