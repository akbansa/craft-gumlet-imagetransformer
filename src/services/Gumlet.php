<?php

namespace gumlet\imagetransformer\services;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\models\ImageTransform;
use gumlet\imagetransformer\Plugin;

/**
 * Gumlet service
 *
 * Handles URL generation and parameter mapping for Gumlet image transformations.
 */
class Gumlet extends Component
{
    /**
     * Get the Gumlet domain from config
     *
     * @return string
     */
    public function getDomain(): string
    {
        $settings = Plugin::getInstance()->getSettings();
        $domain = $settings->gumletDomain ?? '';
        
        if (empty($domain)) {
            $config = Craft::$app->getConfig()->getConfigFromFile('gumlet-imagetransformer');
            $domain = $config['gumletDomain'] ?? '';
        }
        
        return $domain;
    }

    /**
     * Check if Gumlet is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $settings = Plugin::getInstance()->getSettings();
        $enabled = $settings->enabled ?? true;
        
        // If not set in plugin settings, check config file
        if ($enabled === null) {
            $config = Craft::$app->getConfig()->getConfigFromFile('gumlet-imagetransformer');
            $enabled = $config['enabled'] ?? true;
        }
        
        return (bool) $enabled;
    }

    /**
     * Build a Gumlet URL from an asset and transform
     *
     * @param Asset $asset The asset to transform
     * @param ImageTransform|null $transform The transform to apply
     * @param array $additionalParams Additional Gumlet-specific parameters
     * @return string The transformed URL
     */
    public function buildUrl(Asset $asset, ?ImageTransform $transform = null, array $additionalParams = []): string
    {
        if (!$this->isEnabled()) {
            return $asset->getUrl();
        }

        $domain = $this->getDomain();
        
        if (empty($domain)) {
            // If domain is not configured, return original URL
            // This allows the plugin to work even if Gumlet isn't configured yet
            return $asset->getUrl();
        }

        // Get the asset URL - use the volume's base URL or the asset's URL
        $assetUrl = $asset->getUrl();
        
        // If the asset URL already contains the Gumlet domain, use it as-is
        if (str_contains($assetUrl, $domain)) {
            $baseUrl = $assetUrl;
        } else {
            // Replace the base URL with Gumlet domain
            $baseUrl = $this->replaceBaseUrl($assetUrl, $domain);
        }

        // Build query parameters
        $params = $this->buildParams($transform, $additionalParams);

        // Even if no transform params, return the Gumlet URL (for optimization)
        if (empty($params)) {
            return $baseUrl;
        }

        // Check if URL already has query parameters
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        
        return $baseUrl . $separator . http_build_query($params);
    }

    /**
     * Replace the base URL with Gumlet domain
     *
     * @param string $url The original URL
     * @param string $domain The Gumlet domain
     * @return string The URL with Gumlet domain
     */
    protected function replaceBaseUrl(string $url, string $domain): string
    {
        // Parse the URL
        $parsedUrl = parse_url($url);
        
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return $url;
        }

        // Build new URL with Gumlet domain
        $scheme = $parsedUrl['scheme'] ?? 'https';
        $path = $parsedUrl['path'] ?? '/';
        $query = isset($parsedUrl['query']) && $parsedUrl['query'] !== '' ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) && $parsedUrl['fragment'] !== '' ? '#' . $parsedUrl['fragment'] : '';

        return $scheme . '://' . $domain . $path . $query . $fragment;
    }

    /**
     * Build query parameters from transform and additional params
     *
     * @param ImageTransform|null $transform The transform to convert
     * @param array $additionalParams Additional Gumlet-specific parameters
     * @return array The query parameters
     */
    protected function buildParams(?ImageTransform $transform, array $additionalParams = []): array
    {
        $params = [];

        if ($transform) {
            // Width
            if (!empty($transform->width)) {
                $params['w'] = (int) $transform->width;
            }

            // Height
            if (!empty($transform->height)) {
                $params['h'] = (int) $transform->height;
            }

            // Quality
            if (!empty($transform->quality)) {
                $params['q'] = (int) $transform->quality;
            }

            // Format
            if (!empty($transform->format)) {
                $mappedFormat = $this->mapFormat($transform->format);
                if ($mappedFormat !== null) {
                    $params['f'] = $mappedFormat;
                }
            }

            // Mode/Fit
            if (!empty($transform->mode)) {
                $params['fit'] = $this->mapMode($transform->mode);
            }

            // Position/Focus
            if (!empty($transform->position)) {
                $params['crop'] = $this->mapPosition($transform->position);
            }

            // Interlace
            if ($transform->interlace) {
                $params['interlace'] = '1';
            }
        }

        // Merge additional Gumlet-specific parameters
        if (!empty($additionalParams)) {
            $params = array_merge($params, $additionalParams);
        }

        // Apply defaults from config if not set
        $config = Craft::$app->getConfig()->getConfigFromFile('gumlet-imagetransformer');
        
        if (!isset($params['q']) && isset($config['defaultQuality'])) {
            $params['q'] = (int) $config['defaultQuality'];
        }

        if (!isset($params['f']) && isset($config['defaultFormat']) && $config['defaultFormat'] !== 'auto') {
            $params['f'] = (string) $config['defaultFormat'];
        }

        // Filter out null and empty string values
        return array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Map Craft format to Gumlet format
     *
     * @param string|null $format The Craft format
     * @return string|null The Gumlet format
     */
    protected function mapFormat(?string $format): ?string
    {
        if (!$format) {
            return null;
        }

        $map = [
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            'avif' => 'avif',
        ];

        $format = strtolower($format);
        
        return $map[$format] ?? $format;
    }

    /**
     * Map Craft mode to Gumlet fit parameter
     *
     * @param string|null $mode The Craft transform mode
     * @return string The Gumlet fit parameter
     */
    protected function mapMode(?string $mode): string
    {
        if (!$mode) {
            return 'clip';
        }

        $map = [
            'crop' => 'crop',
            'fit' => 'clip',
            'stretch' => 'scale',
            'letterbox' => 'clip',
        ];

        $mode = strtolower($mode);
        
        return $map[$mode] ?? 'clip';
    }

    /**
     * Map Craft position to Gumlet crop parameter
     *
     * @param string|null $position The Craft position
     * @return string The Gumlet crop position
     */
    protected function mapPosition(?string $position): string
    {
        if (!$position) {
            return 'center';
        }

        $map = [
            'top-left' => 'top-left',
            'top-center' => 'top',
            'top-right' => 'top-right',
            'center-left' => 'left',
            'center-center' => 'center',
            'center-right' => 'right',
            'bottom-left' => 'bottom-left',
            'bottom-center' => 'bottom',
            'bottom-right' => 'bottom-right',
        ];

        $position = strtolower($position);
        
        return $map[$position] ?? 'center';
    }
}

