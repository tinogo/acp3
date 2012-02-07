<div class="news">
	<h3 class="header">{$news.headline}</h3>
	<div class="date">
		{$news.date}
	</div>
	<div class="content">
		{$news.text}
{if $news.uri != '' && $news.link_title != ''}
		<div class="hyperlink">
			<strong>{lang t="news|additional_hyperlink"}:</strong> <a href="{$news.uri}"{$news.target}>{$news.link_title}</a>
		</div>
{/if}
	</div>
</div>
{if isset($comments)}
{$comments}
{/if}