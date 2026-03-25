# Installation

## PHP

```bash
composer require blackcube/yii-bleet
```

## Aurelia 2 companion

```bash
npm install @blackcube/aurelia2-bleet
```

## Requirements

- PHP 8.3+
- `blackcube/yii-bridge-model ^1.0`
- `blackcube/fileprovider ^1.0`
- `yiisoft/aliases ^3.1`
- `yiisoft/data ^2.0`
- `yiisoft/data-response ^2.2`
- `yiisoft/form-model ^1.1`
- `yiisoft/html ^3.12`
- `yiisoft/router ^4.0`
- `yiisoft/widget ^2.2`

## Usage

No DI or config-plugin registration needed. The package is a pure widget library — import and use widgets directly via `Bleet::*` factory methods.

Icon resources are resolved internally via `__DIR__` in `Svg` widget — no alias configuration required.
