<?php
$subtype = elgg_extract('subtype', $vars);
$options = elgg_extract('options', $vars);
?>
<div class="elgg-field">
	<label><?php echo elgg_echo('admin:groups:subtypes:identifier') ?>
		<?php
		echo elgg_view('input/text', array(
			'name' => "params[$subtype][identifier]",
			'value' => elgg_extract('identifier', $options, 'groups'),
		));
		?>
</div>
<div class="elgg-field">
	<label>
		<?php
		echo elgg_view('input/checkbox', array(
			'name' => "params[$subtype][root]",
			'value' => 1,
			'checked' => (bool) elgg_extract('root', $options, 1),
		)) . elgg_echo('admin:groups:subtypes:root');
		?>
	</label>
</div>
<div class="elgg-field">
	<label><?php echo elgg_echo('admin:groups:subtypes:parents') ?>
		<?php
		$subtype_options = array();
		$subtypes = elgg_extract('subtypes', $vars, array());
		foreach ($subtypes as $s) {
			$subtype_options[$s] = elgg_echo("group:$s");
		}
		echo elgg_view('input/checkboxes', array(
			'name' => "params[$subtype][parents]",
			'value' => elgg_extract('parents', $options, array_keys($subtype_options)),
			'options' => array_flip($subtype_options),
			'default' => false,
		));
		?>
</div>
<div class="elgg-field">
	<label><?php echo elgg_echo('admin:groups:subtypes:tools') ?>
		<?php
		$tools = (array) elgg_get_config("group_tool_options");
		usort($tools, create_function('$a, $b', 'return strcmp($a->label, $b->label);'));
		$tool_opitons = array();
		foreach ($tools as $tool) {
			$tool_options[$tool->name] = $tool->label;
		}
		echo elgg_view('input/checkboxes', array(
			'name' => "params[$subtype][tools]",
			'value' => elgg_extract('tools', $options, array_keys($tool_options)),
			'options' => array_flip($tool_options),
			'default' => false,
		));
		?>
</div>
<div class="elgg-field">
	<label>
		<?php
		echo elgg_view('input/checkbox', array(
			'name' => "params[$subtype][preset_tools]",
			'value' => 1,
			'checked' => (bool) elgg_extract('preset_tools', $options, 0),
		)) . elgg_echo('admin:groups:subtypes:preset_tools');
		?>
	</label>
</div>
