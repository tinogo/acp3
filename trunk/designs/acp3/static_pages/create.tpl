{if isset($error_msg)}
{$error_msg}
{/if}
<script type="text/javascript" src="{$DESIGN_PATH}static_pages/script.js"></script>
<form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8">
	<div id="tabs">
		<ul>
			<li><a href="#tab-1">{lang t="common|publication_period"}</a></li>
			<li><a href="#tab-2">{lang t="static_pages|page_statements"}</a></li>
			<li><a href="#tab-3">{lang t="common|seo"}</a></li>
		</ul>
		<div id="tab-1">
			<dl>
				<dt><label for="start">{lang t="common|publication_period"}</label></dt>
				<dd>{$publication_period}</dd>
			</dl>
			<p>
				{lang t="common|date_description"}
			</p>
		</div>
		<div id="tab-2" class="ui-tabs-hide">
			<dl>
				<dt><label for="title">{lang t="static_pages|title"}</label></dt>
				<dd><input type="text" name="form[title]" id="title" value="{$form.title}" maxlength="120"></dd>
				<dt><label for="text">{lang t="static_pages|text"}</label></dt>
				<dd>{wysiwyg name="text" value="`$form.text`" height="250"}</dd>
{if isset($create)}
				<dt><label for="create-item-1">{lang t="static_pages|create_menu_item"}</label></dt>
				<dd>
{foreach $create as $row}
					<label for="create-item-{$row.value}">
						<input type="radio" name="form[create]" id="create-item-{$row.value}" value="{$row.value}" class="checkbox"{$row.selected}>
						{$row.lang}
					</label>
{/foreach}
				</dd>
{/if}
			</dl>
			<dl id="create-item-container">
				<dt><label for="block_id">{lang t="menu_items|blocks"}</label></dt>
				<dd>
					<select name="form[block_id]" id="block_id">
						<option value="">{lang t="common|pls_select"}</option>
{foreach $blocks as $row}
						<option value="{$row.id}"{$row.selected}>{$row.title}</option>
{/foreach}
					</select>
				</dd>
				<dt><label for="parent">{lang t="menu_items|superior_page"}</label></dt>
				<dd>
					<select name="form[parent]" id="parent">
						<option value="">{lang t="menu_items|no_superior_page"}</option>
{foreach $pages_list as $block => $pages}
						<optgroup label="{$block}">
{foreach $pages as $row}
							<option value="{$row.id}"{$row.selected}>{$row.spaces}{$row.title}</option>
{/foreach}
						</optgroup>
{/foreach}
					</select>
				</dd>
				<dt><label for="display-1">{lang t="menu_items|display_item"}</label></dt>
				<dd>
{foreach $display as $row}
					<label for="display-{$row.value}">
						<input type="radio" name="form[display]" id="display-{$row.value}" value="{$row.value}" class="checkbox"{$row.selected}>
						{$row.lang}
					</label>
{/foreach}
				</dd>
			</dl>
		</div>
		<div id="tab-3" class="ui-tabs-hide">
			{seo_fields alias="`$form.alias`" keywords="`$form.seo_keywords`" description="`$form.seo_description`"}
		</div>
	</div>
	<div class="form-bottom">
		<input type="submit" value="{lang t="common|submit"}" class="form">
		<input type="reset" value="{lang t="common|reset"}" class="form">
		{$form_token}
	</div>
</form>