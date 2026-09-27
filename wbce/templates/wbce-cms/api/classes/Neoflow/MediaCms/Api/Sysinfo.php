<?php

namespace Neoflow\MediaCms\Api;

class Sysinfo extends AbstractApi
{
    /**
     * @var array
     */
    protected $apiMethods = [
        'get',
    ];

    /**
     * Get system info.
     *
     * @param string $args Method API arguments
     */
    public function get($args)
    {
        $phpinfo = phpinfo2array('phpinfo');
        $this->publish([
            'php' => [
                'version' => phpversion(),
                'version_full' => isset($phpinfo[0]) ? $phpinfo[0] : 'Unknown',
                'loaded_extensions' => implode(', ', get_loaded_extensions()),
            ],
            'server' => [
                'system' => isset($phpinfo['System']) ? $phpinfo['System'] : 'Unknown',
                'server_api' => isset($phpinfo['Server API']) ? $phpinfo['Server API'] : 'Unknown',
            ],
        ]);
    }
}
