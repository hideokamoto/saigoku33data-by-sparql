<?php
/**
 * @package saigoku33data-by-sparql
 * @version 1.0
 */
/*
Plugin Name: saigoku33data-by-sparql
Plugin URI: http://wordpress.org/plugins/test-use-sparql-for-saigoku-33/
Description: 大阪府の文化・観光もしくは名所・旧跡の施設一覧を表示させるプラグイン
Author: Hidetaka Okamoto
Version: 1.0
Author URI: http://wp-kyoto.net/
*/

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
    $html = "{$text}の施設一覧";
    $html .= '<div class="saigoku33data"><ul class="temple">loading...</ul>';
    $html .= "<script type='text/javascript'>
        jQuery(document).ready(function($){
        $.getJSON('" . get_sparql_data($text) . "',function(data) {
        $('.temple').html('');
            for(var i=0;i<36;i++){
                $('.temple').append(
                    '<li><dl><dt>施設名</dt><dd>'+data.results.bindings[i].label.value+'</dd><dt>住所</dt><dd>'+data.results.bindings[i].address.value+'</dd></dl></li>'
                    );
        }
        })
});
</script>";
    $html .= '<p><a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="クリエイティブ・コモンズ・ライセンス" style="border-width:0" src="http://ja.dbpedia.org/statics/cc_by_sa_88x31.png"></a><br><span xmlns:dct="http://purl.org/dc/terms/" href="http://purl.org/dc/dcmitype/Dataset" property="dct:title" rel="dct:type">DBpedia Japanese</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://ja.dbpedia.org" property="cc:attributionName" rel="cc:attributionURL">DBpedia Community</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons 表示 - 継承 3.0 非移植 License</a>.</p></div>';
    return $html;
}

function get_sparql_data($text){
$place = $text;
if (!empty($place)) {
    $place = esc_html($place);
} else {
    $place = ".*";
}
$sparql_base_url ="PREFIX schema:<http://schema.org/>PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>PREFIX lodosaka:<http://lodosaka.hozo.jp/>SELECT DISTINCT ?label ?address ?ku WHERE{ ?uri lodosaka:category_1 ?cat.FILTER (regex(str(?cat), '公園') || regex(str(?cat), '観光'))  ?uri schema:name ?label;  schema:address ?address;  lodosaka:ku ?ku.FILTER (regex(str(?ku), '{$place}'))}";
$sparql_base_url = urlencode($sparql_base_url);
$sparql_url = "http://db.lodosaka.jp/sparql?default-graph-uri=&query={$sparql_base_url}&format=application%2Fsparql-results%2Bjson";

    return $sparql_url;
}

?>