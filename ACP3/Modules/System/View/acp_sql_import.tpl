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
	<div class="form-group">
		<label for="text" class="col-lg-2 control-label">{lang t="system|text"}</label>
		<div class="col-lg-10"><textarea class="form-control" name="text" id="text" cols="50" rows="6">{$form.text}</textarea></div>
	</div>
	<div class="form-group">
		<label for="file" class="col-lg-2 control-label">{lang t="system|file"}</label>
		<div class="col-lg-10"><input type="file" name="file" id="file"></div>
	</div>
	<div class="form-group">
		<div class="col-lg-offset-2 col-lg-10">
			<button type="submit" name="submit" class="btn btn-primary">{lang t="system|submit"}</button>
			{$form_token}
		</div>
	</div>
</form>
{/if}