
<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/xxx/";
?>
<script type='text/javascript'>
var ajax_url = "<?php echo $ajax; ?>" 

function run_local() {
    $(".head_display_cell").click(function(e){
        var value = prompt("Enter a value to filter this field","")
        if(value) {
            Nav("<?php echo $bu . "/" . $url[0] . "/" . $url[1];?>/" + $(this).attr("id") + "/" + value)
        }
    })
} // run_local    
</script>

<?php
if ($list) {
    $inner = "";
    $k = 0;
    $keys = array_keys((array)$list[0]);
    //foreach($columns as $c) { 
    for($k=0; $k<count($columns); $k++) {
        $inner .= div($columns[$k][0], array("style" => "width:" . $columns[$k][1] . "px", 
                                             "class" => "head_display_cell", 
                                             "id" => $keys[$k]));
    }
    echo div($inner);
    $r = 1; // a row count to create an ID
    foreach($list as $item) {
        $c = 0;
        $inner = "";
        foreach($item as $f=>$v) {
            $inner .= div($v, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
            $c++;
        }
        echo div($inner, array("id" => "line_" . $r, "class" => "LINE"));
    }
}    
?>