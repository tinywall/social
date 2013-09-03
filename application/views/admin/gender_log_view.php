 <script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.pieRenderer.min.js"></script>
 <script type="text/javascript" language="javascript"> 
 $(document).ready(function(){
    $.jqplot.config.enablePlugins = true;
	<?php $row=$gender[0];$male=$row->total;$row=$gender[1];$female=$row->total;?>
    s1 = [['male',<?php echo $male;?>], ['female',<?php echo $female;?>]];
   plot1 = $.jqplot('chart', [s1], {
        grid: {
            drawBorder: false, 
            drawGridlines: false,
            background: '#ffffff',
            shadow:false
        },
        axesDefaults: {
            
        },
        seriesDefaults:{
            renderer:$.jqplot.PieRenderer,
            rendererOptions: {
                showDataLabels: true
            }
        },
        legend: {
            show: true,
            rendererOptions: {
                numberRows: 1
            },
            location: 's'
        }
    }); 
});
</script>
<div id="chart" style="margin-top:20px; margin-left:20px; width:300px; height:300px;"></div> 