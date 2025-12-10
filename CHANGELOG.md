# Release Notes for Gumlet

## 1.5.2 - 2025-12-10

### Fixed
- Prevented infinite recursion by building Gumlet URLs directly in `EVENT_BEFORE_DEFINE_URL` and marking the event handled.
- Exposed `buildParams` publicly and added array transform normalization so event handlers can safely reuse transform logic.
- Improved `DefineAssetUrlEvent` handler to use event properties and avoid unknown-method errors.

### Changed
- `getUrl`/transform behavior now consistently generates Gumlet URLs without calling back into Craft URL generation.

## 1.3.4 - 2025-11-20

### Changed
- Made transform parameter handling type-safe
- Only valid ImageTransform properties (width, height, quality, format) are accepted
- All other parameters are automatically routed to additionalParams
- Prevents "Setting unknown property" errors

## 1.3.1 - 2025-11-20

### Changed
- Removed unused `originalTransformer` property
- Cleaned up code annotations

## 1.3.0 - 2025-11-20

### Added
- `gumletUrl()` Twig function for easy URL generation
- `craft.gumlet.buildUrl()` method now accepts array transforms
- Support for passing Gumlet parameters as third argument to `gumletUrl()`
- Improved domain normalization (strips protocol and trailing slashes automatically)
- Better error handling during plugin installation
- Twig extension for direct URL generation

### Changed
- `buildUrl()` method now accepts both array and ImageTransform object types
- Simplified transform parameter handling - Gumlet parameters must be passed via `additionalParams`
- Improved documentation with multiple usage examples
- Better component access patterns

### Fixed
- Fixed Twig extension registration
- Fixed component access in Twig extension and transformer
- Fixed domain normalization to handle URLs with protocols

## 1.1.3 - 2025-11-20

### Added
- Initial release
- Drop-in replacement for Craft CMS native image transforms
- Support for `.srcset()` method
- Additional Gumlet parameters via `gumlet` object key
- Support for PDF rasterization
- Configurable Gumlet domain and settings

