# Release Notes for Gumlet

## 1.2.0 - 2025-11-20

### Added
- `gumletUrl()` Twig function for easy URL generation
- `craft.gumlet.buildUrl()` method now accepts array transforms
- Support for passing Gumlet parameters as third argument to `gumletUrl()`
- Improved domain normalization (strips protocol and trailing slashes automatically)
- Better error handling during plugin installation

### Changed
- `buildUrl()` method now accepts both array and ImageTransform object types
- Improved documentation with multiple usage examples

## 1.1.3 - 2025-11-20

### Added
- Initial release
- Drop-in replacement for Craft CMS native image transforms
- Support for `.srcset()` method
- Additional Gumlet parameters via `gumlet` object key
- Support for PDF rasterization
- Configurable Gumlet domain and settings

