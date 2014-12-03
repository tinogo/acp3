{if $access_check.mode == 'link'}
    <a href="{$access_check.uri}" title="{$access_check.lang}">
        {if isset($access_check.icon)}
            <img src="{$access_check.icon}"{$access_check.width}{$access_check.height} alt="{$access_check.lang}">
        {/if}
        {if isset($access_check.class)}
            <i class="{$access_check.class}" aria-hidden="true"></i>
        {/if}
        {if isset($access_check.title)}
            {$access_check.title}
        {/if}
    </a>
{elseif $access_check.mode == 'input'}
    <input type="image" src="{$access_check.icon}" alt="{$access_check.lang}" title="{$access_check.lang}" style="width:32px;height:32px">
{elseif $access_check.mode == 'button'}
    <button type="submit" class="btn btn-link" title="{$access_check.lang}">
        {if isset($access_check.class)}
            <i class="{$access_check.class}" aria-hidden="true"></i>
        {/if}
        {if isset($access_check.title)}
            {$access_check.title}
        {/if}
    </button>
{/if}