<?php

namespace App\Routes;

use App\Controllers\CategoryController;

class CategoryRoutes extends Router {
    function __construct() {
        $categoryController = new CategoryController();

        $this->routes = [
            'GET' => [
                'categories' => [$categoryController, 'index'],
                'categories/([a-f0-9\-]+)' => [$categoryController, 'show']
            ],
            'POST' => [
                'categories' => [$categoryController, 'store'],
            ],
            'PUT' => [
                'categories/([a-f0-9\-]+)' => [$categoryController, 'update']
            ],
            'DELETE' => [
                'categories/([a-f0-9\-]+)' => [$categoryController, 'destroy']
            ]
        ];
        
    }
}
