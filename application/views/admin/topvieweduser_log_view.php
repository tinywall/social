 <script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.barRenderer.min.js"></script>
 <script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jqplot.categoryAxisRenderer.min.js"></script>
 <script type="text/javascript" language="javascript">
 $(document).ready(function(){
      $.jqplot.config.enablePlugins = true;
         line1 = [
			<?php
			$first=1;$rtoppages=array_reverse($toppages);
			foreach($rtoppages as $row){
			if(!$first){echo ",";}
				$first=0;
				echo "[".$row->total_view.",'<b>".$row->username.'</b><br/>'.$row->total_view." views']";
			}
			?>
		 ];
         plot1 = $.jqplot('chart', [line1], {
            title:'Top Viewed Users',
             seriesDefaults:{
               renderer:$.jqplot.BarRenderer, 
               rendererOptions:{
                 barWidth:25, 
                 barPadding:-25, 
                 barMargin:25, 
                 barDirection: 'horizontal',
                 varyBarColor: true
               }, 
               shadow:false
             },
             legend: {show:false},
             axes:{
                 xaxis:{
				 label: 'No of Views',
            min:0, tickOptions: {formatString: '%.0f',showGridLine: false}},
                 yaxis:{show: true, renderer: $.jqplot.CategoryAxisRenderer,
                            tickOptions: {show: true, showLabel: true},
                            showTicks: true}
                 }
         });
	});
		 
 </script>
 Most Visited pages
 <div id="chart" style="width:700px;height:450px;margin:20px;"></div> 
 
<?php
$no=1;
foreach($rtoppages as $page){
	echo ($no++).' ) <a href="'.base_url().$page->username.'">'.$page->first_name." ".$page->last_name."</a><br/>";
	
} 


?>
