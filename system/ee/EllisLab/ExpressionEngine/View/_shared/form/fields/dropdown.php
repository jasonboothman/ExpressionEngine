<?php
$too_many = 8;

$component = [
	'name' => $field_name,
	'items' => $choices,
	'selected' => $value,
	'disabled' => isset($disabled) ? $disabled : FALSE,
	'tooMany' => $too_many,
	'filterUrl' => isset($filter_url) ? $filter_url : NULL,
	'limit' => isset($limit) ? $limit : 100,
	'groupToggle' => isset($group_toggle) ? $group_toggle : NULL,
	'emptyText' => $empty_text,
	'noResults' => isset($no_results['text']) ? lang($no_results['text']) : NULL
];

?>
<div data-dropdown-react="<?=base64_encode(json_encode($component))?>" data-input-value="<?=$field_name?>">
	<div class="fields-select-drop">
		<div class="field-drop-selected">
			<label>
				<i><?=$empty_text?></i>
			</label>
		</div>
		<div class="field-drop-choices">
			<div class="field-inputs">
				<label class="field-loading">
					<?=lang('loading')?><span></span>
				</label>
			</div>
		</div>
	</div>
</div>
