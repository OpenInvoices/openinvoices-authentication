<?php

namespace OpenInvoices\Authentication;

class Module
{
    /**
     * Gets the module configuration.
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}