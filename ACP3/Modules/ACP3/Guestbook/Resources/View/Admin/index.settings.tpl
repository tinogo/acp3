{extends file="asset:System/layout.ajax-form.tpl"}

{block CONTENT_AJAX_FORM}
    {include file="asset:System/Partials/form_group.select.tpl" options=$dateformat required=true label={lang t="system|date_format"}}
    {include file="asset:System/Partials/form_group.select.tpl" options=$notify required=true label={lang t="guestbook|notification"}}
    <div id="guestbook-entry-notification-wrapper">
        {include file="asset:System/Partials/form_group.input_text.tpl" name="notify_email" value=$form.notify_email labelRequired=true label={lang t="guestbook|notification_email"}}
    </div>
    {include file="asset:System/Partials/form_group.button_group.tpl" options=$overlay required=true label={lang t="guestbook|use_overlay"}}
    {event name="guestbook.layout.settings"}
    {include file="asset:System/Partials/form_group.submit_split.tpl" form_token=$form_token back_url={uri args="acp/guestbook"}}
    {javascripts}
        {include_js module="guestbook" file="admin/index.settings"}
    {/javascripts}
{/block}
