
<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/simulator";
?>
<script type='text/javascript'>
//var base_url = "<?php echo $bu; ?>"
var ajax_url = "<?php echo $ajax; ?>" 


function run_local() {
    $(".act_button").mouseup(function(e) {
        var toks = $(this).attr("ID").split("_")

        if (toks[0] == "SAVE") {
            //submit the form
            $("#form_" + toks[1]).submit();
        }
/*
        if (toks[0] == "DEL") {
            //set a dedicated link
            window.location.href = base_url + "/editor/prod_wf/del/" + toks[1]
        }
*/
        if (toks[0] == "NEW") {
            window.location.href = ajax_url + "/prodpoints/new/" + $("#dd_prodpoint").val()
        }
    })

    $(".editable").change(function(e) {
        var id = $(this).attr("ID").split("_")[1]
        $("#line_"+id).addClass("row_edited")
    })

    $("#dd_prodpoint").change(function(e) {
        //alert($(this).val());
        $("#NEW").prop("disabled", true)
        //get the required materials from the DB
        $.get(ajax_url + "/checkprodpoint/" + $("#dd_prodpoint").val(), function(data) {
            data = $.parseJSON(data)
            //put the result text in the message span
            $("#new_prodpoint").html(data.message)
            //activate the Create button if materials are enough
            if (data.result == "OK") {
                $("#NEW").prop("disabled", false)
            }
        })

    })
    
    
    $("#NEW").prop("disabled", true)
    
} // run_local    
    
</script>

<?php 
if (isset($last_message)) {
    echo div($last_message, array("class"=>"ret_message"));
}

$columns = array (
    array("ID", 50, "RO"),
    array("Good", 300, ""),
    array("Active", 80, ""),
    array("Level", 70, ""),
    array("Type", 100, "RO"),
    array("", 100)
);
$inner = "";
foreach($columns as $c) {
    $inner .= div($c[0], array("style" => "width:" . $c[1] . "px", "class" => "row_edit_cell"));
}
echo div($inner);

if ($list) {
    foreach($list as $item) {
        echo form_open($ajax . "/prodpoints/save",
                        array("ID" => "form_" . $item->id),
                        array("row_id" => $item->id));
        $c = 0;
        $inner = "";
        foreach($item as $f=>$v) {
            $data = array (
                "name" => $f,
                "id" => $f . "_" . $item->id,
                "value" => $v,
                "class" => "editable",
                "style" => "width:" . (string)($columns[$c][1]-20) . "px"
            );
            // some fields must be rendered differently
            if($columns[$c][2] == "RO") {
                $data["disabled"] = "disabled";
                $data["name"] = "skip_" . $data["name"]; // so we skip when saving
            }  else {
                $data["class"] = "editable";
                unset($data["disabled"]);
            }
            if ($f == 'id_good') {
                $html = form_dropdown($f,$goods[$item->pptype],$v,$data);
            } elseif ($f == 'active') {
                $html = form_dropdown($f,array(0=>'No', 1=>'Yes'),$v,$data);
            } else {
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
$inner = button("new", array("ID" => "NEW" , "class" => "act_button")) . "&nbsp;&nbsp;" .
            form_dropdown('dd_prodpoint',$pptypes,'0', array("id"=>"dd_prodpoint")) . 
            span("",array("id"=>"new_prodpoint"));

echo div($inner);
?>
</body>
</html>    