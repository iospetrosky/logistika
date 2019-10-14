
<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/xxx/";
?>
<script type='text/javascript'>
var base_url = "<?php echo $bu; ?>"
var ajax_url = "<?php echo $ajax; ?>" 


function run_local() {
    /*
    $(".act_button").mouseup(function(e) {
        //alert($(this).attr("ID"))
        var toks = $(this).attr("ID").split("_")
        if (toks[0] == "SAVE") {
            //submit the form
            $("#form_" + toks[1]).submit();
        }
        if (toks[0] == "DEL") {
            //set a dedicated link
            window.location.href = base_url + "/editor/prod_wf/del/" + toks[1]
        }
        if (toks[0] == "NEW") {
            //set a dedicated link
            window.location.href = base_url + "/editor/prod_wf/new"
        }
    })

    $(".editable").change(function(e) {
        var id = $(this).attr("ID").split("_")[1]
        $("#line_"+id).addClass("row_edited")
    })
    */
    

    $("#dd_prodpoint").change(function(e) {
        //alert($(this).val());
        //get the required materials from the DB
        //activate the create button if materials are enough
        
    })
} // run_local    
    
</script>

<?php 
$columns = array (
    array("ID", 50),
    array("Good", 120),
    array("Active", 60),
    array("Level", 60),
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
            if ($f == 'pptype') {
                $html = form_dropdown($f,$pptypes,$v,$data);
            } else {
                //$data["class"] = "editable";
                $html = form_input($data);
            }
            $inner .= div($html, array("style" => "width:" . $columns[$c][1] . "px", "class" => "row_edit_cell"));
            $c++;
        }
    }
}
$inner = button("new", array("ID" => "NEW" , "class" => "act_button", "disabled" => "disabled")) .
            form_dropdown('dd_prodpoint',$pptypes,'0', array("id"=>"dd_prodpoint"));

echo div($inner);
?>
</body>
</html>    