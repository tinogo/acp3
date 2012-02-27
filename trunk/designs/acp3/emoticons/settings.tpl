{if isset($error_msg)}
{$error_msg}
{/if}
<form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8">
	<fieldset>
		<legend>{lang t="emoticons|settings"}</legend>
		<dl>
			<dt>
				<label for="width">{lang t="emoticons|image_width"}</label>
				<span>({lang t="common|statements_in_pixel"})</span>
			</dt>
			<dd><input type="number" name="form[width]" id="width" value="{$form.width}"></dd>
			<dt>
				<label for="height">{lang t="emoticons|image_height"}</label>
				<span>({lang t="common|statements_in_pixel"})</span>
			</dt>
			<dd><input type="number" name="form[height]" id="height" value="{$form.height}"></dd>
			<dt>
				<label for="filesize">{lang t="emoticons|image_filesize"}</label>
				<span>({lang t="common|statements_in_byte"})</span>
			</dt>
			<dd><input type="number" name="form[filesize]" id="filesize" value="{$form.filesize}"></dd>
		</dl>
	</fieldset>
	<div class="form-bottom">
		<input type="submit" value="{lang t="common|submit"}" class="form">
		{$form_token}
	</div>
</form>