<?php

namespace Drupal\bank\Views\Handlers\Area;

/**
 * Class BankHandler
 * @package Drupal\bank\Views\Handlers\Area
 */
class BankHandler extends \views_handler_area {

    public function render($empty = FALSE)
    {
        return 'My Bank Area View';
    }
}