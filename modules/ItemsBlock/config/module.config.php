<?php
return [
    'block_layouts' => [
        'invokables' => [
            'itemsBlock' => ItemsBlock\Site\BlockLayout\ItemsBlock::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ]
];
