<title>{$HEAD_TITLE}</title>
{include file="asset:system/meta.tpl" meta=$META inline}
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="alternate" type="application/rss+xml" href="{uri args="feeds/index/index/feed_news"}" title="{$PAGE_TITLE} - {lang t="news|news"}">
<link rel="alternate" type="application/rss+xml" href="{uri args="feeds/index/index/feed_files"}" title="{$PAGE_TITLE} - {lang t="files|files"}">
<!-- STYLESHEETS -->
<!--[if lt IE 9]>
{include_js module="system" file="libs/html5shiv"}
<![endif]-->