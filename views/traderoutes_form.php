
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
            $("#form_" + toks[1]).submit()
        }
        if (toks[0] == "DEL") {
            //set a dedicated link
            window.location.href = base_url + "/editor/traderoutes/del/" + toks[1]
        }
        if (toks[0] == "NEW") {
            //set a dedicated link
            window.location.href = base_url + "/editor/traderoutes/new"
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
$columns = array (
    array("ID", 50),
    array("Description", 200),
    array("Start Hex", 90),
    array("End Hex", 90),
    array("Hex len", 90),
    array("Hex cost", 90),
    array("Travel type", 90),
    array("", 100)
);
$inner = "";
foreach($columns as $c) {
    $inner .= div($c[0], array("style" => "width:" . $c[1] . "px", "class" => "row_edit_cell"));
}
echo div($inner);

if ($list) {
    foreach($list as $item) {
        $c = 0;
        echo form_open("{$bu}/editor/traderoutes/save",
                        array("ID" => "form_" . $item->id),
                        array("row_id" => $item->id));
        $inner = "";
        foreach($item as $f=>$v) {
            $data = array(
                "name" => $f,
                "id" => $f . "_" . $item->id,
                "value" => $v,
                "class" => "editable",
                "style" => "width:" . (string)($columns[$c][1]-20) . "px"
            );
            // some fields must be rendered differently
            if ($f == "traveltype") {
                $html = form_dropdown($f,array("ROAD"=>"Road", "SEA"=>"Sea", "RIVER"=>"River"),$v,$data);
            } else {
                //$data["class"] = "editable";
                $html = form_input($data);
            }
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
    
$inner = button("new", array("ID" => "NEW" , "class" => "act_button"));
echo div($inner);

?>
</body>
</html>    