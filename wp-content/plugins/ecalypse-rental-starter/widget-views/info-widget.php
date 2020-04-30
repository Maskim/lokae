<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<div class="service">

	<h3 class="small">Our company</h3>
	<?php $data = unserialize($info); ?>
	<p>
		<?= $data['name'] ?><br />
		<?= $data['phone'] ?><br />
		<?= $data['email'] ?>
	</p>
	<!--
	<?php print_r($data); ?>
	//-->
	
	
</div>