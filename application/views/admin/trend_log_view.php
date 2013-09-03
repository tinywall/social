<?php

$text = '';
foreach($status as $row){
	$text.=" ".$row->status_message;
}
function isnt_common_word($word)
{
	$word_filter = array('no', 'and', 'or', 'the','is');
	return !in_array($word, $word_filter);
}
$text = strtolower($text);
preg_match_all('~[\w-]+~', $text, $words);
$words = array_filter($words[0], 'isnt_common_word');
$words = array_count_values($words);
arsort($words);
?>
<h3>Top Trends:</h3><br/>
<?php
$keys=array_keys($words);
for($i=0;$i<10&&$i<sizeof($keys);$i++){
	echo '<b>'.($i+1)." ) ".$keys[$i]."</b><br/><br/>";
}
?>