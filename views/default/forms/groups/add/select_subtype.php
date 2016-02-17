<?php

$identifier = elgg_extract('identifier', $vars, 'groups');
?>
<div>
	<label><?php echo elgg_echo("$identifier:add:select_subtype") ?></label>
	<?php
	echo elgg_view('input/groups/subtype', $vars);
	?>
</div>
<div class="elgg-foot">
	<?php
	echo elgg_view('input/submit', array(
		'value' => elgg_echo('groups:add:select_subtype:continue'),
	));
	?>
</div>