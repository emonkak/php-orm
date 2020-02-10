<?php

declare(strict_types=1);

return [
    'directory_list' => [
        'src',
        'vendor/emonkak/database',
        'vendor/emonkak/enumerable',
        'vendor/ocramius/proxy-manager',
        'vendor/psr/simple-cache',
    ],
    'suppress_issue_types' => [
        'PhanParamReqAfterOpt'
    ],
    'exclude_analysis_directory_list' => [
        'vendor/'
    ],
    'analyze_signature_compatibility' => false,
];
