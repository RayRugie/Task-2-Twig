<?php

namespace App\Core;

/**
 * Simple Template Engine
 * 
 * A fallback template engine when Twig is not available.
 * Uses PHP templates for rendering.
 */
class SimpleTemplateEngine
{
    private $globals = [];
    
    /**
     * Add global variable
     */
    public function addGlobal(string $name, $value): void
    {
        $this->globals[$name] = $value;
    }
    
    /**
     * Render template
     */
    public function render(string $template, array $data = []): void
    {
        // Merge globals with template data
        $data = array_merge($this->globals, $data);
        
        // Extract variables for use in template
        extract($data);
        
        // Convert Twig template path to PHP template path
        $phpTemplate = $this->convertTemplatePath($template);
        
        if (file_exists($phpTemplate)) {
            include $phpTemplate;
        } else {
            // Fallback: render a simple error page
            $this->renderErrorPage($template);
        }
    }
    
    /**
     * Convert Twig template path to PHP template path
     */
    private function convertTemplatePath(string $template): string
    {
        // Convert .twig to .php
        $phpTemplate = str_replace('.twig', '.php', $template);
        
        // Build full path
        return TEMPLATES_PATH . '/' . $phpTemplate;
    }
    
    /**
     * Render error page when template is not found
     */
    private function renderErrorPage(string $template): void
    {
        echo '<div class="container mt-5">';
        echo '<div class="alert alert-warning">';
        echo '<h4>Template Not Found</h4>';
        echo '<p>Template <code>' . htmlspecialchars($template) . '</code> could not be found.</p>';
        echo '<p>This is a development fallback. In production, ensure all templates are properly installed.</p>';
        echo '</div>';
        echo '</div>';
    }
}
