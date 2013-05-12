{if isset($categories)}
<table class="table table-striped">
	<thead>
		<tr>
			<th colspan="2">{lang t="files|category_select"}</th>
		</tr>
	</thead>
	<tbody>
{foreach $categories as $cat}
		<tr>
			<td style="width:25%"><a href="{uri args="files/files/cat_`$cat.id`"}">{$cat.title}</a></td>
			<td>{$cat.description}</td>
		</tr>
{/foreach}
	</tbody>
</table>
{else}
<div class="alert align-center">
	<strong>{lang t="system|no_entries"}</strong>
</div>
{/if}