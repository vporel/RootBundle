<?php
namespace RootBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController as EA_AbstractDashboardController;

abstract class AbstractDashboardController extends EA_AbstractDashboardController
{

    protected function renderView(string $template, array $data = []): string
    {
        if(!str_ends_with($template, ".html.twig")){
            $template = $template . ".html.twig";
        }
        return parent::renderView($template, $data);
    }
}
