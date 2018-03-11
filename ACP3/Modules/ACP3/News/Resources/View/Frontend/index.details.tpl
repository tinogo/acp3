{extends file="asset:`$LAYOUT`"}

{block CONTENT}
    <article>
        <section class="dataset-box">
            <header class="navbar navbar-default">
                <div class="navbar-header">
                    <h3 class="navbar-brand">{$news.title}</h3>
                </div>
                <time class="navbar-text small pull-right" datetime="{date_format date=$news.start format="c"}">
                    {date_format date=$news.start format=$dateformat}
                </time>
            </header>
            <div class="content">
                {$news.text|rewrite_uri}
                {if $news.uri != '' && $news.link_title != ''}
                    <footer class="well well-sm hyperlink">
                        <strong>{lang t="news|hyperlink"}:</strong>
                        <a href="{$news.uri|prefix_uri}"{$news.target}>{$news.link_title}</a>
                    </footer>
                {/if}
            </div>
            {event name="news.event.news_details_after" id=$news.id title=$news.title}
            {event name="share.layout.add_social_sharing"}
        </section>
        {if $comments_allowed === true}
            <section>
                {load_module module="frontend/comments" args=['module' => 'news', 'entryId' => $news.id]}
                {load_module module="frontend/comments/index/create" args=['module' => 'news', 'entryId' => $news.id, 'redirectUrl' => base64_encode("news/index/details/id_`$news.id`")]}
            </section>
        {/if}
    </article>
{/block}
