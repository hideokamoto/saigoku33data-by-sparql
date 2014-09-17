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

add_action('admin_menu', 'sparql_hooks');
add_action('save_post', 'save_sparql');
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

function sparql_hooks() {
    add_meta_box('sparql', 'Temple Name', 'sparql_input', 'post', 'normal', 'high');
    add_meta_box('sparql', 'Temple Name', 'sparql_input', 'page', 'normal', 'high');
}
function sparql_input() {
    global $post;
    echo '<input type="hidden" name="sparql_noncename" id="sparql_noncename" value="'.wp_create_nonce('custom-js').'" />';
    //echo '<textarea name="sparql-1" id="sparql" rows="5" cols="30" style="width:100%;">'.get_post_meta($post->ID,'_sparql',true).'</textarea>';
$select = '<select name="sparql" size="37" style="height:auto;">
<option value="">全件表示</option>
<option value="青岸渡寺"'. selected($select,"青岸渡寺").'>那智山寺（青岸渡寺）</option>
<option value="紀三井寺"'. selected($sparql,"紀三井寺").'>紀三井寺（護国院）</option>
<option value="粉河寺"'. selected($sparql,"粉河寺").'>粉河寺</option>
<option value="施福寺">槇尾寺（施福寺）</option>
<option value="葛井寺">藤井寺（葛井寺）</option>
<option value="青岸渡寺">壺阪寺（南法華寺）</option>
<option value="岡寺">岡寺</option>
<option value="長谷寺">長谷寺</option>
<option value="興福寺">興福寺（南円堂）</option>
<option value="三室戸寺">御室戸寺（三室戸寺）</option>
<option value="醍醐寺">上醍醐寺</option>
<option value="正法寺">岩間寺</option>
<option value="石山寺">石山寺</option>
<option value="園城寺">三井寺(園城寺)</option>
<option value="観音寺">今熊野観音寺</option>
<option value="清水寺">清水寺</option>
<option value="六波羅蜜寺">六波羅蜜寺</option>
<option value="頂法寺">六角堂</option>
<option value="行願寺">行願寺</option>
<option value="善峯寺">善峯寺</option>
<option value="穴太寺">穴太寺</option>
<option value="総持寺">総持寺</option>
<option value="勝尾寺">勝尾寺</option>
<option value="中山寺">中山寺</option>
<option value="加東市">播州清水寺</option>
<option value="一乗寺">一乗寺</option>
<option value="圓教寺">圓教寺</option>
<option value="成相寺">成相寺</option>
<option value="松尾寺">松尾寺</option>
<option value="宝厳寺">宝厳寺</option>
<option value="長命寺">長命寺</option>
<option value="観音正寺">観音正寺</option>
<option value="華厳寺">華厳寺</option>
<option value="法起院">法起院</option>
<option value="元慶寺">元慶寺</option>
<option value="菩提寺">菩提寺</option>
</select>';
echo $select;
}
function save_sparql($post_id) {
    if (!wp_verify_nonce($_POST['sparql_noncename'], 'custom-js')) return $post_id;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
    $sparql = $_POST['sparql'];
    update_post_meta($post_id, '_sparql', $sparql);
}

?>