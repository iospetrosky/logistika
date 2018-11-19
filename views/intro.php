<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Logistika - config</title>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
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
    $(".close, .modal").click(function() {
        $("#myModal").fadeOut(200)
        if (redir_after_modal != '') {
            window.location.replace(redir_after_modal)
        }
    })

    if(typeof(run_local) == typeof(Function)) {
        run_local() // must be defined in the subsequent views
    }
})
</script>
<?php
    echo link_tag('logistika/libraries/main.css');
    echo link_tag('logistika/libraries/modal.css');
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
    if ($url[0] == "editor"): 
        $links = array("players" => "Players",
                       "wh_goods" => "Warehouse goods",
                       "places" => "Places",
                       "prodpoints" => "Prod. points",
                       "prod_wf" => "Prod. workflows",
                       "equivalent" => "Equivalent",
                       "goods" => "Goods");
    elseif ($url[0] == "display"):
        $links = array("majorwarehouses" => "Major warehouses",
                       "marketplace" => "Marketplaces" );
    else:
        $links = array("editor" => "Editor",
                       "display" => "Viewer" );
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



