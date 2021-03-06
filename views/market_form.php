<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/simulator";
?>
<script type='text/javascript'>
var ajax_url = "<?php echo $ajax; ?>";

function run_local() {
    $(".editable").change(function(e) {
        // editable format: name_id
        var tok = $(this).attr("ID").split("_")
        $("#line_"+tok[1]).addClass("row_edited")
        //this happens for every editable and the action depends on the name
        switch(tok[0]) {
            case "price":
                $.get(ajax_url + "/updatemarketprice/" + tok[1] + "/" + $(this).val(), function(data){
                    data = JSON.parse(data)
                    
                    if (data.retcode == "OK") {
                        $(data.line).removeClass("row_edited")
                        $("#equiv_" + data.id).val(data.equiv)
                    } else {
                        ShowAlert(data.message,'Error','','')
                    }
                })
                break;
        }
    })
    
    $(".act_button").mouseup(function(e) {
        //alert($(this).attr("ID"))
        var toks = $(this).attr("ID").split("_")
        if (toks[0] == "CANCEL") {
            $.get(ajax_url + "/cancelorder/" + toks[1], function(data){
                if (data.substring(0,1) == "OK") {
                    Nav("<?php echo current_url(); ?>");
                } else {
                    ShowAlert(data,'Error','','<?php echo current_url(); ?>')
                }
            })
        }
        if (toks[0] == "BUY") {
            $("#id_market_item").val(toks[1])
            $("#buyForm").css("display","block");
        }
    })
    $("#btn_cancel_order").mouseup(function(e) {
        $("#buyForm").css("display","none");
    })

} // run_local    
    
</script>


<?php
if ($place == "") {
    // list the possible market places as a list of links
    echo "<UL>";
    foreach($list as $item) {
        echo "<LI>";
        echo "<A HREF=" . $ajax . "/marketplace/" . $item->id_place . ">" . $item->pname . "</A>";
        echo "</LI>";
    }
    echo "</UL>";
} else {
    echo heading("Market of $place &nbsp;&nbsp;&nbsp;&nbsp;[<a href='" . $ajax . "/marketplace'>Reload</a>] &nbsp;&nbsp;&nbsp;&nbsp;[<a href='{$ajax}/marketplace/-1'>Unset</a>]",3);
    $columns = array (
        array("ID", 80, "RO"),
        array("Merchant", 150, "RO"),
        array("OP", 50, "RO"),
        array("Good", 150, "RO"),
        array("QTY", 60, "RO"), // depends on the owner of the deal
        array("Price", 60, "?"),
        array("Equivalent", 200, "RO"),
        array("", 100)
    );
    $inner = "";
    foreach($columns as $c) {
        $inner .= div($c[0], array("style" => "width:" . $c[1] . "px", "class" => "row_edit_cell"));
    }
    echo div($inner, array("_style" => "width:1000px"));
    
    foreach($list as $item) {
        $c = 0;
        $inner = "";
        foreach($item as $f=>$v) {
            //skip all the values beyond the last column
            $data = array(
                "name" => $f,
                "id" => $f . "_" . $item->id,
                "value" => $v,
                "style" => "width:" . (string)($columns[$c][1]-20) . "px"
            );
            if($columns[$c][2] == "RO") {
                $data["disabled"] = "disabled";
                $data["name"] = "skip_" . $data["name"]; // so we skip when saving
            }  elseif ($columns[$c][2] == "?") {
                //it depends
                if ($item->id_player == $player) {
                    $data["class"] = "editable";
                    unset($data["disabled"]);
                } else {
                    $data["disabled"] = "disabled";
                    $data["name"] = "skip_" . $data["name"]; // so we skip when saving
                }
            } else {
                $data["class"] = "editable";
                unset($data["disabled"]);
            }
            $html = form_input($data);
            $inner .= div($html, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
            $c++;
            if(!isset($columns[$c+1])) break; // +1 because the last is the "" 
        }
        $but = "";
        if ($item->id_player == $player) {
            $but .= button("Cancel", array("ID" => "CANCEL_" . $item->id, "class" => "act_button"));
        } 
        if (($item->id_player != $player) && ($item->op_type = 'S') && ($item->gname != 'Food')) {
            $but .= button("Buy order", array("ID" => "BUY_" . $item->id, "class" => "act_button"));
        }
        $inner .= div($but, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
        echo div($inner, array("id" => "line_" . $item->id, "class" => "LINE"));
    } // foreach $item
}
?>

<!-- the form may be used to buy -->
<div class="form-popup" id="buyForm">
  <form action="<?php echo $ajax . "/buyorder"; ?>" class="form-container" autocomplete="off" method="get">
    <h3>Order information</h3>
    
    <input type="hidden" name="id_market_item" id="id_market_item" />
    <input type="hidden" name="current_player" value="<?php echo $player; ?>" />
    
    <label for="quantity"><b>Quantity</b></label>
    <input type="text"  name="quantity" id="txt_quantity">

    <label for="price"><b>Price x unit</b></label>
    <input type="text"  name="price" id="txt_price">

    <button type="submit" class="btn" id="btn_place_order">Place order</button>
    <button type="button" class="btn cancel" id="btn_cancel_order">Cancel</button>
  </form>
</div>    
    
    
    
</body>
</html>    