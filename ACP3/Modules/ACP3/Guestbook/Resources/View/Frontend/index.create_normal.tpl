{extends file="asset:`$LAYOUT`"}

{block CONTENT}
    {if isset($error_msg)}
        {$error_msg}
    {/if}
    <form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8" class="form-horizontal" data-ajax-form="true" data-ajax-form-loading-text="{lang t="system|loading_please_wait"}">
        {include file="asset:System/Partials/form_group.input_text.tpl" name="name" value=$form.name required=true readonly=$form.name_disabled maxlength=20 label={lang t="system|name"}}
        {include file="asset:System/Partials/form_group.input_email.tpl" name="mail" value=$form.mail readonly=$form.mail_disabled maxlength=120 label={lang t="system|email_address"}}
        {include file="asset:System/Partials/form_group.input_url.tpl" name="website" value=$form.website readonly=$form.website_disabled maxlength=120 label={lang t="system|website"}}
        <div class="form-group">
            <label for="message" class="col-sm-2 control-label required">{lang t="system|message"}</label>

            <div class="col-sm-10">
                {if $can_use_emoticons}
                    {event name="emoticons.render_emoticons_list"}
                {/if}
                <textarea class="form-control" name="message" id="message" cols="50" rows="6" required>{$form.message}</textarea>
            </div>
        </div>
        {if isset($subscribe_newsletter)}
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                        <label for="subscribe-newsletter">
                            <input type="checkbox" name="subscribe_newsletter" id="subscribe-newsletter" value="1"{$subscribe_newsletter}>
                            {$LANG_subscribe_to_newsletter}
                        </label>
                    </div>
                </div>
            </div>
        {/if}
        {event name="captcha.event.display_captcha"}
        {include file="asset:System/Partials/form_group.submit.tpl" form_token=$form_token}
    </form>
    {javascripts}
        {include_js module="system" file="ajax-form"}
    {/javascripts}
{/block}
