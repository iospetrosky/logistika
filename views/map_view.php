
<?php
$bu = config_item('base_url') . '/' . config_item('index_page');
$ajax = $bu . "/xxx/";
?>
<script type='text/javascript'>
//var base_url = "<?php echo $bu; ?>" //already defined above
var ajax_url = "<?php echo $ajax; ?>" 


function run_local() {
            
} // run_local    
    
</script>

<h3><?php echo $mapname; ?></h3>
<div class="map_img">
    <!--img src="<?php echo base_url('logistika/images/island_map.png'); ?>" width="630" height="800" alt=""/-->
    <?php
    // draw the links on the hexagons
    foreach ($tiles as $tile):
    ?>
    <div class="hexagon" style="left:<?php echo $tile->hx - floor($hex_wdt/2);?>px;top:<?php echo $tile->hy - floor($hex_hgt/2);?>px;"
        id="hex_<?php echo $tile->hrow;?>_<?php echo $tile->hcol;?>"
        name="<?php echo $tile->txtname;?>">&nbsp;</div>

    <?php endforeach; ?>
</div>

