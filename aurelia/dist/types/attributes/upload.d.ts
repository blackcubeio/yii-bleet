import { ILogger, IEventAggregator } from "aurelia";
export declare class BleetUploadCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly ea;
    endpoint: string;
    previewEndpoint: string;
    deleteEndpoint: string;
    accept: string;
    maxFiles: number;
    multiple: boolean;
    chunkSize: number;
    private resumable;
    private dropzone;
    private browseButton;
    private fileList;
    private hiddenInput;
    private previewTemplate;
    private handledFiles;
    private parentForm;
    private csrfToken;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator);
    attaching(): void;
    attached(): void;
    detaching(): void;
    private extractCsrfToken;
    private initResumable;
    /**
     * Charge les fichiers depuis une valeur (initialisation)
     */
    private setFiles;
    /**
     * Remplace tous les fichiers par un seul (mode single)
     */
    private setFile;
    /**
     * Ajoute un fichier (mode multiple)
     */
    private appendFile;
    /**
     * Supprime un fichier
     */
    private onRemove;
    private deleteFileOnServer;
    private generatePreviewUrl;
    private generateDeleteUrl;
    private updateHiddenInput;
    private renderFileList;
    private loadPreview;
    private showErrorToast;
    private onDragEnter;
    private onDragLeave;
    private onFileAdded;
    private onFileSuccess;
    private onFileError;
}
//# sourceMappingURL=upload.d.ts.map