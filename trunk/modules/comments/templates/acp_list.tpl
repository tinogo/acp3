<form action="{uri args="acp/comments/delete"}" method="post">
	<div id="adm-list" class="well">
		{check_access mode="link" path="acp/comments/settings" icon="32/advancedsettings" width="32" height="32"}
		{check_access mode="input" path="acp/comments/delete" icon="32/cancel" lang="system|delete_marked"}
		<h2>{lang t="system|overview"}</h2>
	</div>
{if isset($redirect_message)}
{$redirect_message}
{/if}
{if isset($comments)}
	<table id="acp-table" class="table table-striped">
		<thead>
			<tr>
{if $can_delete === true}
				<th style="width:3%"><input type="checkbox" id="mark-all" value="1"></th>
{/if}
				<th>{lang t="comments|module"}</th>
				<th>{lang t="comments|comments_count"}</th>
			</tr>
		</thead>
		<tbody>
{foreach $comments as $row}
			<tr>
{if $can_delete === true}
				<td><input type="checkbox" name="entries[]" value="{$row.module_id}"></td>
{/if}
				<td>{check_access mode="link" path="acp/comments/list_comments/id_`$row.module_id`" lang="comments|show_comments" title=$row.name}</td>
				<td>{$row.comments_count}</td>
			</tr>
{/foreach}
		</tbody>
	</table>
{if $can_delete === true}
{mark name="entries"}
{/if}
{else}
	<div class="alert align-center">
		<strong>{lang t="system|no_entries"}</strong>
	</div>
{/if}
</form>