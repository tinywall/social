<script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.dateAxisRenderer.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.highlighter.js"></script> 
<script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.cursor.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.canvasTextRenderer.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.canvasAxisLabelRenderer.js"></script>
  <script type="text/javascript" language="javascript"> 
  
  $(document).ready(function(){
      $.jqplot.config.enablePlugins = true;
 
line1 = [
<?php
$first=1;
foreach($register as $row){
if(!$first){echo ",";}
	$first=0;
	echo "[".$row->timestamp."000,".$row->count."]";
}
?>
];
plot2 = $.jqplot('chart', [line1], {
    title: 'User Registration vs Time',
	highlighter: {
           show:true
       },
       cursor: {
           show: true,
           zoom: true
       },
    axes: {
        xaxis: {
			autoscale: true,
			label: 'Date of Registration',
            renderer: $.jqplot.DateAxisRenderer,
            numberTicks: 5
        },
		yaxis: {
			//autoscale: true,
			labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
			label: 'No. of Registration'
          	
        }
    },
	series:[{},{lineWidth:1, color:'#999999', showMarker:false}]
});
  });
  </script>
  <style>
  pre.code-block{
    background: #D8F4DC;
    border: 1px solid rgb(200, 200, 200);
    padding-top: 1em;
    padding-left: 3em;
    padding-bottom: 1em;
    margin-top: 1em;
    margin-bottom: 3em;
    
}
pre.code {
    background: #D8F4DC;
    border: 1px solid rgb(200, 200, 200);
    padding-top: 1em;
    padding-left: 3em;
    padding-bottom: 1em;
    margin-top: 1em;
    margin-bottom: 4em;
}
</style>
  <div id="chart" style="margin-top:20px; margin-left:20px; width:750px; height:500px;"></div>