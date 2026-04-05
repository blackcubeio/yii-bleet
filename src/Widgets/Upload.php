<?php

declare(strict_types=1);

/**
 * Upload.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetModelAwareTrait;
use Blackcube\FileProvider\Resumable\ResumableConfig;
use Yiisoft\Html\Html;

/**
 * Upload widget - Zone d'upload avec Resumable.js
 *
 * Usage (with default config):
 *   Bleet::upload()
 *       ->accept(['jpg', 'jpeg', 'png', 'gif', 'pdf'])
 *       ->maxFiles(5)
 *       ->multiple()
 *       ->name('files')
 *       ->render()
 *
 * Usage (avec override endpoints):
 *   Bleet::upload()
 *       ->endpoint('/custom/upload')
 *       ->previewEndpoint('/custom/preview')
 *       ->deleteEndpoint('/custom/delete')
 *       ->name('files')
 *       ->render()
 *
 * Usage (avec active() pour formulaires):
 *   Bleet::upload()
 *       ->active($model, 'document')
 *       ->accept(['pdf', 'jpg'])
 *       ->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Upload extends AbstractWidget
{
    use BleetModelAwareTrait;
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private ResumableConfig $config;
    private ?string $endpoint = null;
    private ?string $previewEndpoint = null;
    private ?string $deleteEndpoint = null;
    private array $accept = [];
    private int $maxFiles = 1;
    private bool $multiple = false;
    private ?int $chunkSize = null;
    private ?string $name = null;
    private ?string $id = null;
    private ?string $value = null;
    private ?string $label = null;
    private ?string $hint = null;
    private bool $disabled = false;
    private bool $required = false;

    public function __construct(?ResumableConfig $config = null)
    {
        $this->config = $config ?? new ResumableConfig();
    }

    /**
     * Sets l'endpoint pour l'upload des chunks
     */
    public function endpoint(string $endpoint): self
    {
        $new = clone $this;
        $new->endpoint = $endpoint;
        return $new;
    }

    /**
     * Sets l'endpoint pour les previews
     * Utiliser {name} comme placeholder pour le nom of the file
     */
    public function previewEndpoint(string $previewEndpoint): self
    {
        $new = clone $this;
        $new->previewEndpoint = $previewEndpoint;
        return $new;
    }

    /**
     * Sets l'endpoint pour la suppression des fichiers temporaires
     */
    public function deleteEndpoint(string $deleteEndpoint): self
    {
        $new = clone $this;
        $new->deleteEndpoint = $deleteEndpoint;
        return $new;
    }

    /**
     * Sets the accepted file extensions
     * @param string[] $accept Ex: ['jpg', 'jpeg', 'png', 'gif', 'pdf']
     */
    public function accept(array $accept): self
    {
        $new = clone $this;
        $new->accept = $accept;
        return $new;
    }

    /**
     * Sets le nombre maximum files
     */
    public function maxFiles(int $maxFiles): self
    {
        $new = clone $this;
        $new->maxFiles = $maxFiles;
        return $new;
    }

    /**
     * Enables le mode multi-fichiers
     */
    public function multiple(bool $multiple = true): self
    {
        $new = clone $this;
        $new->multiple = $multiple;
        return $new;
    }

    /**
     * Sets la taille des chunks en bytes
     */
    public function chunkSize(int $chunkSize): self
    {
        $new = clone $this;
        $new->chunkSize = $chunkSize;
        return $new;
    }

    /**
     * Sets le nom du champ
     */
    public function name(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * Sets the id du widget
     */
    public function id(string $id): self
    {
        $new = clone $this;
        $new->id = $id;
        return $new;
    }

    /**
     * Sets the value initiale (fichiers existants)
     * Format: @blfs/path/file1.jpg,@blfs/path/file2.pdf
     */
    public function value(?string $value): self
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }

    /**
     * Sets the label de la zone de drop
     */
    public function label(string $label): self
    {
        $new = clone $this;
        $new->label = $label;
        return $new;
    }

    /**
     * Sets le hint (texte d'aide)
     */
    public function hint(string $hint): self
    {
        $new = clone $this;
        $new->hint = $hint;
        return $new;
    }

    /**
     * Marks the widget as disabled
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->disabled = $disabled;
        return $new;
    }

    /**
     * Marks le widget comme requis
     */
    public function required(bool $required = true): self
    {
        $new = clone $this;
        $new->required = $required;
        return $new;
    }

    public function render(): string
    {
        return $this->renderView('upload', $this->prepareViewParams());
    }

    /**
     * Prépare les paramètres pour la vue
     * @return array<string, mixed>
     */
    private function prepareViewParams(): array
    {
        // Value resolution : explicit > model > null
        $name = $this->name ?? $this->getInputName();
        $id = $this->id ?? $this->getInputId();
        $value = $this->value ?? $this->getValue();
        $required = $this->required || $this->isRequired();

        // Label resolution: explicit > model > null
        $labelContent = $this->label ?? $this->getLabel();
        $labelHtml = '';
        if ($labelContent !== null && $labelContent !== '') {
            $labelWidget = Bleet::label($labelContent)->color($this->color);
            if ($this->hasModel()) {
                $labelWidget = $labelWidget->active($this->getModel(), $this->getProperty());
            } else {
                if ($id !== null) {
                    $labelWidget = $labelWidget->for($id);
                }
                if ($this->required) {
                    $labelWidget = $labelWidget->required();
                }
            }
            $labelHtml = $labelWidget->render();
        }

        // Container attributes
        $containerAttributes = [
            'class' => implode(' ', $this->prepareClasses()),
            'bleet-upload' => Aurelia::attributesCustomAttribute($this->buildAureliaOptions()),
        ];
        if ($id !== null) {
            $containerAttributes['id'] = $id . '-container';
        }
        if ($this->disabled) {
            $containerAttributes['data-disabled'] = true;
        }
        if ($this->hasErrors()) {
            $containerAttributes['data-error'] = true;
        }

        // Dropzone text
        $dropzoneText = $this->multiple ? 'Drop files here' : 'Drop file here';
        $hint = $this->hint ?? $this->getHint();

        return [
            'name' => $name,
            'id' => $id,
            'value' => $value,
            'required' => $required,
            'disabled' => $this->disabled,
            'labelHtml' => $labelHtml,
            'containerAttributes' => $containerAttributes,
            'dropzoneText' => $dropzoneText,
            'hint' => $hint,
            'dropzoneClasses' => $this->getDropzoneClasses(),
            'dropzoneIconClasses' => $this->getDropzoneIconClasses(),
            'dropzoneTextClasses' => $this->getDropzoneTextClasses(),
            'hintClasses' => $this->getHintClasses(),
            'previewItemClasses' => $this->getPreviewItemClasses(),
            'previewLinkClasses' => $this->getPreviewLinkClasses(),
            'previewImageClasses' => $this->getPreviewImageClasses(),
            'previewIconClasses' => $this->getPreviewIconClasses(),
            'previewNameClasses' => $this->getPreviewNameClasses(),
            'previewRemoveClasses' => $this->getPreviewRemoveClasses(),
        ];
    }

    /**
     * Construit les options pour l'attribut Aurelia bleet-upload
     * @return array<string, mixed>
     */
    private function buildAureliaOptions(): array
    {
        $options = [
            'endpoint' => $this->endpoint ?? $this->config->getUploadEndpoint(),
            'previewEndpoint' => ($this->previewEndpoint ?? $this->config->getPreviewEndpoint()) . '?name=__name__',
            'deleteEndpoint' => ($this->deleteEndpoint ?? $this->config->getDeleteEndpoint()) . '?name=__name__',
            'chunkSize' => $this->chunkSize ?? $this->config->getChunkSize(),
            'maxFiles' => $this->maxFiles,
            'multiple.bind' => $this->multiple,
        ];

        if (!empty($this->accept)) {
            $options['accept'] = implode(',', $this->accept);
        }

        return $options;
    }

    /**
     * Classes for le container principal
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [
            'relative',
        ];
    }

    /**
     * Classes for la dropzone
     * @return string[]
     */
    private function getDropzoneClasses(): array
    {
        $baseClasses = [
            'flex',
            'flex-col',
            'items-center',
            'justify-center',
            'gap-2',
            'rounded-lg',
            'border-2',
            'border-dashed',
            'px-6',
            'py-10',
            'text-center',
            'transition-colors',
        ];

        return [...$baseClasses, ...$this->getDropzoneColorClasses()];
    }

    /**
     * Classes couleur pour la dropzone
     * @return string[]
     */
    private function getDropzoneColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => [
                'border-primary-300',
                'hover:border-primary-400',
                'bg-primary-50/50',
                'hover:bg-primary-50',
            ],
            Bleet::COLOR_SECONDARY => [
                'border-secondary-300',
                'hover:border-secondary-400',
                'bg-secondary-50/50',
                'hover:bg-secondary-50',
            ],
            Bleet::COLOR_SUCCESS => [
                'border-success-300',
                'hover:border-success-400',
                'bg-success-50/50',
                'hover:bg-success-50',
            ],
            Bleet::COLOR_DANGER => [
                'border-danger-300',
                'hover:border-danger-400',
                'bg-danger-50/50',
                'hover:bg-danger-50',
            ],
            Bleet::COLOR_WARNING => [
                'border-warning-300',
                'hover:border-warning-400',
                'bg-warning-50/50',
                'hover:bg-warning-50',
            ],
            Bleet::COLOR_INFO => [
                'border-info-300',
                'hover:border-info-400',
                'bg-info-50/50',
                'hover:bg-info-50',
            ],
            Bleet::COLOR_ACCENT => [
                'border-accent-300',
                'hover:border-accent-400',
                'bg-accent-50/50',
                'hover:bg-accent-50',
            ],
        };
    }

    /**
     * Classes for the icon de la dropzone
     * @return string[]
     */
    private function getDropzoneIconClasses(): array
    {
        $baseClasses = ['size-10'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-400'],
            Bleet::COLOR_SECONDARY => ['text-secondary-400'],
            Bleet::COLOR_SUCCESS => ['text-success-400'],
            Bleet::COLOR_DANGER => ['text-danger-400'],
            Bleet::COLOR_WARNING => ['text-warning-400'],
            Bleet::COLOR_INFO => ['text-info-400'],
            Bleet::COLOR_ACCENT => ['text-accent-400'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * Classes for le texte de la dropzone
     * @return string[]
     */
    private function getDropzoneTextClasses(): array
    {
        $baseClasses = ['block', 'font-medium'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * Classes for le hint de la dropzone
     * @return string[]
     */
    private function getHintClasses(): array
    {
        $baseClasses = ['block', 'text-sm'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-500'],
            Bleet::COLOR_SECONDARY => ['text-secondary-500'],
            Bleet::COLOR_SUCCESS => ['text-success-500'],
            Bleet::COLOR_DANGER => ['text-danger-500'],
            Bleet::COLOR_WARNING => ['text-warning-500'],
            Bleet::COLOR_INFO => ['text-info-500'],
            Bleet::COLOR_ACCENT => ['text-accent-500'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * Classes for le conteneur d'item preview
     * @return string[]
     */
    private function getPreviewItemClasses(): array
    {
        return [
            'flex',
            'items-center',
            'gap-3',
            'p-2',
            'rounded-lg',
            'bg-secondary-50',
            'border',
            'border-secondary-200',
        ];
    }

    /**
     * Classes for le lien de preview
     * @return string[]
     */
    private function getPreviewLinkClasses(): array
    {
        return [
            'shrink-0',
            'size-12',
            'rounded',
            'overflow-hidden',
            'bg-secondary-100',
            'flex',
            'items-center',
            'justify-center',
            'cursor-pointer',
        ];
    }

    /**
     * Classes for l'image de preview
     * @return string[]
     */
    private function getPreviewImageClasses(): array
    {
        return [
            'size-full',
            'object-contain',
            'hidden',
        ];
    }

    /**
     * Classes for l'icône de preview (fichier par défaut)
     * @return string[]
     */
    private function getPreviewIconClasses(): array
    {
        return [
            'size-6',
            'text-secondary-500',
        ];
    }

    /**
     * Classes for le nom du fichier
     * @return string[]
     */
    private function getPreviewNameClasses(): array
    {
        return [
            'text-sm',
            'font-medium',
            'text-secondary-700',
            'truncate',
        ];
    }

    /**
     * Classes for le bouton supprimer
     * @return string[]
     */
    private function getPreviewRemoveClasses(): array
    {
        return [
            'shrink-0',
            'p-1',
            'rounded-full',
            'text-secondary-500',
            'hover:text-danger-500',
            'hover:bg-danger-50',
            'transition-colors',
            'cursor-pointer',
        ];
    }
}
