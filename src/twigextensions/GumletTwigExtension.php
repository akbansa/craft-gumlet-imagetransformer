<?php

namespace gumlet\imagetransformer\twigextensions;

use craft\elements\Asset;
use craft\models\ImageTransform;
use gumlet\imagetransformer\Plugin;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Gumlet Twig extension
 */
class GumletTwigExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('gumletUrl', [$this, 'gumletUrl']),
        ];
    }

    /**
     * Build a Gumlet URL for an asset with optional transform
     *
     * @param Asset $asset The asset to transform
     * @param array|ImageTransform|null $transform Transform parameters or ImageTransform object
     * @param array $additionalParams Additional Gumlet-specific parameters
     * @return string The Gumlet URL
     */
    public function gumletUrl(Asset $asset, $transform = null, array $additionalParams = []): string
    {
        $plugin = Plugin::getInstance();
        
        // Access the gumlet component as a property (created by Craft from config())
        $gumlet = $plugin->gumlet;
        
        return $gumlet->buildUrl($asset, $transform, $additionalParams);
    }
}

