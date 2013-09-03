 <script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.barRenderer.min.js"></script>
 <script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.categoryAxisRenderer.min.js"></script>
 <script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.canvasAxisLabelRenderer.js"></script>
 <script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.canvasTextRenderer.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.highlighter.js"></script> 
<script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.cursor.js"></script>
 <?php $row=$age[0];?>
   	
 <script type="text/javascript" language="javascript"> 
 $(document).ready(function(){
    $.jqplot.config.enablePlugins = true;
	 line1 = [<?php echo $row->male_teen;?>,<?php echo $row->male_youth;?>,<?php echo $row->male_professional;?>,<?php echo $row->male_other;?>];
     line2 = [<?php echo $row->female_teen;?>,<?php echo $row->female_youth;?>,<?php echo $row->female_professional;?>,<?php echo $row->female_other;?>];
	 line3= [<?php echo $row->male_teen+$row->female_teen;?>,<?php echo  $row->male_youth+$row->female_youth;?>,<?php echo $row->male_professional+$row->female_professional;?>,<?php echo $row->male_other+$row->female_other;?>];
	 
     plot3c = $.jqplot('chart', [line1, line2,line3], {
         legend: {
             show: true,
             location: 'nw'
         },
         title: 'Age / Gender wise Users',
		 highlighter: {
           show:true
      	 },
         seriesDefaults: {
             renderer: $.jqplot.BarRenderer,
             rendererOptions: {
                 barPadding: 6,
                 barMargin: 20
             }
         },
         series: [{
             label: 'Male'
         },
         {
             label: 'Female'
         },
		 {
             label: 'Total'
         }],
         axes: {
             xaxis: {
			 	label: 'Age Limit',
                 renderer: $.jqplot.CategoryAxisRenderer,
                 ticks: ['13 - 17', '18 - 24', '25 - 34', '35 +']
             },
             yaxis: {
			 	label: 'No. of Users',
				labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
			 	min: 0, max: 20, numberTicks:5
			 }
         }
     });
	});   
</script>
<div id="chart" style="margin-top:20px; margin-left:50px; width:360px; height:300px;"></div> 

 <script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.pieRenderer.min.js"></script>
 <script type="text/javascript" language="javascript"> 
 $(document).ready(function(){
    s1 = [['13-17',<?php echo $row->male_teen+$row->female_teen;?>],['18-24',<?php echo $row->male_youth+$row->female_youth;?>],['25-34',<?php echo $row->male_professional+$row->female_professional;?>],['35+',<?php echo $row->male_other+$row->female_other;?>]];
   plot1 = $.jqplot('chart2', [s1], {
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
<div id="chart2" style="margin-top:20px; margin-left:20px; width:300px; height:300px;"></div> 