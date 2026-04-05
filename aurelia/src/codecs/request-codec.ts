import {ICodec, IHttpRequest} from '../interfaces/api';

export class RequestCodec {

    public static codec: ICodec = {
        encode: (ctx: IHttpRequest) => {
            return Promise.resolve({
                ...ctx,
                headers: {
                    ...ctx.headers,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
        }
    };
}
