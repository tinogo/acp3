{extends file="asset:`$LAYOUT`"}

{block CONTENT}
    {redirect_message}
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>{lang t="system|name"}</th>
            <th>{lang t="system|description"}</th>
            <th>{lang t="system|author"}</th>
            <th>{lang t="system|version"}</th>
            <th>{lang t="system|options"}</th>
        </tr>
        </thead>
        <tbody>
        {foreach $designs as $row}
            <tr>
                <td>{$row.name}</td>
                <td>{$row.description}</td>
                <td>{$row.author}</td>
                <td>{$row.version}</td>
                <td>
                    {if $row.selected == 1}
                        <i class="fa fa-check text-success" aria-hidden="true"></i>
                    {else}
                        <a href="{uri args="acp/system/extensions/designs/dir_`$row.dir`"}" data-ajax-form="true" data-ajax-form-loading-text="{lang t="system|loading_please_wait"}">
                            <i class="fa fa-trash text-danger" aria-hidden="true"></i>
                        </a>
                    {/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {javascripts}
        {include_js module="system" file="ajax-form"}
    {/javascripts}
{/block}
