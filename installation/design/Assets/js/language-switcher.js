/*
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

jQuery(document).ready(function ($) {
    var $doc = $(document),
        $languages = $('#languages');

    $doc.data('has-changes', false);
    $('#content').find(':input').change(function () {
        $doc.data('has-changes', true);
    });

    $languages.find('.btn').addClass('hidden');
    $('#lang').change(function () {
        var submitForm = true;
        if ($doc.length > 0 && $doc.data('has-changes') === true) {
            submitForm = confirm($(this).data('change-language-warning'));
        }

        if (submitForm === true) {
            $languages.submit();
        }
    });
});
