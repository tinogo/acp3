<div id="adm-list">
	{check_access mode="link" action="users|edit_profile" uri="users/edit_profile" icon="32/edit_user" width="32" height="32"}
	{check_access mode="link" action="users|edit_settings" uri="users/edit_settings" icon="32/advancedsettings" width="32" height="32"}
</div>
{if isset($redirect_message)}
{$redirect_message}
{/if}
<form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8">
	<fieldset>
		<legend>{lang t="users|drafts"}</legend>
		{wysiwyg name="draft" value="$draft" height="250" toolbar="simple"}
	</fieldset>
	<div class="form-bottom">
		<input type="submit" value="{lang t="common|submit"}" class="form">
		<input type="reset" value="{lang t="common|reset"}" class="form">
	</div>
</form>