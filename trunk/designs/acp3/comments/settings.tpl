{if isset($error_msg)}
{$error_msg}
{/if}
<form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8">
	<fieldset>
		<legend>{lang t="guestbook|settings"}</legend>
		<dl>
			<dt><label for="date-format">{lang t="common|date_format"}</label></dt>
			<dd>
				<select name="form[dateformat]" id="date-format">
					<option value="">{lang t="common|pls_select"}</option>
{foreach $dateformat as $row}
					<option value="{$row.value}"{$row.selected}>{$row.lang}</option>
{/foreach}
				</select>
			</dd>
		</dl>
	</fieldset>
	<div class="form-bottom">
		<input type="submit" value="{lang t="common|submit"}" class="form">
		{$form_token}
	</div>
</form>