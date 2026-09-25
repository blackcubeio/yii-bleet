<?php

declare(strict_types=1);

/**
 * Upload.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Form\Field\Upload as BaseUpload;
use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Bleet\Traits\BleetFieldTrait;
use Yiisoft\Html\Html;
use Blackcube\FileProvider\Resumable\ResumableConfig;
use Blackcube\Form\Aurelia;

/**
 * Upload styled with the Bleet colors.
 *
 * The field mechanics live in blackcube/yii-form; what follows is only the
 * styling: the classes, the shades, the sizes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Upload extends BaseUpload
{
    use BleetColorTrait;
    use BleetFieldTrait;
    use RenderViewTrait;

    protected ResumableConfig $config;
    protected ?string $endpoint = null;
    protected ?string $previewEndpoint = null;
    protected ?string $deleteEndpoint = null;
    protected ?int $maxFiles = null;
    protected ?int $chunkSize = null;
    protected ?string $id = null;

    /**
     * Sets the endpoint for uploading the chunks
     */
    public function __construct(?ResumableConfig $config = null)
    {
        $this->config = $config ?? new ResumableConfig();
    }

    public function endpoint(string $endpoint): self
    {
        $new = clone $this;
        $new->endpoint = $endpoint;
        return $new;
    }

    /**
     * Sets the endpoint for the previews
     * Use {name} as a placeholder for the file name
     */
    public function previewEndpoint(string $previewEndpoint): self
    {
        $new = clone $this;
        $new->previewEndpoint = $previewEndpoint;
        return $new;
    }

    /**
     * Sets the endpoint for deleting the temporary files
     */
    public function deleteEndpoint(string $deleteEndpoint): self
    {
        $new = clone $this;
        $new->deleteEndpoint = $deleteEndpoint;
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
     * Sets the chunk size in bytes
     */
    public function chunkSize(int $chunkSize): self
    {
        $new = clone $this;
        $new->chunkSize = $chunkSize;
        return $new;
    }

    protected function generateInput(): string
    {
        return $this->renderView('upload', $this->prepareViewParams());
    }

    /**
     * Prepares the view parameters
     * @return array<string, mixed>
     */
    protected function prepareViewParams(): array
    {
        $inputData = $this->getInputData();
        $name = $inputData->getName();
        $id = ($this->getTagAttributes()['id'] ?? null) ?? $inputData->getId();
        $value = $inputData->getValue();
        $required = $this->required;

        $containerAttributes = [
            'class' => implode(' ', $this->prepareClasses()),
            'bleet-upload' => Aurelia::attributesCustomAttribute($this->buildAureliaOptions()),
        ];
        if ($id !== null) {
            $containerAttributes['id'] = $id.'-container';
        }
        if ($this->disabled === true) {
            $containerAttributes['data-disabled'] = true;
        }
        if ($inputData->getValidationErrors() !== []) {
            $containerAttributes['data-error'] = true;
        }

        $dropzoneText = $this->multiple === true ? 'Drop files here' : 'Drop file here';

        return [
            'name' => $name,
            'id' => $id,
            'value' => $value,
            'required' => $required,
            'disabled' => $this->disabled,
            'containerAttributes' => $containerAttributes,
            'dropzoneText' => $dropzoneText,
            'hint' => null,
            'browseButton' => $this->renderBrowseButton(),
            'previewListClasses' => $this->previewListClasses(),
            'removeIconClasses' => $this->removeIconClasses(),
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
     * Builds the options for the bleet-upload Aurelia attribute
     * @return array<string, mixed>
     */
    protected function buildAureliaOptions(): array
    {
        $options = [
            'endpoint' => $this->endpoint ?? $this->config->getUploadEndpoint(),
            'previewEndpoint' => ($this->previewEndpoint ?? $this->config->getPreviewEndpoint()).'?name=__name__',
            'deleteEndpoint' => ($this->deleteEndpoint ?? $this->config->getDeleteEndpoint()).'?name=__name__',
            'chunkSize' => $this->chunkSize ?? $this->config->getChunkSize(),
            'maxFiles' => $this->maxFiles,
            'multiple.bind' => $this->multiple,
        ];

        if (empty($this->accept) === false) {
            $options['accept'] = implode(',', $this->accept);
        }

        return $options;
    }

    /**
     * The button opening the file picker. An interface project renders its own
     * by overriding this method.
     */
    protected function renderBrowseButton(): string
    {
        $attributes = [
            'type' => 'button',
            'data-upload' => 'browse',
        ];

        if ($this->disabled === true) {
            $attributes['disabled'] = true;
        }

        return Html::button('parcourir', $attributes)->render();
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
    protected function getDropzoneClasses(): array
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
     * Color classes for the dropzone
     * @return string[]
     */
    protected function getDropzoneColorClasses(): array
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
    protected function getDropzoneIconClasses(): array
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
     * Classes for the dropzone text
     * @return string[]
     */
    protected function getDropzoneTextClasses(): array
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
     * Classes for the dropzone hint
     * @return string[]
     */
    protected function getHintClasses(): array
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
    protected function getPreviewItemClasses(): array
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
     * Classes for the preview link
     * @return string[]
     */
    protected function getPreviewLinkClasses(): array
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
    protected function getPreviewImageClasses(): array
    {
        return [
            'size-full',
            'object-contain',
            'hidden',
        ];
    }

    /**
     * Classes for the preview icon (default file)
     * @return string[]
     */
    protected function getPreviewIconClasses(): array
    {
        return [
            'size-6',
            'text-secondary-500',
        ];
    }

    /**
     * Classes for the file name
     * @return string[]
     */
    protected function getPreviewNameClasses(): array
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
    protected function getPreviewRemoveClasses(): array
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

    /**
     * @return string[]
     */
    protected function previewListClasses(): array
    {
        return [
            'mt-4',
            'space-y-2',
        ];
    }

    /**
     * @return string[]
     */
    protected function removeIconClasses(): array
    {
        return ['size-5'];
    }
}
