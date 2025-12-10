<?php

namespace gumlet\imagetransformer;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\services\ImageTransforms;
use craft\elements\Asset;
use craft\base\Model;
use craft\events\DefineBehaviorsEvent;
use craft\web\twig\variables\CraftVariable;
use gumlet\imagetransformer\models\Settings;
use gumlet\imagetransformer\services\Gumlet as GumletService;
use gumlet\imagetransformer\transformers\GumletTransformer;
use gumlet\imagetransformer\twigextensions\GumletTwigExtension;
use gumlet\imagetransformer\behaviors\GumletAssetBehavior;
use yii\base\Event;

/**
 * Gumlet plugin
 *
 * Adds Gumlet powered asset transforms to Craft CMS.
 *
 * @method static Plugin getInstance()
 * @property GumletService $gumlet
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.3.4';
    public bool $hasCpSettings = false;

    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                'gumlet' => GumletService::class,
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Only initialize transformer if Craft application is fully bootstrapped
        // and the ImageTransforms service is available
        // This prevents errors during plugin installation
        if (!Craft::$app) {
            return;
        }

        // Override Craft's default image transformer with Gumlet transformer
        // This replaces the default transformer completely, so all image transforms
        // will go through Gumlet instead of Craft's native image processing
        try {
            if (Craft::$app->has('imageTransforms', true)) {
                $imageTransforms = Craft::$app->getImageTransforms();
                if ($imageTransforms) {
                    // Set Gumlet transformer as the new default
                    // This means ALL image transforms will use Gumlet URLs with query parameters
                    // instead of Craft's path-based transform URLs (e.g., _300x300_crop_center-center_none/)
                    $imageTransforms->setTransformer(new GumletTransformer());
                }
            }
        } catch (\Throwable $e) {
            // Silently fail if service isn't available (e.g., during early installation phase)
        }

        // Register Twig extension
        try {
            if (Craft::$app->getView()) {
                Craft::$app->getView()->registerTwigExtension(new GumletTwigExtension());
            }
        } catch (\Throwable $e) {
            // Silently fail if view service isn't available
        }

        // Register Gumlet service as a Twig variable (only for web requests)
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                try {
                    /** @var CraftVariable $variable */
                    $variable = $event->sender;
                    // Get the plugin instance
                    $plugin = self::getInstance();
                    if ($plugin) {
                        // Try to get the component - it should be available after plugin init
                        // Access it directly as a property (Craft creates it from config())
                        try {
                            $gumletService = $plugin->gumlet;
                            if ($gumletService) {
                                $variable->set('gumlet', $gumletService);
                            }
                        } catch (\Throwable $componentError) {
                            // If component access fails, create service directly as fallback
                            $variable->set('gumlet', new GumletService());
                        }
                    }
                } catch (\Throwable $e) {
                    // Silently fail if service isn't available
                }
            }
        );

        // Attach GumletAssetBehavior to Asset elements
        Event::on(
            Asset::class,
            Model::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->behaviors[] = GumletAssetBehavior::class;
            }
        );
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Settings
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return null; // Settings are managed via config file
    }
}

