/*
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

jQuery(document).ready(($) => {
    $('[data-datatable-init]').each(function () {
        const $this = $(this),
            json = $this.data('datatable-init');

        $this.dataTable(json);
    });
});
