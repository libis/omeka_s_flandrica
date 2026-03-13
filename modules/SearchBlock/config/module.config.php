<?php
return [
    'block_layouts' => [
        'invokables' => [
            'SearchBlock' => SearchBlock\Site\BlockLayout\SearchBlock::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ]
];
