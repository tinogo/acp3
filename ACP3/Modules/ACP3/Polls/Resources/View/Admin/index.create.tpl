{extends file="asset:System/layout.ajax-form.tpl"}

{block CONTENT_AJAX_FORM}
    {tabset identifier="poll-admin-edit-form"}
        {tab title={lang t="system|publication_period"}}
            {datepicker name=['start', 'end'] value=[$form.start, $form.end]}
        {/tab}
        {tab title={lang t="polls|poll"}}
            {include file="asset:System/Partials/form_group.input_text.tpl" name="title" value=$form.title required=true maxlength=120 label={lang t="polls|question"}}
            {foreach $answers as $row}
                <div class="row mb-3">
                    <label for="answer-{$row@index}" class="col-md-2 col-form-label">{lang t="polls|answer_x" args=['%number%' => $row@index+1]}</label>

                    <div class="col-md-10">
                        {if isset($row.id)}
                            <div class="input-group">
                                <input class="form-control" type="text" name="answers[{$row@index}][text]" id="answer-{$row@index}" value="{$row.text}" maxlength="120">
                                <div class="input-group-addon">
                                    <input type="checkbox" name="answers[{$row@index}][delete]" value="1">
                                </div>
                            </div>
                            <input type="hidden" name="answers[{$row@index}][id]" value="{$row.id}">
                        {else}
                            <input class="form-control" type="text" name="answers[{$row@index}][text]" id="answer-{$row@index}" value="{$row.text}" maxlength="120">
                        {/if}
                    </div>
                </div>
            {/foreach}
            <div class="row mb-3">
                <div class="offset-md-2 col-md-10">
                    <button type="submit" name="add_answer" class="btn btn-outline-secondary" data-hash-change="#tab-content-2">
                        {lang t="polls|add_answer"}
                    </button>
                </div>
            </div>
            {include file="asset:System/Partials/form_group.checkbox.tpl" options=$options label={lang t="system|options"}}
        {/tab}
    {/tabset}
    {include file="asset:System/Partials/form_group.submit_split.tpl" form_token=$form_token back_url={uri args="acp/polls"}}
    {javascripts}
        {include_js module="system" file="partials/hash-change"}
    {/javascripts}
{/block}
