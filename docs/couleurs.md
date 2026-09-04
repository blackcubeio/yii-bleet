# Couleurs des champs et des boutons

Ce document fige les tons employés par les composants de formulaire, pour
qu'un markup écrit à la main tombe sur les mêmes valeurs que `Bleet::input()`
ou `Bleet::button()`.

Les sept couleurs sémantiques — `primary`, `secondary`, `success`, `danger`,
`warning`, `info`, `accent` — remplacent les couleurs de Tailwind Plus :
`gray` devient `secondary`, `red` devient `danger`, `indigo` devient
`primary`.

## Bloc d'un champ

| partie | classes | source |
|---|---|---|
| conteneur | *(aucune)* | `<div>` nu |
| libellé | `block text-sm/6 font-medium text-secondary-900` | `BleetFieldTrait::LABEL_CLASS` |
| conteneur de l'input | `mt-2` | `BleetFieldTrait::INPUT_CONTAINER_CLASS` |
| aide | `mt-2 text-sm text-secondary-500` | `BleetFieldTrait::HINT_CLASS` |
| erreur | `mt-2 text-sm text-danger-600` | `BleetFieldTrait::ERROR_CLASS` |

L'aide et l'erreur sont des `<p>` portant un identifiant — `<id>-description`
et `<id>-error` — vers lequel l'input pointe par `aria-describedby`.

## Champ de saisie

| état | classes de couleur |
|---|---|
| normal | `text-<couleur>-900` · `outline-<couleur>-300` · `placeholder:text-<couleur>-400` · `focus:outline-<couleur>-600` |
| erreur | `text-danger-900` · `outline-danger-300` · `placeholder:text-danger-300` · `focus:outline-danger-600` |
| icône d'erreur | `text-danger-500` |

La bordure est un `outline-1 -outline-offset-1` qui passe à
`focus:outline-2 focus:-outline-offset-2`, comme le bloc *Input groups* de
Tailwind Plus.

En erreur, l'input passe dans une grille (`grid grid-cols-1`) et porte
`aria-invalid="true"` ; l'icône `exclamation-circle` se superpose à droite.

## Case à cocher

| partie | classes |
|---|---|
| case | `border-<couleur>-300` · `checked:border-<couleur>-600` · `checked:bg-<couleur>-600` |
| indéterminée | `indeterminate:border-<couleur>-600` · `indeterminate:bg-<couleur>-600` |
| désactivée | `disabled:border-<couleur>-300` · `disabled:bg-<couleur>-100` |
| coche | `stroke-white`, atténuée en `stroke-<couleur>-950/25` quand désactivée |
| libellé | `font-medium text-secondary-900` |
| explication | `text-secondary-500` |

## Bouton

| variante | classes de couleur |
|---|---|
| plein | `bg-<couleur>-600` · `hover:bg-<couleur>-500` · `text-white` · `focus-visible:outline-<couleur>-600` |
| `outline()` | `text-<couleur>-700` · `hover:bg-<couleur>-500` · `hover:text-white` |
| `ghost()` | `text-<couleur>-700` · `border-<couleur>-300` · `hover:bg-<couleur>-600` |
| `inverse()` | `bg-white/90` · `text-<couleur>-700` · `hover:bg-white` |
| pastille | `bg-danger-600` · `text-white` |

Les tailles suivent le bloc *Buttons* de Tailwind Plus : `xs` en
`px-2 py-1 text-xs`, `sm` en `px-2 py-1 text-sm`, `md` en
`px-2.5 py-1.5 text-sm`, `lg` en `px-3 py-2 text-sm`, `xl` en
`px-3.5 py-2.5 text-sm`.

## Groupe de boutons

Le groupe habille ses enfants : l'anneau, les coins et le chevauchement
viennent du groupe, la teinte reste celle de chaque bouton.

| partie | classes |
|---|---|
| conteneur | `isolate inline-flex rounded-md shadow-xs` |
| chaque bouton | `relative inline-flex items-center bg-white px-2 py-2 inset-ring-1 focus:z-10` |
| anneau et survol | `inset-ring-<couleur du groupe>-300` · `hover:bg-<couleur du groupe>-50` |
| contenu | `text-<couleur du bouton>-600` |
| position | `rounded-l-md` au premier, `-ml-px` aux suivants, `rounded-r-md` au dernier |

## Règle de lecture

- **300** : bordures au repos
- **500** : texte secondaire, icônes
- **600** : fond plein, état actif, focus, erreur
- **500** : survol d'un fond plein
- **700** : texte sur fond clair
- **900** : titres et libellés
- **100** : fonds désactivés

Les tons sont produits par `BleetColorTrait` : `textColorClass()` (700),
`textStrongColorClass()` (900), `textMutedColorClass()`, `borderColorClass()`,
`bgColorClass()`, `focusVisibleRingClasses()`. Un composant qui a besoin d'une
couleur passe par ces méthodes plutôt que d'écrire le ton en dur.

Une classe Tailwind ne se compose jamais : `'bg-'.$couleur.'-600'` n'est
jamais généré, puisque Tailwind lit le source et ne verrait pas la classe.
Chaque ton est écrit en toutes lettres dans un `match`.
