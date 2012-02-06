{if isset($comments)}
<form action="{uri args="acp/comments/delete_comments"}" method="post">
	<div id="adm-list">
		{check_access mode="input" action="comments|delete_comments" icon="32/cancel" lang="common|delete_marked"}
		<h2>{lang t="common|overview"}</h2>
	</div>
	<hr>
{if isset($redirect_message)}
{$redirect_message}
{/if}
{$pagination}
{assign var="can_delete" value=modules::check("comments", "delete_comments")}
	<table class="acp-table">
		<thead>
			<tr>
{if $can_delete === true}
				<th><input type="checkbox" id="mark-all" value="1" class="checkbox"></th>
{/if}
				<th>{lang t="common|date"}</th>
				<th>{lang t="common|name"}</th>
				<th>{lang t="common|message"}</th>
				<th>{lang t="comments|ip}</th>
				<th style="width:3%">{lang t="common|id"}</th>
			</tr>
		</thead>
		<tbody>
{foreach $comments as $row}
			<tr>
{if $can_delete === true}
				<td><input type="checkbox" name="entries[]" value="{$row.id}" class="checkbox"></td>
{/if}
				<td>{$row.date}</td>
				<td>{check_access mode="link" action="comments|edit" uri="acp/comments/edit/id_`$row.id`" title=$row.name}</td>
				<td>{$row.message}</td>
				<td>{$row.ip}</td>
				<td>{$row.id}</td>
			</tr>
{/foreach}
		</tbody>
	</table>
{if $can_delete === true}
{mark name="entries"}
{/if}
</form>
{else}
	<div class="error">
		<h5>{lang t="common|no_entries"}</h5>
	</div>
{/if}