{if isset($galleries)}
{$pagination}
{foreach $galleries as $row}
<div class="dataset-box">
	<div class="header">
		<div class="small pull-right">{$row.date}</div>
		<a href="{uri args="gallery/pics/id_`$row.id`"}">{$row.name} ({$row.pics_lang})</a>
	</div>
</div>
{/foreach}
{else}
<div class="alert align-center">
	<strong>{lang t="system|no_entries"}</strong>
</div>
{/if}