<?php
namespace Drupal\russiabank\Controller;


/**
 * Provides route responses for the  module.
 */
class MainController {



    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function contentAction() {
        die('TEST');
        return [
            '#markup' => 'Hello, world',
        ];
    }

}