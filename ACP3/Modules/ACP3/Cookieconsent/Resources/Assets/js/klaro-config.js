/*
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

((window) => {
    window.klaroConfig = {
        // With the 0.7.0 release we introduce a 'version' paramter that will make
        // if easier for us to keep configuration files backwards-compatible in the future.
        version: 1,

        // You can customize the ID of the DIV element that Klaro will create
        // when starting up. If undefined, Klaro will use 'klaro'.
        elementID: 'klaro',

        // Setting this to true will keep Klaro from automatically loading itself
        // when the page is being loaded.
        noAutoLoad: false,

        // Setting this to true will render the descriptions of the consent
        // modal and consent notice are HTML. Use with care.
        htmlTexts: false,

        // Setting 'embedded' to true will render the Klaro modal and notice without
        // the modal background, allowing you to e.g. embed them into a specific element
        // of your website, such as your privacy notice.
        embedded: false,

        // You can group services by their purpose in the modal. This is advisable
        // if you have a large number of services. Users can then enable or disable
        // entire groups of services instead of having to enable or disable every service.
        groupByPurpose: true,

        // How Klaro should store the user's preferences. It can be either 'cookie'
        // (the default) or 'localStorage'.
        storageMethod: 'cookie',

        // You can customize the name of the cookie that Klaro uses for storing
        // user consent decisions. If undefined, Klaro will use 'klaro'.
        cookieName: 'klaro',

        // You can also set a custom expiration time for the Klaro cookie.
        // By default, it will expire after 120 days.
        cookieExpiresAfterDays: 365,

        // You can change to cookie domain for the consent manager itself.
        // Use this if you want to get consent once for multiple matching domains.
        // If undefined, Klaro will use the current domain.
        //cookieDomain: '.github.com',

        // Defines the default state for services (true=enabled by default).
        default: false,

        // If "mustConsent" is set to true, Klaro will directly display the consent
        // manager modal and not allow the user to close it before having actively
        // consented or declines the use of third-party services.
        mustConsent: true,

        // Show "accept all" to accept all services instead of "ok" that only accepts
        // required and "default: true" services
        acceptAll: true,

        // replace "decline" with cookie manager modal
        hideDeclineAll: false,

        // hide "learnMore" link
        hideLearnMore: false,

        // show cookie notice as modal
        noticeAsModal: false,

        // You can also remove the 'Realized with Klaro!' text in the consent modal.
        // Please don't do this! We provide Klaro as a free open source tool.
        // Placing a link to our website helps us spread the word about it,
        // which ultimately enables us to make Klaro! better for everyone.
        // So please be fair and keep the link enabled. Thanks :)
        //disablePoweredBy: true,

        // you can specify an additional class (or classes) that will be added to the Klaro `div`
        //additionalClass: 'my-klaro',

        // You can define the UI language directly here. If undefined, Klaro will
        // use the value given in the global "lang" variable. If that does
        // not exist, it will use the value given in the "lang" attribute of your
        // HTML tag. If that also doesn't exist, it will use 'en'.
        //lang: 'en',

        // You can overwrite existing translations and add translations for your
        // service descriptions and purposes. See `src/translations/` for a full
        // list of translations that can be overwritten:
        // https://github.com/KIProtect/klaro/tree/master/src/translations

        // Example config that shows how to overwrite translations:
        // https://github.com/KIProtect/klaro/blob/master/src/configs/i18n.js
        translations: {
            // translations defined under the 'zz' language code act as default
            zz: {
                privacyPolicyUrl: '/#privacy',
            },
            de: {
                purposes: {
                    security: 'Sicherheit',
                },
                acp3: {
                    description: 'Das verwendete Content Management Systems.',
                }
            },
            en: {
                acp3: {
                    description: 'The used Content Management System.',
                }
            }
        },

        // This is a list of third-party services that Klaro will manage for you.
        services: [
            {
                default: true,
                name: 'acp3',
                purposes: ['security'],
                required: true,
                title: 'ACP3',
            }
        ],
    };
})(window);
