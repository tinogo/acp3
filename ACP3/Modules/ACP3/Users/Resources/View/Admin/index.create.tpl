{extends file="asset:System/layout.ajax-form.tpl"}

{block CONTENT_AJAX_FORM}
    {tabset identifier="user-admin-edit-form"}
        {tab title={lang t="system|general"}}
            {include file="asset:System/Partials/form_group.input_text.tpl" name="nickname" value=$form.nickname required=true maxlength=30 label={lang t="users|nickname"}}
            {include file="asset:System/Partials/form_group.input_text.tpl" name="realname" value=$form.realname maxlength=80 label={lang t="users|realname"}}
            {include file="asset:System/Partials/form_group.select.tpl" options=$gender required=true label={lang t="users|gender"}}
            {datepicker name="birthday" value=$birthday inputFieldOnly=true withTime=false label={lang t="users|birthday"}}
            {include file="asset:System/Partials/form_group.select.tpl" options=$roles required=true multiple=true label={lang t="permissions|roles"}}
            {include file="asset:System/Partials/form_group.button_group.tpl" options=$super_user required=true label={lang t="users|super_user"}}
        {/tab}
        {tab title={lang t="users|contact"}}
            {foreach $contact as $row}
                {include file="asset:System/Partials/form_group.input_text.tpl" name=$row.name value=$row.value maxlength=$row.maxlength label=$row.lang}
            {/foreach}
        {/tab}
        {tab title={lang t="users|address"}}
            {include file="asset:System/Partials/form_group.input_text.tpl" name="street" value=$form.street maxlength=80 label={lang t="users|address_street"}}
            {include file="asset:System/Partials/form_group.input_text.tpl" name="house_number" value=$form.house_number maxlength=5 label={lang t="users|address_house_number"}}
            {include file="asset:System/Partials/form_group.input_text.tpl" name="zip" value=$form.zip maxlength=5 label={lang t="users|address_zip"}}
            {include file="asset:System/Partials/form_group.input_text.tpl" name="city" value=$form.city maxlength=80 label={lang t="users|address_city"}}
            {include file="asset:System/Partials/form_group.select.tpl" options=$countries label={lang t="users|country"}}
        {/tab}
        {tab title={lang t="users|privacy"}}
            {include file="asset:System/Partials/form_group.button_group.tpl" options=$mail_display required=true label={lang t="users|display_mail"}}
            {include file="asset:System/Partials/form_group.button_group.tpl" options=$address_display required=true label={lang t="users|display_address"}}
            {include file="asset:System/Partials/form_group.button_group.tpl" options=$country_display required=true label={lang t="users|display_country"}}
            {include file="asset:System/Partials/form_group.radio.tpl" options=$birthday_display required=true label={lang t="users|birthday"}}
        {/tab}
        {tab title={lang t="users|pwd"}}
            {block PASSWORD_FIELDS}
                {include file="asset:Users/Partials/password_fields.tpl" required=true}
            {/block}
        {/tab}
    {/tabset}
    {include file="asset:System/Partials/form_group.submit_split.tpl" form_token=$form_token back_url={uri args="acp/users"}}
{/block}
