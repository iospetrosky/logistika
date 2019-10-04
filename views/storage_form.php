<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/simulator/";
?>
<script type='text/javascript'>
//var base_url = "<?php echo $bu; ?>"
var ajax_url = "<?php echo $ajax; ?>";
var last_id;

function run_local() {
    $(".good_source").draggable({
        revert: function(event,ui) {
            $(this).data("uiDraggable").originalPosition = {top:0, left:0};
            return !event;
        }
    });
    $(".good_dest").droppable({
        drop: function(event, ui) {
            //alert(ui.draggable.find('input').attr("ID"));
            var amount_to_move = prompt("What's the amount to move?", ui.draggable.find('input').val())
            if  (!amount_to_move) {
                // revert and bye
                ui.draggable.animate({top:0, left:0},500);
            } else {
                //process the request
                var id_from = ui.draggable.find('input').attr("ID").split("_")[2];
                var id_to = $(this).find('input').val();
                if ((!id_from) || (!id_to)) {
                    ui.draggable.animate({top:0, left:0},500);
                }
                $.get(base_url + "/simulator/movegoods/" + amount_to_move + "/" + id_from + "/" + id_to, function(data){
                    if (data != "OK") { 
                        ShowAlert(data,'Error','',ajax_url + "storage"); 
                    } else {
                        Nav(ajax_url + "storage");
                    }
                })
            }
            return !event;
        }
    });

    $(".act_button").mouseup(function(e) {
        //alert($(this).attr("ID"))
        var toks = $(this).attr("ID").split("_");
        last_id = toks[1];
        if (toks[0] == "SELL") {
            //display the form
            $("#sellForm").css("display","block");
        }
        if (toks[0] == "TRAVEL") {
            //display the form
            $("#travelForm").css("display","block");
        }
        if (toks[0] == "STORUNIT") {
            //submit the form
            $("#storageForm").css("display","block");
        }
    })
    $("#btn_place_order").mouseup(function(e) {
        //create a market order 
        $.get(base_url + "/simulator/createsellorder/" + last_id + "/" + $("#txt_quantity").val() + "/" + $("#txt_price").val(), function(data){
            if (data != "OK") { 
                ShowAlert(data,'Error','',ajax_url + "storage"); 
            } else {
                Nav(ajax_url + "storage");
            }
        })
    })
    $("#btn_start_travel").mouseup(function(e) {
        //last_id contains the ID of the warehouse, in this case a mean of transport
        $.get(base_url + "/simulator/begintravel/" + last_id + "/" + $("#txt_route_id").val(), function(data){
            if (data != "OK") { 
                ShowAlert(data,'Error','',ajax_url + "storage"); 
            } else {
                Nav(ajax_url + "storage");
            }
        })
    })
    $("#btn_setup_storage").mouseup(function(e) {
        //last_id contains the ID of the warehouse, in this case a mean of transport
        $.get(base_url + "/simulator/setupstorage", function(data){
            if (data != "OK") { 
                ShowAlert(data,'Error','',ajax_url + "storage"); 
            } else {
                Nav(ajax_url + "storage");
            }
        })
    })
    
    $("#btn_cancel_order").mouseup(function(e) {
        $("#sellForm").css("display","none");
    })
    $("#btn_cancel_travel").mouseup(function(e) {
        $("#travelForm").css("display","none");
    })
    $("#btn_cancel_storage").mouseup(function(e) {
        $("#storageForm").css("display","none");
    })
   
} // run_local    
    
</script>


<?php 
if ($place != "") {
    echo heading("You are in $place",3);
}
// Label, width of the input field, Read only or not, width of the column (optional)
$columns = array (
    array("ID", 50, "RO"),
    array("Place", 150, "RO"),
    array("WH", 50, "RO"),
    array("Description", 100, "RO"),
    array("Space", 90, "RO"),
    array("Good", 150, "RO"),
    array("QTY", 60, "RO", 90),
    array("Locked", 60, "RO"),
    array("Type", 90, "RO"),
    array("", 100)
);
$inner = "";
foreach($columns as $c) {
    $ww = isset($c[3])?$c[3]:$c[1];
    $inner .= div($c[0], array("style" => "width:" . $ww . "px", "class" => "row_edit_cell"));
}
echo div($inner, array("_style" => "width:1000px"));

if ($list) {
    
    foreach($list as $item) {
        $c = 0;
        /*
        echo form_open("{$bu}/simulator/method/params",
                        array("ID" => "form_" . $item->id),
                        array("row_id" => $item->id));
        */
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
            if ($f == 'id_whouse') {
                $special_class = "good_dest";
            }
            $html = form_input($data);
            if ($f == 'avail_quantity') {
                $special_class = "good_source";
                $html .= img(array('src'=>'logistika/images/pallet.png',
                                    'rel'=>'lightbox',
                                    'alt'=>'Goods',
                                    'align'=>'middle'
                                    )
                            );
            }
            $ww = isset($columns[$c][3])?$columns[$c][3]:$columns[$c][1];
            $inner .= div($html, array("style" => "width:" . $ww . "px", "class" => "row_edit_cell " . $special_class));
            $c++;
        }
        
        $but= button("Sell", array("ID" => "SELL_" . $item->id, "class" => "act_button"));
        if (($item->whtype != 'STATIC') && ($item->pname = $place)) {
            $but .= button("Travel", array("ID" => "TRAVEL_" . $item->id_whouse, "class" => "act_button"));
        }
        $inner .= div($but, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
        echo div($inner, array("id" => "line_" . $item->id, "class" => "LINE"));
        //echo form_close();
    }
}
if ($place != "") {
    $inner = button("Setup storage unit", array("ID" => "STORUNIT" , "class" => "act_button"));
    echo div($inner);
}
?>

<?php
if ($place != ""):
// if there's no place set the form is not even drawn
?>
<div class="form-popup" id="storageForm">
  <form class="form-container" autocomplete="off">
    <h3>Storage unit setup</h3>
    <?php if($warehouse): ?>
    You already have <?php echo $warehouse->capacity; ?> units of storage in <?php echo $place; ?>.
    Do you want to upgrade for 1000G?
    <?php else: ?>
    Do you want to buy your first storage point in <?php echo $place; ?> for 1000G?
    <?php endif; ?>
    <button type="button" class="btn" id="btn_setup_storage">Yes</button>
    <button type="button" class="btn cancel" id="btn_cancel_storage">Cancel</button>
  </form>
</div>    
<?php endif; ?>


<div class="form-popup" id="sellForm">
  <form class="form-container" autocomplete="off">
    <h3>Order information</h3>
    
    <label for="quantity"><b>Quantity</b></label>
    <input type="text"  name="quantity" id="txt_quantity">

    <label for="price"><b>Price x unit</b></label>
    <input type="text"  name="price" id="txt_price">

    <button type="button" class="btn" id="btn_place_order">Place order</button>
    <button type="button" class="btn cancel" id="btn_cancel_order">Cancel</button>
  </form>
</div>    
    
<div class="form-popup" id="travelForm">
  <form class="form-container" autocomplete="off">
    <h3>Travel setup</h3>
    <?php if ($routes) : ?>
    <label for="route"><b>Travel route</b></label><br/>
    <?php
        echo form_dropdown("route",$routes,"","id = txt_route_id");
    ?>
    <button type="button" class="btn" id="btn_start_travel">Departure</button>
    <?php else: ?>
    <br/>No routes available. Did you select a marketplace?
    <?php endif; ?>
    <button type="button" class="btn cancel" id="btn_cancel_travel">Cancel</button>
  </form>
</div>    
    
    
</body>
</html>    