# Gumlet Asset Transformer for Craft CMS

Adds Gumlet powered asset transforms to Craft CMS:

1. Drop-in replacement for Craft CMS native image transforms and `.srcset()` method
2. Add additional Gumlet parameters to image transforms
3. Use Gumlet for CP thumbnails
4. Allows `.pdf` files to be rasterized and transformed (unlike Craft CMS transforms)

The only thing you'll need to update is your filesystem Base URL to use your Gumlet domain.

## Requirements

This plugin requires Craft CMS 4.0.0 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin with Composer.

```bash
composer require gumlet/craft-gumlet-imagetransformer -w && php craft plugin/install gumlet-imagetransformer
```

Then update your filesystem Base URL to use your Gumlet domain.

## Configuration

Copy `config.php` into Craft's config folder and rename it to `gumlet-imagetransformer.php`.

```bash
cp vendor/gumlet/craft-gumlet-imagetransformer/src/config.php config/gumlet-imagetransformer.php
```

Update the `gumletDomain` config setting with your Gumlet domain.

```php
return [
    'gumletDomain' => 'your-domain.gumlet.io',
    'enabled' => true,
    'defaultQuality' => 80,
    'defaultFormat' => 'auto',
    'autoFormat' => true,
];
```

## Usage

This plugin provides multiple ways to generate Gumlet URLs for your images:

1. **Automatic transformer** (when configured as default) - Works with `asset.setTransform()` and `asset.url`
2. **Twig function** - Use `gumletUrl()` function in templates
3. **Service method** - Use `craft.gumlet.buildUrl()` in templates

### Method 1: Using Twig Function (Recommended)

The easiest way to generate Gumlet URLs is using the `gumletUrl()` Twig function:

```twig
{# Basic usage with transform array #}
<img src="{{ gumletUrl(asset, { width: 300, height: 300 }) }}" alt="{{ asset.title }}" />

{# With additional Gumlet parameters #}
<img src="{{ gumletUrl(asset, { width: 300, height: 300 }, { blur: 10, brightness: 5 }) }}" alt="{{ asset.title }}" />

{# With quality and format #}
<img src="{{ gumletUrl(asset, { width: 500, height: 500, quality: 85, format: 'webp' }) }}" alt="{{ asset.title }}" />
```

### Method 2: Using Service Method

You can also use the Gumlet service directly:

```twig
{# Basic usage #}
<img src="{{ craft.gumlet.buildUrl(asset, { width: 300, height: 300 }) }}" alt="{{ asset.title }}" />

{# With Gumlet-specific parameters in the transform array #}
<img src="{{ craft.gumlet.buildUrl(asset, { width: 300, height: 300, gumlet: { blur: 10 } }) }}" alt="{{ asset.title }}" />

{# Without transform (just replaces domain) #}
<img src="{{ craft.gumlet.buildUrl(asset) }}" alt="{{ asset.title }}" />
```

### Method 3: Using Asset Transforms (If Transformer is Active)

If the Gumlet transformer is set as the default, you can use Craft's native transform methods:

```twig
{# Set the transform #}
{% do asset.setTransform({ 
    width: 300, 
    height: 300,
}) %}

{# Render the tag #}
{{ tag('img', {
  src: asset.url,
  width: asset.width,
  height: asset.height,
  srcset: asset.getSrcset(['1.5x', '2x', '3x']),
  alt: asset.title,
}) }}
```

**Note:** The automatic transformer may not always be active depending on your Craft CMS configuration. Using `gumletUrl()` or `craft.gumlet.buildUrl()` is more reliable.

### Adding Additional Gumlet Parameters

In addition to the standard Craft CMS transform options:

* `mode`
* `width`
* `height`
* `quality`
* `format`
* `position`
* `fill`

You can also apply additional Gumlet parameters. There are two ways to do this:

**Option 1: Using the `gumlet` key in transform array**

```twig
{# Using gumletUrl() function #}
<img src="{{ gumletUrl(asset, { 
    width: 300, 
    height: 300,
    gumlet: {
        blur: 20,
        brightness: 10,
        contrast: 5,
    }
}) }}" alt="{{ asset.title }}" />

{# Using service method #}
<img src="{{ craft.gumlet.buildUrl(asset, { 
    width: 300, 
    height: 300,
    gumlet: {
        blur: 20,
        brightness: 10,
        contrast: 5,
    }
}) }}" alt="{{ asset.title }}" />
```

**Option 2: Passing as third parameter (gumletUrl only)**

```twig
<img src="{{ gumletUrl(asset, { width: 300, height: 300 }, { blur: 20, brightness: 10 }) }}" alt="{{ asset.title }}" />
```

### Available Gumlet Parameters

Gumlet supports many transformation parameters. Some common ones include:

* `blur` - Apply blur effect (0-100)
* `brightness` - Adjust brightness (-100 to 100)
* `contrast` - Adjust contrast (-100 to 100)
* `saturation` - Adjust saturation (-100 to 100)
* `sharpen` - Sharpen the image (0-100)
* `rotate` - Rotate image (degrees)
* `flip` - Flip image ('h' for horizontal, 'v' for vertical)
* `watermark` - Add watermark
* `text` - Add text overlay

For a complete list of available parameters, see the [Gumlet Image Transformation API documentation](https://docs.gumlet.com/docs/image-transform-apis).

**Note:** This plugin uses Gumlet's standard short parameter names (`w` for width, `h` for height, `q` for quality, `f` for format). These are the default parameter names used by Gumlet's image transformation API.

#### `gumlet` object key values

In Craft CMS < v5.6.0 the additional `gumlet` object key values may be lost when calling `.srcset()`. This is a limitation of how Craft CMS handles transform parameters.

## Mapping

### Transform Modes

Craft CMS transform modes are mapped to Gumlet fit parameters:

* `crop` → `crop`
* `fit` → `clip`
* `stretch` → `scale`
* `letterbox` → `clip`

### Position

Craft CMS positions are mapped to Gumlet crop positions:

* `top-left` → `top-left`
* `top-center` → `top`
* `top-right` → `top-right`
* `center-left` → `left`
* `center-center` → `center`
* `center-right` → `right`
* `bottom-left` → `bottom-left`
* `bottom-center` → `bottom`
* `bottom-right` → `bottom-right`

## About

Gumlet asset transforms for Craft CMS

