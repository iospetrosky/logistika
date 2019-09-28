
<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/xxx/";
?>
<script type='text/javascript'>
var base_url = "<?php echo $bu; ?>"
var ajax_url = "<?php echo $ajax; ?>" 


function run_local() {
    $(".act_button").mouseup(function(e) {
        //alert($(this).attr("ID"))
        var toks = $(this).attr("ID").split("_")
        if (toks[0] == "SAVE") {
            //submit the form
            $("#form_" + toks[1]).submit();
        }
        if (toks[0] == "DEL") {
            //set a dedicated link
            window.location.href = base_url + "/editor/wh_goods/del/" + toks[1]
        }
    })
    $(".editable").change(function(e) {
        var id = $(this).attr("ID").split("_")[1]
        $("#line_"+id).addClass("row_edited")
    })
            
} // run_local    
    
</script>


<?php 
// https://www.codeigniter.com/userguide3/helpers/form_helper.html#
// https://www.codeigniter.com/userguide3/database/query_builder.html#updating-data
if ($list) {
    $columns = array (
        array("ID", 50, "RO"),
        array("ID WH", 50, "RO"),
        array("Good", 100, "RO"),
        array("Quantity", 90, "WR"),
        array("Locked qty", 90, "WR"),
        array("Capacity", 150, "RO"),
        array("Type", 90, "RO"),
        array("Place", 150, "RO"),
        array("Player", 150, "RO"),
        array("", 100)
    );
    $inner = "";
    foreach($columns as $c) {
        $inner .= div($c[0], array("style" => "width:" . $c[1] . "px", "class" => "row_edit_cell"));
    }
    echo div($inner, array("_style" => "width:1000px"));
    
    foreach($list as $item) {
        $c = 0;
        echo form_open("{$bu}/editor/wh_goods/save",
                        array("ID" => "form_" . $item->id),
                        array("row_id" => $item->id));
        $inner = "";
        foreach($item as $f=>$v) {
            $data = array(
                "name" => $f,
                "id" => $f . "_" . $item->id,
                "value" => $v,
                "style" => "width:" . (string)($columns[$c][1]-20) . "px"
            );
            if($columns[$c][2] == "RO") {
                $data["disabled"] = "disabled";
                $data["name"] = "skip_" . $data["name"]; // so we skip when saving
            } else {
                $data["class"] = "editable";
                unset($data["disabled"]);
            }
            $html = form_input($data);
            $inner .= div($html, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
            $c++;
        }
        $but = button("save", array("ID" => "SAVE_" . $item->id, "class" => "act_button"));
        $but.= button("del", array("ID" => "DEL_" . $item->id, "class" => "act_button"));
        $inner .= div($but, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
        echo div($inner, array("id" => "line_" . $item->id, "class" => "LINE"));
        echo form_close();
    }
}
?>
    
    
    
    
</body>
</html>    