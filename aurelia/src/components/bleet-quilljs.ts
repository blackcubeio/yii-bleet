import { IPlatform, ILogger, bindable, customElement, INode, resolve } from "aurelia";
import Quill, { QuillOptions } from "quill";
import template from './bleet-quilljs.html';

const Link: any = Quill.import('formats/link');
class BlackcubeLink extends Link {
    static create(value: any) {
        let node = super.create(value);
        value = this.sanitize(value);
        node.setAttribute('href', value);
        if(value.startsWith("https://") || value.startsWith("http://") || value.startsWith("://")) {
            // do nothing
        } else {
            node.removeAttribute('target');
        }
        return node;
    }
}
Quill.register(BlackcubeLink, true);

@customElement({ name: 'bleet-quilljs', template })
export class Quilljs {
    private hiddenField!: HTMLInputElement;
    private editorElement!: HTMLElement;
    private quill!: Quill;

    @bindable() public fieldId: string = '';
    @bindable() public fieldName: string = '';
    @bindable() public content: string = '';
    @bindable() public options: QuillOptions = {
        theme: 'snow',
        modules: {
            toolbar:[
                ['bold', 'italic', 'underline'],
                [{ 'list': 'bullet' }],
                ['link']
            ]
        },
        formats: ['bold', 'italic', 'link', 'underline', 'list']
    };

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('<bleet-quilljs>'),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
    ) {
    }

    public attached(): void {
        this.logger.debug('Attached');
        if (this.fieldId) {
            this.hiddenField.id = this.fieldId;
        }
        if (this.fieldName) {
            this.hiddenField.name = this.fieldName;
        }
        this.hiddenField.value = this.content;
        this.editorElement.innerHTML = this.content;
        this.options.theme = 'snow';
        this.quill = new Quill(this.editorElement, this.options);
        this.quill.on('text-change', this.onTextChange);
    }

    private onTextChange = () => {
        this.hiddenField.value = this.editorElement.querySelector('.ql-editor')?.innerHTML ?? '';
        this.logger.trace(this.hiddenField.value);
    };

    public detaching(): void {
        this.quill.off('text-change', this.onTextChange);
        this.logger.debug('Detaching');
    }
}
