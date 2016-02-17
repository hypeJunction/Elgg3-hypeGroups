<div>
	<label><?php echo elgg_echo('admin:groups:subtypes:name') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'subtype',
	));
	?>
</div>
<div class="elgg-foot">
	<?php
	echo elgg_view('input/submit');
	?>
</div>