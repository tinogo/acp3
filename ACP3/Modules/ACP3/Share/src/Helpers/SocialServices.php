<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Share\Helpers;

use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Share\Installer\Schema;

class SocialServices
{
    /**
     * @var array<string, string|null>
     */
    private static $servicesMap = [
        'twitter' => null,
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'pinterest' => 'Pinterest',
        'xing' => 'Xing',
        'whatsapp' => null,
        'addthis' => 'AddThis',
        'tumblr' => null,
        'flattr' => 'Flattr',
        'diaspora' => null,
        'reddit' => 'Reddit',
        'stumbleupon' => 'StumbleUpon',
        'threema' => null,
        'weibo' => null,
        'tencent - weibo' => null,
        'qzone' => null,
        'telegram' => null,
        'vk' => 'Vk',
        'mail' => null,
        'print' => null,
        'info' => null,
    ];

    public function __construct(private SettingsInterface $settings)
    {
    }

    public function getAllServices(): array
    {
        return array_keys(self::$servicesMap);
    }

    public function getActiveServices(): array
    {
        $settings = $this->settings->getSettings(Schema::MODULE_NAME);

        $services = unserialize($settings['services']);
        if (\is_array($services) === false) {
            $services = [];
        }

        return array_intersect(
            $this->getAllServices(),
            $services
        );
    }

    public function getAllBackendServices(): array
    {
        return array_filter(self::$servicesMap);
    }

    public function getActiveBackendServices(): array
    {
        $activeServices = $this->getActiveServices();

        return array_values(
            array_filter(
                $this->getAllBackendServices(),
                static fn (?string $value, string $key) => $value !== null && \in_array($key, $activeServices, true),
                \ARRAY_FILTER_USE_BOTH
            )
        );
    }
}
