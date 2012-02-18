{if isset($error_msg)}
{$error_msg}
{/if}
<form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8">
	<fieldset>
		<legend>{lang t="users|forgot_pwd"}</legend>
		<p>
			{lang t="users|forgot_pwd_description"}
		</p>
		<dl>
			<dt><label for="nick-mail">{lang t="users|nickname_or_email"}</label></dt>
			<dd><input type="text" name="form[nick_mail]" id="nick-mail" value="{$form.nick_mail}" maxlength="120"></dd>
		</dl>
	</fieldset>
{$captcha}
	<div class="form-bottom">
		<input type="submit" value="{lang t="common|submit"}" class="form">
		<input type="reset" value="{lang t="common|reset"}" class="form">
		{$form_token}
	</div>
</form>