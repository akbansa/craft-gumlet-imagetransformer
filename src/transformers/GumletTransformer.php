<?php

namespace gumlet\imagetransformer\transformers;

use craft\base\imagetransforms\ImageTransformerInterface;
use craft\elements\Asset;
use craft\models\ImageTransform;
use craft\models\ImageTransformIndex;
use gumlet\imagetransformer\Plugin;

/**
 * Gumlet image transformer
 *
 * Transforms Craft CMS image assets using Gumlet's image transformation service.
 */
class GumletTransformer implements ImageTransformerInterface
{
    /**
     * @inheritdoc
     */
    public function getTransformUrl(Asset $asset, ImageTransform $transform, bool $immediately): string
    {
        $plugin = Plugin::getInstance();
        $gumlet = $plugin->gumlet;
        
        // Extract additional Gumlet parameters from transform
        $additionalParams = [];
        
        // Check if transform has additional Gumlet parameters
        // These can be passed via the transform array in templates
        // In Craft CMS, custom transform parameters are stored in the transform's custom properties
        if (method_exists($transform, 'getCustomProperties')) {
            $customProps = $transform->getCustomProperties();
            if (isset($customProps['gumlet']) && is_array($customProps['gumlet'])) {
                $additionalParams = $customProps['gumlet'];
            }
        } else {
            // Fallback: try toArray() method
            $transformArray = $transform->toArray();
            if (isset($transformArray['gumlet']) && is_array($transformArray['gumlet'])) {
                $additionalParams = $transformArray['gumlet'];
            }
        }

        return $gumlet->buildUrl($asset, $transform, $additionalParams);
    }

    /**
     * @inheritdoc
     */
    public function invalidateAssetTransforms(Asset $asset): void
    {
        // Gumlet handles transforms on-the-fly, so no invalidation needed
    }

    /**
     * @inheritdoc
     */
    public function getTransformIndex(Asset $asset, ImageTransform $transform): ?ImageTransformIndex
    {
        // Gumlet generates URLs on-the-fly, so we don't need to track transform indices
        return null;
    }

    /**
     * @inheritdoc
     */
    public function deleteTransformIndex(ImageTransformIndex $index): void
    {
        // No-op for Gumlet
    }

    /**
     * @inheritdoc
     */
    public function purgeTransformsForAsset(Asset $asset): void
    {
        // No-op for Gumlet
    }

    /**
     * @inheritdoc
     */
    public function getUrlForTransform(Asset $asset, ImageTransform $transform, ImageTransformIndex $index): string
    {
        return $this->getTransformUrl($asset, $transform, false);
    }
}

