<?php
/**
 * Implements hook_views_data()
 */
function bank_views_data()
{
    return [
        'views' => [
            'bank_area' => [
                'title' => t('Title Bank Area'),
                'help' => t('Some helps for bank area'),
                'area' => [
                    'handler' => 'Drupal\bank\Views\Handlers\Area\BankHandler'
                ]
            ]
        ]
    ];
}