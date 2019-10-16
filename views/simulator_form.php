
<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/xxx/";
?>
<script type='text/javascript'>
var ajax_url = "<?php echo $ajax; ?>" 

function run_local() {
    $("#btn_activate").mouseup(function(e) {
        setCookie("current_id",$("#player_id").val(),100)
        Nav(base_url + "/simulator")
    })
} // run_local    
    
</script>

<?php
if ($current_id) {
    echo "<H2>Interpreting $current_player - id: $current_id</h2>";
} else {
    echo "<H2>No current player selected</h2>";
}
echo "<hr />";
echo "<h3>Select the player to impersonate</h3>";
// The selection form is always diplayed in order to change player
echo form_dropdown('player_id',$players_list,'',array('id'=>'player_id'));
echo button("Activate", array("ID" => "btn_activate" , "class" => "act_button"));
?>