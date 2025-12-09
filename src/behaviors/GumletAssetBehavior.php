<?php
namespace gumlet\imagetransformer\behaviors;

use craft\elements\Asset;
use yii\base\Behavior;
use gumlet\imagetransformer\Plugin;

class GumletAssetBehavior extends Behavior
{
    /**
     * Override getUrl to support transformation arrays
     */
    public function getUrl($transform = null): string
    {
        $plugin = Plugin::getInstance();
        $gumlet = $plugin->gumlet;
        return $gumlet->buildUrl($this->owner, $transform);
    }

    /**
     * Override getWidth to support transformation arrays
     */
    public function getWidth($transform = null): int
    {
        if (is_array($transform) && isset($transform['width'])) {
            return (int)$transform['width'];
        }
        return $this->owner->width;
    }

    /**
     * Override getHeight to support transformation arrays
     */
    public function getHeight($transform = null): int
    {
        if (is_array($transform) && isset($transform['height'])) {
            return (int)$transform['height'];
        }
        return $this->owner->height;
    }
}
