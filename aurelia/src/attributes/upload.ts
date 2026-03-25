import {customAttribute, bindable, ILogger, INode, IEventAggregator, resolve} from "aurelia";
import Resumable from "resumablejs";
import {Channels, ToasterAction, UiColor, UiToastIcon} from '../enums/event-aggregator';
import {IToaster} from '../interfaces/event-aggregator';

/**
 * Fichier geré par l'attribut
 */
interface UploadedFile {
    name: string;
    shortname: string | undefined;
    previewUrl: string;
    deleteUrl: string;
    file?: Resumable.ResumableFile | null;
}

@customAttribute({ name: 'bleet-upload', defaultProperty: 'endpoint' })
export class BleetUploadCustomAttribute {
    @bindable endpoint: string = '';
    @bindable() previewEndpoint: string = '';
    @bindable() deleteEndpoint: string = '';
    @bindable() accept: string = '';
    @bindable() maxFiles: number = 1;
    @bindable() multiple: boolean = false;
    @bindable() chunkSize: number = 512 * 1024;

    private resumable: Resumable | null = null;
    private dropzone: HTMLElement | null = null;
    private browseButton: HTMLElement | null = null;
    private fileList: HTMLElement | null = null;
    private hiddenInput: HTMLInputElement | null = null;
    private previewTemplate: HTMLTemplateElement | null = null;
    private handledFiles: UploadedFile[] = [];
    private parentForm: HTMLFormElement | null = null;
    private csrfToken: { name: string; value: string } | null = null;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-upload'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
    ) {
        this.logger.trace('constructor');
    }

    public attaching(): void {
        this.dropzone = this.element.querySelector('[data-upload=dropzone]');
        this.browseButton = this.element.querySelector('[data-upload=browse]');
        this.fileList = this.element.querySelector('[data-upload=list]');
        this.hiddenInput = this.element.querySelector('[data-upload=value]') as HTMLInputElement;
        this.previewTemplate = this.element.querySelector('[data-upload=preview-template]') as HTMLTemplateElement;
    }

    public attached(): void {
        if (!this.endpoint || !this.dropzone) {
            this.logger.warn('Missing endpoint or dropzone');
            return;
        }

        if (this.element.hasAttribute('data-disabled')) {
            return;
        }

        this.parentForm = this.element.closest('form');
        this.extractCsrfToken();
        this.initResumable();
        this.setFiles(this.hiddenInput?.value || '');
    }

    public detaching(): void {
        if (this.resumable && this.dropzone) {
            this.dropzone.removeEventListener('dragover', this.onDragEnter);
            this.dropzone.removeEventListener('dragenter', this.onDragEnter);
            this.dropzone.removeEventListener('dragleave', this.onDragLeave);
            this.dropzone.removeEventListener('drop', this.onDragLeave);
        }
    }

    private extractCsrfToken(): void {
        if (!this.parentForm) return;

        const csrfInput = this.parentForm.querySelector('input[name=_csrf]') as HTMLInputElement;
        if (csrfInput) {
            this.csrfToken = {
                name: csrfInput.name,
                value: csrfInput.value
            };
        }
    }

    private initResumable(): void {
        const resumableConfig: Resumable.ConfigurationHash = {
            target: this.endpoint,
            chunkSize: this.chunkSize,
            simultaneousUploads: 3,
            permanentErrors: [400, 404, 415, 422, 500, 501],
            maxChunkRetries: 0
        };

        if (this.accept) {
            const fileTypes = this.accept.split(/\s*,\s*/).filter(v => v.trim() !== '');
            resumableConfig.fileType = fileTypes;
            resumableConfig.fileTypeErrorCallback = (file: Resumable.ResumableFile) => {
                this.showErrorToast(`Le fichier "${file.fileName}" n'est pas un type autorise (${fileTypes.map(t => t.toUpperCase()).join(', ')})`);
            };
        }

        if (this.csrfToken) {
            resumableConfig.headers = {
                'X-CSRF-Token': this.csrfToken.value
            };
        }

        this.resumable = new Resumable(resumableConfig);

        if (!this.resumable.support) {
            this.logger.warn('Resumable.js not supported');
            return;
        }

        if (this.browseButton) {
            this.resumable.assignBrowse(this.browseButton, false);
        }

        if (this.dropzone) {
            this.resumable.assignDrop(this.dropzone);
            this.dropzone.addEventListener('dragover', this.onDragEnter);
            this.dropzone.addEventListener('dragenter', this.onDragEnter);
            this.dropzone.addEventListener('dragleave', this.onDragLeave);
            this.dropzone.addEventListener('drop', this.onDragLeave);
        }

        this.resumable.on('fileAdded', this.onFileAdded);
        this.resumable.on('fileSuccess', this.onFileSuccess);
        this.resumable.on('fileError', this.onFileError);
    }

    /**
     * Charge les fichiers depuis une valeur (initialisation)
     */
    private setFiles(value: string): void {
        const files = value.split(/\s*,\s*/).filter(v => v.trim() !== '');
        this.handledFiles = files.map(name => ({
            name,
            shortname: name.split(/.*[\/|\\]/).pop(),
            previewUrl: this.generatePreviewUrl(name),
            deleteUrl: this.generateDeleteUrl(name)
        }));
        this.renderFileList();
        this.updateHiddenInput();
    }

    /**
     * Remplace tous les fichiers par un seul (mode single)
     */
    private setFile(name: string, file: Resumable.ResumableFile | null = null): void {
        // Supprimer les anciens fichiers temporaires
        this.handledFiles.forEach(f => {
            if (f.file && this.resumable) {
                this.resumable.removeFile(f.file);
            }
            this.deleteFileOnServer(f.name);
        });

        this.handledFiles = [{
            name,
            shortname: name.split(/.*[\/|\\]/).pop(),
            previewUrl: this.generatePreviewUrl(name),
            deleteUrl: this.generateDeleteUrl(name),
            file
        }];
        this.renderFileList();
        this.updateHiddenInput();
    }

    /**
     * Ajoute un fichier (mode multiple)
     */
    private appendFile(name: string, file: Resumable.ResumableFile | null = null): void {
        this.handledFiles.push({
            name,
            shortname: name.split(/.*[\/|\\]/).pop(),
            previewUrl: this.generatePreviewUrl(name),
            deleteUrl: this.generateDeleteUrl(name),
            file
        });
        this.renderFileList();
        this.updateHiddenInput();
    }

    /**
     * Supprime un fichier
     */
    private onRemove(handledFile: UploadedFile, evt: Event): void {
        evt.stopPropagation();
        evt.preventDefault();

        const index = this.handledFiles.findIndex(f => f.name === handledFile.name);
        if (index === -1) return;

        if (handledFile.file && this.resumable) {
            this.resumable.removeFile(handledFile.file);
        }

        this.deleteFileOnServer(handledFile.name);
        this.handledFiles.splice(index, 1);
        this.renderFileList();
        this.updateHiddenInput();
    }

    private deleteFileOnServer(name: string): void {
        // Ne supprimer que les fichiers temporaires
        if (!name || !name.startsWith('@bltmp/')) return;

        const deleteUrl = this.generateDeleteUrl(name);
        if (!deleteUrl) return;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: this.csrfToken ? {
                'X-CSRF-Token': this.csrfToken.value
            } : {}
        }).catch(e => this.logger.error('Delete failed', e));
    }

    private generatePreviewUrl(name: string): string {
        if (!this.previewEndpoint) return '';
        return this.previewEndpoint.replace('__name__', encodeURIComponent(name));
    }

    private generateDeleteUrl(name: string): string {
        if (!this.deleteEndpoint) return '';
        return this.deleteEndpoint.replace('__name__', encodeURIComponent(name));
    }

    private updateHiddenInput(): void {
        if (!this.hiddenInput) return;
        this.hiddenInput.value = this.handledFiles.map(f => f.name).join(', ');
        this.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    private renderFileList(): void {
        if (!this.fileList || !this.previewTemplate) return;

        this.fileList.innerHTML = '';

        this.handledFiles.forEach(handledFile => {
            const fragment = this.previewTemplate!.content.cloneNode(true) as DocumentFragment;
            const item = fragment.firstElementChild as HTMLElement;

            // Preview link
            const previewLink = item.querySelector('[data-upload=preview-link]') as HTMLAnchorElement;
            if (previewLink) {
                previewLink.href = handledFile.previewUrl ? `${handledFile.previewUrl}&original=1` : '#';
            }

            // Preview image et icon
            const previewImage = item.querySelector('[data-upload=preview-image]') as HTMLImageElement;
            const previewIcon = item.querySelector('[data-upload=preview-icon]') as HTMLElement;

            if (handledFile.previewUrl) {
                this.loadPreview(previewImage, previewIcon, handledFile);
            }
            // Si pas de previewUrl, l'icône reste visible (hidden est sur l'image par défaut)

            // Nom du fichier
            const nameEl = item.querySelector('[data-upload=preview-name]') as HTMLElement;
            if (nameEl) {
                nameEl.textContent = handledFile.shortname || '';
            }

            // Bouton supprimer
            const removeBtn = item.querySelector('[data-upload=preview-remove]') as HTMLButtonElement;
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => this.onRemove(handledFile, e));
            }

            this.fileList!.appendChild(fragment);
        });
    }

    private loadPreview(previewImage: HTMLImageElement, previewIcon: HTMLElement, handledFile: UploadedFile): void {
        const shortname = handledFile.shortname || '';

        if (shortname.toLowerCase().endsWith('.svg')) {
            // SVG : fetch et inline dans le container parent (le lien)
            fetch(handledFile.previewUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load SVG');
                    return response.text();
                })
                .then(svgContent => {
                    // Cacher l'icône par défaut
                    previewIcon.classList.add('hidden');
                    // Insérer le SVG à la place de l'image
                    previewImage.insertAdjacentHTML('afterend', svgContent);
                    const svg = previewImage.parentElement?.querySelector('svg:not([data-upload])');
                    if (svg) {
                        svg.classList.add('size-full');
                        svg.removeAttribute('width');
                        svg.removeAttribute('height');
                    }
                })
                .catch(() => {
                    // Garder l'icône visible en cas d'erreur
                });
        } else {
            // Autres fichiers : utiliser l'image du template
            previewImage.src = handledFile.previewUrl;
            previewImage.alt = shortname;
            previewImage.onload = () => {
                previewImage.classList.remove('hidden');
                previewIcon.classList.add('hidden');
            };
            previewImage.onerror = () => {
                // Garder l'icône visible en cas d'erreur
            };
        }
    }

    private showErrorToast(message: string): void {
        this.ea.publish(Channels.Toaster, <IToaster>{
            action: ToasterAction.Add,
            toast: {
                id: `upload-error-${Date.now()}`,
                duration: 5000,
                color: UiColor.Danger,
                icon: UiToastIcon.Danger,
                title: 'Erreur',
                content: message
            }
        });
    }

    // Resumable.js event handlers
    private onDragEnter = (evt: DragEvent): void => {
        evt.preventDefault();
        const dt = evt.dataTransfer;
        if (dt && dt.types.indexOf('Files') >= 0) {
            evt.stopPropagation();
            dt.dropEffect = 'copy';
            this.dropzone?.classList.add('border-primary-600', 'bg-primary-50');
        }
    };

    private onDragLeave = (evt: Event): void => {
        this.dropzone?.classList.remove('border-primary-600', 'bg-primary-50');
    };

    private onFileAdded = (file: Resumable.ResumableFile, event: DragEvent): void => {
        this.logger.debug('onFileAdded', file.fileName);
        this.resumable?.upload();
    };

    private onFileSuccess = (file: Resumable.ResumableFile, serverMessage: string): void => {
        this.logger.debug('onFileSuccess', file.fileName, serverMessage);

        try {
            const response = JSON.parse(serverMessage);
            if (!response.finalFilename) {
                throw new Error('Missing finalFilename in response');
            }

            const finalName = `@bltmp/${response.finalFilename}`;

            if (!this.multiple) {
                this.setFile(finalName, file);
            } else {
                this.appendFile(finalName, file);
            }
        } catch (e) {
            this.logger.error('Failed to parse server response', e);
            this.showErrorToast('Reponse serveur invalide');
        }
    };

    private onFileError = (file: Resumable.ResumableFile, message: string): void => {
        this.logger.error('onFileError', file.fileName, message);
        this.showErrorToast(`Echec de l'upload de "${file.fileName}"`);
    };
}
