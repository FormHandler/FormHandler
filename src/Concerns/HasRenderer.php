<?php

namespace FormHandler\Concerns;

use FormHandler\Renderer\AbstractRenderer;

trait HasRenderer
{
    /**
     * The default renderer which will be used for all FromHandler instances.
     *
     * @var AbstractRenderer|null
     */
    protected static ?AbstractRenderer $defaultRenderer = null;

    /**
     * An renderer which will be used to render the fields.
     *
     * @var AbstractRenderer
     */
    protected AbstractRenderer $renderer;

    /**
     * Get the default renderer.
     *
     * For more information about formatters, {@see AbstractRenderer}
     *
     * @return AbstractRenderer|null
     */
    public static function getDefaultRenderer(): ?AbstractRenderer
    {
        return self::$defaultRenderer;
    }

    /**
     * Set a default renderer for all the form objects which are created.
     *
     * This could be useful when creating multiple forms in your project, and
     * if you don't want to set a custom renderer for every Form object.
     *
     * Example:
     * ```php
     * // set the default renderer which should be used
     * Form::setDefaultRenderer( new MyCustomRenderer() );
     * ```
     *
     * For more information about renderers, {@see AbstractRenderer}
     *
     * @param AbstractRenderer $renderer
     */
    public static function setDefaultRenderer(AbstractRenderer $renderer): void
    {
        self::$defaultRenderer = $renderer;
    }

    /**
     * Return the formatter
     *
     * @return AbstractRenderer
     */
    public function getRenderer(): AbstractRenderer
    {
        return $this->renderer;
    }

    /**
     * Set a renderer object
     *
     * @param AbstractRenderer $renderer
     *
     * @return $this
     */
    public function setRenderer(AbstractRenderer $renderer): self
    {
        $this->renderer = $renderer;

        return $this;
    }
}