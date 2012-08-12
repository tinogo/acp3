{if isset($sql_queries)}
<pre>
{foreach $sql_queries as $row}
<span style="color:#{$row.color}">{$row.query}</span>
{/foreach}
</pre>
{else}
{if isset($error_msg)}
{$error_msg}
{/if}
<form action="{$REQUEST_URI}" method="post" enctype="multipart/form-data" accept-charset="UTF-8" class="form-horizontal">
	<fieldset>
		<legend>{lang t="system|acp_sql_import"}</legend>
		<div class="control-group">
			<label for="text" class="control-label">{lang t="system|text"}</label>
			<div class="controls"><textarea name="text" id="text" cols="50" rows="6" class="span6">{$form.text}</textarea></div>
		</div>
		<div class="control-group">
			<label for="file" class="control-label">{lang t="system|file"}</label>
			<div class="controls"><input type="file" name="file" id="file"></div>
		</div>
	</fieldset>
	<div class="form-actions">
		<input type="submit" name="submit" value="{lang t="common|submit"}" class="btn">
		{$form_token}
	</div>
</form>
{/if}