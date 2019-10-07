
<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/xxx/";
?>
<script type='text/javascript'>
//var base_url = "<?php echo $bu; ?>"
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
            window.location.href = base_url + "/editor/prodpoints/del/" + toks[1]
        }
        if (toks[0] == "NEW") {
            window.location.href = base_url + "/editor/prodpoints/new"
        }
        if (toks[0] == "NEWMAT") {
            window.location.href = base_url + "/editor/prodpoints/newmat/" + toks[1]
        }
        
    })

    $(".editable").change(function(e) {
        var id = $(this).attr("ID").split("_")[1]
        $("#line_"+id).addClass("row_edited")
    })
    
    $(".editable2").change(function(e) {
        var id = $(this).attr("ID").split("@")[1]
        var fld = $(this).attr("ID").split("@")[0]
        //fire and forget
        $.get(base_url + "/FE/A/" + id, 
                    {'table'   : 'prodpoint_reqmaterials', 
                        'field'  : fld, 
                        'newval' : $(this).val()
                    })
    })
} // run_local    
    
</script>

<?php 
if ($list) {
    $columns = array (
        array("ID", 50),
        array("Prod. point type", 180),
        array("Conversion cost", 70),
        array("Materials needed to build", 200),
        array("", 100)
    );
    $inner = "";
    foreach($columns as $c) {
        $inner .= div($c[0], array("style" => "width:" . $c[1] . "px", "class" => "row_edit_cell"));
    }
    echo div($inner);
    
    foreach($list as $item) {
        $c = 0;
        echo form_open("{$bu}/editor/prodpoints/save",
                        array("ID" => "form_" . $item->id),
                        array("row_id" => $item->id));
        $inner = "";
        foreach($item as $fld=>$val) {
            $fldformat = array(
                "name" => $fld,
                "id" => $fld . "_" . $item->id,
                "value" => $val,
                "class" => "editable",
                "style" => "width:" . (string)($columns[$c][1]-20) . "px"
            );
            switch($fld) {
                case 'mat_needed':
                    $in2 = "";
                    $html = "";
                    foreach($val as $it2) {
                        foreach($it2 as $f2=>$v2) {
                            $fldformat = array(
                                "name" => $f2,
                                "id" => $f2 . "@" . $it2->id,
                                "value" => $v2,
                                "class" => "editable2",
                                "style" => "width:80px"
                            );
                            switch($f2) {
                                case 'mat_id':
                                    $html .= form_dropdown($f2,$goods,$v2,$fldformat);
                                    break;
                                case 'quantity':
                                    $html .= form_input($fldformat);
                                    break;
                                default:
                                    //skip field
                            }
                        }
                    }
                    //put a new button
                    $html .= button("new mat", array("ID" => "NEWMAT_" . $item->id, "class" => "act_button"));
                    break;
                default:
                    $html = form_input($fldformat);
            }
            $inner .= div($html, array("style" => ";vertical-align:top; width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
            $c++;
        }
        $but = button("save", array("ID" => "SAVE_" . $item->id, "class" => "act_button"));
        $but.= button("del", array("ID" => "DEL_" . $item->id, "class" => "act_button"));
        $inner .= div($but, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
        echo div($inner, array("id" => "line_" . $item->id, "class" => "LINE", "style" => "border-bottom:1px dotted black"));
        echo form_close();
    }
    
    $inner = button("new", array("ID" => "NEW" , "class" => "act_button"));
    echo div($inner);
    
}
?>
    
    
    
    
</body>
</html>    