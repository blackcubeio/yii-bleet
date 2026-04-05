import { ICodec, IHttpRequest } from '../interfaces/api';
import { CsrfConfig } from '../configure';

export class CsrfCodec {

    public static codec: ICodec = {
        encode: (ctx: IHttpRequest) => {
            const meta = document.querySelector('meta[name="csrf"]');
            const token = meta?.getAttribute('content');
            if (!token) {
                return Promise.resolve(ctx);
            }
            return Promise.resolve({
                ...ctx,
                headers: {
                    ...ctx.headers,
                    'X-CSRF-Token': token,
                }
            });
        }
    };

    public static fromConfig(config: CsrfConfig): ICodec {
        return {
            encode: (ctx: IHttpRequest) => {
                const meta = document.querySelector(`meta[name="${config.metaName}"]`);
                const token = meta?.getAttribute('content');
                if (!token) {
                    return Promise.resolve(ctx);
                }
                return Promise.resolve({
                    ...ctx,
                    headers: {
                        ...ctx.headers,
                        [config.headerName]: token,
                    }
                });
            }
        };
    }
}
