<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>
    <?php
    if (isset($page_title)) {
        echo $page_title;
    } else {
        echo "Logistika";
    }
    ?>
    </title>

<!--script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script-->
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="<?php echo config_item('base_url'); ?>/logistika/libraries/cookies.js"></script>
<script src="<?php echo config_item('base_url'); ?>/logistika/libraries/utils.js"></script>

<script type="text/javascript">
var base_url = "<?php echo config_item('index_page_url') ; ?>"
var redir_after_modal = ''

function ShowAlert(atext, atitle = 'Warning', afooter = '', redir = '') {
    redir_after_modal = redir
    $(".modal-header h2").text(atitle)
    $(".modal-body").html(atext)
    $(".modal-footer h3").text(afooter)
    $("#myModal").fadeIn(200)
}


$(document).ready(function () {
    // do global stuff
    $("input").attr("autocomplete", "off"); // no autocomplete on every page

    $(".close, .modal").click(function() {
        $("#myModal").fadeOut(200)
        if (redir_after_modal != '') {
            window.location.replace(redir_after_modal)
        }
    })

    if(typeof(run_local) == typeof(Function)) {
        run_local() // must be defined in the subsequent views
    }
<?php
// Pagination plugin
if ((isset($page)) && (isset($page_title))) : 
?>
    //manage of the pagination buttons (if they exist)
    $(".paginators").click(function() {
        //ID = NAVPAGE_number
        var toks = $(this).attr("ID").split("_")
        setCookie("<?php echo $cookyname; ?>", toks[1],1);
        window.location.replace("<?php echo $naked_url; ?>")
    })
<?php endif; ?>    
    
})
</script>
<?php
echo link_tag('logistika/libraries/main.css');
echo link_tag('logistika/libraries/modal.css');
echo link_tag('logistika/libraries/popup_form.css');
if (strpos(uri_string(),"/map") !== false):
    //the map view so load the specific CSS
    echo link_tag('logistika/libraries/map.css');
?>
<style>
.map_img {
    background-image: url('<?php echo base_url("logistika/images/{$mapname}.png"); ?>');
    width: <?php echo $map_wdt; ?>px;
    height: <?php echo $map_hgt; ?>px;
    position: relative;
}  
.hexagon {
    _border: 1px solid black;
    width: <?php echo $hex_wdt; ?>px;
    height: <?php echo $hex_hgt; ?>px;
    position: absolute;
}
.hexagon:hover {
    background-image: url('<?php echo base_url("logistika/images/hexagon_red2.png"); ?>');
    background-size: <?php echo $hex_wdt; ?>px;
    _background-color: red;
    cursor: pointer;
}
.inpath {
    background-image: url('<?php echo base_url("logistika/images/hexagon_green2.png"); ?>');
    background-size: <?php echo $hex_wdt; ?>px;
}
.transports {
    background-image: url('<?php echo base_url("logistika/images/hexagon_green2.png"); ?>');
    background-size: <?php echo $hex_wdt; ?>px;
}

</style>
<?php
endif;

if (strpos(uri_string(),"/storage") !== false):
?>
<style>
.good_source {
    cursor: grab;
}
</style>
<?php
endif;
?>


</head>
<body>
<!-- The Modal -->
<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">&times;</span>
      <h2>-</h2>
    </div>
    <div class="modal-body">
    </div>
    <div class="modal-footer">
      <h3>&nbsp;</h3>
    </div>
  </div>
</div>
<h1>Test</h1>
<div class="menubuttons">
<button onclick="Nav('<?php echo config_item('index_page_url') ; ?>')">Home</button>

<?php 
    //the index of the array is the name of a function in the corresponding controller
    if ($url[0] == "editor"): 
        $links = array("players" => "Players",
                       "wh_goods" => "Warehouse goods",
                       "places" => "Places",
                       "prodpoints" => "Prod. points",
                       "prodptmajors" => "Prod. points majors",
                       "prod_wf" => "Prod. workflows",
                       "equivalent" => "Equivalent",
                       "goods" => "Goods",
                       "traderoutes" => "Trade routes",
                       "items" => "Items");
    elseif ($url[0] == "display"):
        $links = array("majorwarehouses" => "Major warehouses",
                       "marketplace" => "Marketplaces",
                       "map" => "Map");
    elseif ($url[0] == "simulator"):
        $links = array( "marketplace" => "Marketplace",
                        "storage" => "Storage",
                        "fleet" => "Fleet manager",
                        "prodpoints" => "Prod. points"
                        );
    else:
        $links = array("editor" => "Editor",
                       "display" => "Viewer",
                       "simulator" => "Sym" );
    endif;

    foreach($links as $link=>$text) {
        echo "<button ";
        if (isset($url[1])) if ($url[1] == $link) echo "class=current ";
        if (isset($url[0])) { $kk = $url[0] . "/";} else { $kk = ""; }
        echo "onclick=Nav('" . config_item('index_page_url') . "/" . $kk . $link . "')>";
        echo $text;
        echo "</button>";
    }
?>
</div>

<?php
// Pagination plugin
if (isset($page)) {
    //it means all the other stuff is set also
    for($xb=1;$xb<=$page;$xb++) {
        echo button($xb, array("ID" => "NAVPAGE_" . $xb, "class" => "paginators"));
    }
    if (!$last_page) echo button("next", array("ID" => "NAVPAGE_" . strval($xb), "class" => "paginators"));
}   
?>
