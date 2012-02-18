{if isset($galleries)}
{$pagination}
{foreach $galleries as $row}
<div class="galleries">
	<div class="date">
		{$row.date}
	</div>
	<h3 class="header"><a href="{uri args="gallery/pics/id_`$row.id`" alias="1"}">{$row.name} ({$row.pics})</a></h3>
</div>
{/foreach}
{else}
<div class="error-box">
	<h5>{lang t="common|no_entries"}</h5>
</div>
{/if}