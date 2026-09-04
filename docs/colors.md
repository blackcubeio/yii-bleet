# Field and button colors

This document pins the tones used by the form components, so that
hand-written markup lands on the same values as `Bleet::input()` or
`Bleet::button()`.

The seven semantic colors — `primary`, `secondary`, `success`, `danger`,
`warning`, `info`, `accent` — replace the Tailwind Plus colors: `gray`
becomes `secondary`, `red` becomes `danger`, `indigo` becomes `primary`.

## Field block

| part | classes | source |
|---|---|---|
| container | *(none)* | bare `<div>` |
| label | `block text-sm/6 font-medium text-secondary-900` | `BleetFieldTrait::LABEL_CLASS` |
| input container | `mt-2` | `BleetFieldTrait::INPUT_CONTAINER_CLASS` |
| hint | `mt-2 text-sm text-secondary-500` | `BleetFieldTrait::HINT_CLASS` |
| error | `mt-2 text-sm text-danger-600` | `BleetFieldTrait::ERROR_CLASS` |

The hint and the error are `<p>` elements carrying an id — `<id>-description`
and `<id>-error` — that the input points to through `aria-describedby`.

## Input field

| state | color classes |
|---|---|
| normal | `text-<color>-900` · `outline-<color>-300` · `placeholder:text-<color>-400` · `focus:outline-<color>-600` |
| error | `text-danger-900` · `outline-danger-300` · `placeholder:text-danger-300` · `focus:outline-danger-600` |
| error icon | `text-danger-500` |

The border is an `outline-1 -outline-offset-1` that switches to
`focus:outline-2 focus:-outline-offset-2`, like the *Input groups* block of
Tailwind Plus.

In error, the input moves into a grid (`grid grid-cols-1`) and carries
`aria-invalid="true"`; the `exclamation-circle` icon overlays on the right.

## Checkbox

| part | classes |
|---|---|
| box | `border-<color>-300` · `checked:border-<color>-600` · `checked:bg-<color>-600` |
| indeterminate | `indeterminate:border-<color>-600` · `indeterminate:bg-<color>-600` |
| disabled | `disabled:border-<color>-300` · `disabled:bg-<color>-100` |
| check mark | `stroke-white`, dimmed to `stroke-<color>-950/25` when disabled |
| label | `font-medium text-secondary-900` |
| description | `text-secondary-500` |

## Button

| variant | color classes |
|---|---|
| solid | `bg-<color>-600` · `hover:bg-<color>-500` · `text-white` · `focus-visible:outline-<color>-600` |
| `outline()` | `text-<color>-700` · `hover:bg-<color>-500` · `hover:text-white` |
| `ghost()` | `text-<color>-700` · `border-<color>-300` · `hover:bg-<color>-600` |
| `inverse()` | `bg-white/90` · `text-<color>-700` · `hover:bg-white` |
| badge | `bg-danger-600` · `text-white` |

Sizes follow the *Buttons* block of Tailwind Plus: `xs` is
`px-2 py-1 text-xs`, `sm` is `px-2 py-1 text-sm`, `md` is
`px-2.5 py-1.5 text-sm`, `lg` is `px-3 py-2 text-sm`, `xl` is
`px-3.5 py-2.5 text-sm`.

## Button group

The group dresses its children: the ring, the corners and the overlap come
from the group, the tint stays that of each button.

| part | classes |
|---|---|
| container | `isolate inline-flex rounded-md shadow-xs` |
| each button | `relative inline-flex items-center bg-white px-2 py-2 inset-ring-1 focus:z-10` |
| ring and hover | `inset-ring-<group color>-300` · `hover:bg-<group color>-50` |
| content | `text-<button color>-600` |
| position | `rounded-l-md` on the first, `-ml-px` on the following, `rounded-r-md` on the last |

## Reading rule

- **300**: borders at rest
- **500**: secondary text, icons
- **600**: solid background, active state, focus, error
- **500**: hover on a solid background
- **700**: text on a light background
- **900**: headings and labels
- **100**: disabled backgrounds

The tones are produced by `BleetColorTrait`: `textColorClass()` (700),
`textStrongColorClass()` (900), `textMutedColorClass()`, `borderColorClass()`,
`bgColorClass()`, `focusVisibleRingClasses()`. A component that needs a color
goes through these methods rather than hard-coding the tone.

A Tailwind class is never composed: `'bg-'.$color.'-600'` is never generated,
since Tailwind reads the source and would not see the class. Each tone is
spelled out in full in a `match`.
