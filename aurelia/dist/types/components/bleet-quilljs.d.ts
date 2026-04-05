import { IPlatform, ILogger } from "aurelia";
import { QuillOptions } from "quill";
export declare class Quilljs {
    private readonly logger;
    private readonly platform;
    private readonly element;
    private hiddenField;
    private editorElement;
    private quill;
    fieldId: string;
    fieldName: string;
    content: string;
    options: QuillOptions;
    constructor(logger?: ILogger, platform?: IPlatform, element?: HTMLElement);
    attached(): void;
    private onTextChange;
    detaching(): void;
}
//# sourceMappingURL=bleet-quilljs.d.ts.map