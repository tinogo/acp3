{if isset($error_msg)}
{$error_msg}
{/if}
<script type="text/javascript">
$(document).ready(function() {
	$('input[name="overlay"]').bind('click', function() {
		if ($(this).val() == 1) {
			$('#comments-container').hide();
		} else {
			$('#comments-container').show();
		}
	});

	$('input[name="overlay"]:checked').trigger('click');
});
</script>
<form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8">
	<div id="tabs">
		<ul>
			<li><a href="#tab-1">{lang t="common|general_statements"}</a></li>
			<li><a href="#tab-2">{lang t="gallery|image_dimensions"}</a></li>
		</ul>
		<div id="tab-1">
			<dl>
				<dt><label for="date-format">{lang t="common|date_format"}</label></dt>
				<dd>
					<select name="dateformat" id="date-format">
						<option value="">{lang t="common|pls_select"}</option>
{foreach $dateformat as $row}
						<option value="{$row.value}"{$row.selected}>{$row.lang}</option>
{/foreach}
					</select>
				</dd>
			</dl>
			<dl>
				<dt><label for="sidebar-entries">{lang t="common|sidebar_entries_to_display"}</label></dt>
				<dd>
					<select name="sidebar" id="sidebar-entries">
						<option>{lang t="common|pls_select"}</option>
{foreach $sidebar_entries as $row}
						<option value="{$row.value}"{$row.selected}>{$row.value}</option>
{/foreach}
					</select>
				</dd>
			</dl>
			<dl>
				<dt>
					<label for="overlay-1">{lang t="gallery|use_overlay"}</label>
					<span>({lang t="gallery|use_overlay_description"})</span>
				</dt>
				<dd>
{foreach $overlay as $row}
					<label for="overlay-{$row.value}">
						<input type="radio" name="overlay" id="overlay-{$row.value}" value="{$row.value}" class="checkbox"{$row.checked}>
						{$row.lang}
					</label>
{/foreach}
				</dd>
			</dl>
{if isset($comments)}
			<dl id="comments-container">
				<dt><label for="comments-1">{lang t="common|allow_comments"}</label></dt>
				<dd>
{foreach $comments as $row}
					<label for="comments-{$row.value}">
						<input type="radio" name="comments" id="comments-{$row.value}" value="{$row.value}" class="checkbox"{$row.checked}>
						{$row.lang}
					</label>
{/foreach}
				</dd>
			</dl>
{/if}
		</div>
		<div id="tab-2">
			<dl>
				<dt>
					<label for="thumbwidth">{lang t="gallery|thumb_image_width"}</label>
					<span>({lang t="common|statements_in_pixel"})</span>
				</dt>
				<dd><input type="number" name="thumbwidth" id="thumbwidth" value="{$form.thumbwidth}"></dd>
			</dl>
			<dl>
				<dt>
					<label for="thumbheight">{lang t="gallery|thumb_image_height"}</label>
					<span>({lang t="common|statements_in_pixel"})</span>
				</dt>
				<dd><input type="number" name="thumbheight" id="thumbheight" value="{$form.thumbheight}"></dd>
			</dl>
			<dl>
				<dt>
					<label for="width">{lang t="gallery|image_width"}</label>
					<span>({lang t="common|statements_in_pixel"})</span>
				</dt>
				<dd><input type="number" name="width" id="width" value="{$form.width}"></dd>
			</dl>
			<dl>
				<dt>
					<label for="height">{lang t="gallery|image_height"}</label>
					<span>({lang t="common|statements_in_pixel"})</span>
				</dt>
				<dd><input type="number" name="height" id="height" value="{$form.height}"></dd>
			</dl>
			<dl>
				<dt>
					<label for="maxwidth">{lang t="gallery|max_image_width"}</label>
					<span>({lang t="common|statements_in_pixel"})</span>
				</dt>
				<dd><input type="number" name="maxwidth" id="maxwidth" value="{$form.maxwidth}"></dd>
			</dl>
			<dl>
				<dt>
					<label for="maxheight">{lang t="gallery|max_image_height"}</label>
					<span>({lang t="common|statements_in_pixel"})</span>
				</dt>
				<dd><input type="number" name="maxheight" id="maxheight" value="{$form.maxheight}"></dd>
			</dl>
			<dl>
				<dt>
					<label for="filesize">{lang t="gallery|image_filesize"}</label>
					<span>({lang t="common|statements_in_byte"})</span>
				</dt>
				<dd><input type="number" name="filesize" id="filesize" value="{$form.filesize}"></dd>
			</dl>
		</div>
	</div>
	<div class="form-bottom">
		<input type="submit" name="submit" value="{lang t="common|submit"}" class="form">
		{$form_token}
	</div>
</form>