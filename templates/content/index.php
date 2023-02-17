<div id="aaochat-content">

<?php

if (is_array($_['data'])) {
	//echo 'Printing content';
 //echo "<br/>".$_['data']['content_api_url']."<br/>";
 echo $_['data']['aaochat_content'];
} else {
	echo "Page not found.";
}
?>

</div>
