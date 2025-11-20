<?php

namespace gumlet\imagetransformer;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\services\ImageTransforms;
use craft\web\twig\variables\CraftVariable;
use gumlet\imagetransformer\models\Settings;
use gumlet\imagetransformer\services\Gumlet as GumletService;
use gumlet\imagetransformer\transformers\GumletTransformer;
use yii\base\Event;

/**
 * Gumlet plugin
 *
 * Adds Gumlet powered asset transforms to Craft CMS.
 *
 * @method static Plugin getInstance()
 * @method GumletService getGumlet()
 * @property GumletService $gumlet
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';
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

        // Set Gumlet transformer as the default transformer
        // This replaces Craft's default image transformer
        Craft::$app->getImageTransforms()->setTransformer(new GumletTransformer());

        // Register Gumlet service as a Twig variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('gumlet', $this->getGumlet());
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

