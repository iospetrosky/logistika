
<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/display";
?>
<script type='text/javascript'>
//var base_url = "<?php echo $bu; ?>" //already defined above
var ajax_url = "<?php echo $ajax; ?>" 


function run_local() {
<?php 
if($action=='draw'): 
//Mark all the hexagons in the path
foreach($routepath as $item):
$html = "<div class='path_item'>" . $item->pathsequence . "</div>";
?>
$('div[name=<?php echo $item->map_tile; ?>]').addClass("inpath").html("<?php echo $html; ?>");
<?php
endforeach;
endif;
?>

<?php 
if($action=='showtransp'):
foreach($transports as $item):
?>
$('div[name=<?php echo $item->hexmap; ?>]').addClass("transports");
<?php
endforeach;
endif;
?>


<?php if($action=='draw'): ?>
//special behaviour when in DRAW mode
$(".hexagon").mouseup(function(e) {
    var toks = $(this).attr("ID").split("_");
    $.get(ajax_url + "/add_tile/" + <?php echo $path_id; ?> + "/" + "R" + toks[1] + "C" + toks[2] , 
        function(data) {
            console.log(data);
            if (data == "OK") {
                Nav("<?php echo current_url(); ?>");
            } 
    })
})

$(".act_button").mouseup(function(e) {
    //alert($(this).attr("ID"))
    var toks = $(this).attr("ID").split("_")
    if (toks[0] == "DEL") {
        //set a dedicated link
        $.get(ajax_url + "/del_tile/" + toks[1], function(data) {
            Nav("<?php echo current_url(); ?>");
        })
    }
})

$(".editable").change(function(e) {
    var tok = $(this).attr("ID").split("_")
    $("#line_"+tok[1]).addClass("row_edited")
    //this happens for every editable and the action depends on the name
    switch(tok[0]) {
        case "pathsequence":
            $.get(ajax_url + "/update_sequence/" + tok[1] + "/" + $(this).val(), function(data){
                data = JSON.parse(data)
                if (data.retcode == "OK") {
                    Nav("<?php echo current_url(); ?>");
                    //$(data.line).removeClass("row_edited")
                } else {
                    ShowAlert(data.message,'Error','','')
                }
            })
            break;
    }
})
<?php endif; ?>

<?php if($action=='showtransp'): ?>
$(".transports").mouseup(function(e) {
    var curr_id = $(this).attr("name")
    $(".map_tile_info").fadeOut(1)
    //promise... waits for all the fadeOut are completed
    $(".map_tile_info").promise().done(function() {
        $(".map_tile_info[name=TILE_"+curr_id+"]").fadeIn("slow")        
    })
})
<?php endif; ?>

//the rest does not depend on the view mode
$(".hexagon").hover(function() {
    var curr_id = $(this).attr("name")
    $("#map_tile_name").append($("<span>[" + curr_id + "]</span>"))
}, function() {
    $("#map_tile_name").find("span").last().remove()    
})



} // run_local    
    
</script>

<h3 id="map_tile_name"><span><?php echo $mapname; ?>&nbsp;&nbsp;&nbsp;&nbsp;</span></h3>
<div class="map_box">
    <div class="map_img">
        <?php
        // draw the links on the hexagons
        foreach ($tiles as $tile):
        ?>
        <div class="hexagon" style="left:<?php echo $tile->hx - floor($hex_wdt/2);?>px;top:<?php echo $tile->hy - floor($hex_hgt/2);?>px;"
            id="hex_<?php echo $tile->hrow;?>_<?php echo $tile->hcol;?>"
            name="<?php echo $tile->txtname;?>">&nbsp;</div>

        <?php endforeach; ?>
    </div>
    <div class="map_infos">
    <?php if($action=='draw'): ?>
    <?php
    $columns = array (
        array("ID", 50, "RO"),
        array("Sequence", 80, "WR"),
        array("Tile", 90, "RO"),
        array("", 60)
    );
    $inner = "";
    foreach($columns as $c) {
        $inner .= div($c[0], array("style" => "width:" . $c[1] . "px", "class" => "row_edit_cell"));
    }
    echo div($inner, array("_style" => "width:1000px"));
    foreach($routepath as $item){
        $c = 0;
        $inner = "";
        foreach($item as $f=>$v) {
            $data = array(
                "name" => $f,
                "id" => $f . "_" . $item->id,
                "value" => $v,
                "style" => "width:" . (string)($columns[$c][1]-20) . "px"
            );
            if(($columns[$c][2] == "RO") || ($item->pathsequence == 1) || ($item->pathsequence == 999)) {
                $data["disabled"] = "disabled";
                $data["name"] = "skip_" . $data["name"]; // so we skip when saving
            }  else {
                $data["class"] = "editable";
                unset($data["disabled"]);
            }
            $html = form_input($data);
            $inner .= div($html, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
            $c++;
            if ($c == 3) break; // don't display all the fields
        }
        //first and last elements can't be edited
        if (($item->pathsequence == 1) || ($item->pathsequence == 999)) {
            $but = "";
        } else {
            $but= button("Del", array("ID" => "DEL_" . $item->id, "class" => "act_button"));
        }
        $inner .= div($but, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
        echo div($inner, array("id" => "line_" . $item->id, "class" => "LINE"));
        
    } ?>
    <?php endif; // action = DRAW ?>
    <?php if($action=='showtransp'): ?>
    <?php
        foreach($transports as $item) {
            //echo "<!--" . print_r($item, true) . "-->";
            $this->table->set_caption($item->hexmap . " - " . $item->pname);
            if(isset($item->fullname)) $this->table->add_row(array('data'=>'Player','class'=>'TD_Label'),$item->fullname);
            $this->table->add_row(array('data'=>'Type','class'=>'TD_Label'),$item->whtype);
            $this->table->add_row(array('data'=>'Route','class'=>'TD_Label'),$item->description);
            if(!is_array($item->goods)) {
                $this->table->add_row(array('data'=>'Goods','class'=>'TD_Label'),'Empty cargo');
            } else {
                $this->table->add_row(array('data'=>'Goods','class'=>'TD_Label','rowspan'=>count($item->goods)),$item->goods[0]->gname . ' (' . $item->goods[0]->quantity . ')');
                for($k=1;$k<count($item->goods);$k++) {
                    $this->table->add_row($item->goods[$k]->gname . ' (' . $item->goods[$k]->quantity . ')');
                }
            }
            $html = $this->table->generate();
            $this->table->clear();
            echo div($html, array("class"=>'map_tile_info', "name"=>"TILE_" . $item->hexmap));
        }
    ?>
    <?php endif; // action = TRANSPORTS ?>
    </div>
</div>
