
<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/xxx/";
?>
<script type='text/javascript'>
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
            window.location.href = base_url + "/editor/goods/del/" + toks[1]
        }
        if (toks[0] == "NEW") {
            //set a dedicated link
            window.location.href = base_url + "/editor/goods/new"
        }
    })

    $(".editable").change(function(e) {
        var id = $(this).attr("ID").split("_").pop();
        $("#line_"+id).addClass("row_edited")
    })
            
} // run_local    
    
</script>

<?php 
// https://www.codeigniter.com/userguide3/helpers/form_helper.html#
// https://www.codeigniter.com/userguide3/database/query_builder.html#updating-data
if ($list) {
    $columns = array (
        array("ID", 50),
        array("Good", 200),
        array("Type", 70),
        array("Workers", 90),
        array("Plains price", 90),
        array("Plains prod", 90),
        array("Description", 300),
        array("Produced in", 130),
        array("", 100)
    );
    $inner = "";
    foreach($columns as $c) {
        $inner .= div($c[0], array("style" => "width:" . $c[1] . "px", "class" => "row_edit_cell"));
    }
    echo div($inner);
    
    foreach($list as $item) {
        $c = 0;
        echo form_open("{$bu}/editor/goods/save",
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
            switch($f) {
                case 'gtype':
                    $html = form_dropdown($f,array("A"=>"A", "B"=>"B", "C"=>"C"),$v,$data);
                    break;
                case 'pptype_req':
                    $html = form_dropdown($f,$pptypes,$v,$data);
                    break;
                default:
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
    
    $inner = button("new", array("ID" => "NEW" , "class" => "act_button"));
    echo div($inner);
    
}
?>
    
    
    
    
</body>
</html>    