<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/simulator/";
?>
<script type='text/javascript'>
//var base_url = "<?php echo $bu; ?>"
var ajax_url = "<?php echo $ajax; ?>";
var last_id;

function run_local() {
    $(".act_button").mouseup(function(e) {
        //alert($(this).attr("ID"))
        var toks = $(this).attr("ID").split("_");
        last_id = toks[1];
        if (toks[0] == "CANCEL") {
            $.get(ajax_url + "cancelroute/" + last_id, function(data) {
                if (data != "OK") { 
                    ShowAlert(data,'Error','',ajax_url + "fleet"); 
                } else {
                    Nav(ajax_url + "fleet");
                }
            })
        }
        if(toks[0] == "NEW") {
            $("#transportForm").css("display","block");
            
        }
    })

    $("#btn_create").mouseup(function(e) {
        //create a market order 
        $.post(ajax_url + "createtransport/",$("#newitem_form").serialize(), function(data){
            if (data != "OK") { 
                ShowAlert(data,'Error','',ajax_url + "fleet"); 
            } else {
                Nav(ajax_url + "fleet");
            }
        })
    })

    $("#btn_cancel").mouseup(function(e) {
        $("#sellForm").css("display","none");
    })

} // run_local    
    
</script>


<?php 
//id,description,traveltype,mov_points,curr_points,hexmap,current_location"
$columns = array (
    array("ID", 50, "RO"),
    array("Type", 70, "RO"),
    array("Route description", 180, "RO"),
    array("Route type", 80, "RO"),
    array("MP x Turn", 90, "RO"),
    array("Current MP", 90, "RO"),
    array("HEX map", 80, "RO"),
    array("Location", 100, "RO"),
    array("Status",70,"RO"),
    array("", 120)
);
$inner = "";
foreach($columns as $c) {
    $inner .= div($c[0], array("style" => "width:" . $c[1] . "px", "class" => "row_edit_cell"));
}
echo div($inner, array("_style" => "width:1000px"));

if ($list) {
    foreach($list as $item) {
        $c = 0;
        $inner = "";
        foreach($item as $f=>$v) {
            $special_class = ""; // used for draggable/droppable
            $data = array(
                "name" => $f,
                "id" => $f . "_" . $item->id,
                "value" => $v,
                "style" => "width:" . (string)($columns[$c][1]-20) . "px"
            );
            if($columns[$c][2] == "RO") {
                $data["disabled"] = "disabled";
                $data["name"] = "skip_" . $data["name"]; // so we skip when saving
            }  else {
                $data["class"] = "editable";
                unset($data["disabled"]);
            }
            if($columns[$c][0]=="Status") {
                $data["value"]=($v?"Home":"Away"); //curioso che scriva uno Zero
            }
            $html = form_input($data);
            $inner .= div($html, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell " . $special_class));
            $c++;
        }
        
        $but= button("Cancel route", array("ID" => "CANCEL_" . $item->id, "class" => "act_button"));
        $inner .= div($but, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
        echo div($inner, array("id" => "line_" . $item->id, "class" => "LINE"));
        //echo form_close();
    }
}
if($current_location = get_cookie("market_id")) {
    $inner = button("new", array("ID" => "NEW" , "class" => "act_button"));
    echo div($inner);
} else {
    $current_location = 0;
}

?>


<div class="form-popup" id="transportForm">
  <form class="form-container" autocomplete="off" id="newitem_form">
    <h3>Order information</h3>
    
    <label for="whtype"><b>Type</b></label>
    <select name="whtype" id="txt_whtype">
      <option value="SHIP">Ship</option>
      <option value="WHEEL">Any wheel</option>
    </select>
    <br>
    <label for="capacity"><b>Capacity</b></label><br>
    <input type="text"  name="capacity" id="txt_capacity">

    <label for="mov_points"><b>Mov. points</b></label>
    <input type="text"  name="mov_points" id="txt_mov_points">

    <button type="button" class="btn" id="btn_create">Create</button>
    <button type="button" class="btn cancel" id="btn_cancel">Cancel</button>
  </form>
</div>    
    
    
    
</body>
</html>    