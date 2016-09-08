{extends file="asset:`$LAYOUT`"}

{block CONTENT}
    {if isset($error_msg)}
        {$error_msg}
    {/if}
    <form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8" class="form-horizontal" data-ajax-form="true" data-ajax-form-loading-text="{lang t="system|loading_please_wait"}">
        {block PERMISSIONS_RESOURCES_MODULE}
            <div class="form-group">
                <label for="modules" class="col-sm-2 control-label required">{lang t="system|module"}</label>

                <div class="col-sm-10">
                    <select class="form-control" name="modules" id="modules" required>
                        {foreach $modules as $row}
                            <option value="{$row.dir}"{$row.selected}>{$row.name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/block}
        {include file="asset:System/Partials/form_group.input_text.tpl" name="area" value=$form.area required=true maxlength=255 label={lang t="permissions|area"}}
        {include file="asset:System/Partials/form_group.input_text.tpl" name="controller" value=$form.controller required=true maxlength=255 label={lang t="permissions|controller"}}
        {include file="asset:System/Partials/form_group.input_text.tpl" name="resource" value=$form.resource required=true maxlength=255 label={lang t="permissions|resource_name"}}
        <div class="form-group">
            <label for="privileges" class="col-sm-2 control-label required">{lang t="permissions|assigned_privilege"}</label>

            <div class="col-sm-10">
                <select class="form-control" name="privileges" id="privileges" required>
                    {foreach $privileges as $row}
                        <option value="{$row.id}"{$row.selected}>{$row.key}{if !empty($row.description)} ({$row.description}){/if}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {include file="asset:System/Partials/form_group.submit.tpl" form_token=$form_token back_url={uri args="acp/permissions/resources"}}
    </form>
    {javascripts}
        {include_js module="system" file="ajax-form"}
    {/javascripts}
{/block}
