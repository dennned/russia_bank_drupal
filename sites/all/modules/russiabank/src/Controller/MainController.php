<?php
namespace Drupal\russiabank\Controller;


/**
 * Provides route responses for the  module.
 */
class MainController{

    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function contentAction() {
        return [
            '#markup' => 'Hello, world',
        ];
    }

}