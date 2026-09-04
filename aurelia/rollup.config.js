// Final Rollup configuration example
import typescript from '@rollup/plugin-typescript';
import pkg from './package.json' with { type: "json" };

export default {
    plugins: [typescript({})],
    input: 'src/index.ts',
    output: [
        {
            file: pkg.main,
            sourcemap: true,
            format: 'cjs',
            inlineDynamicImports: true
        },
        {
            file: pkg.module,
            sourcemap: true,
            format: 'es',
            inlineDynamicImports: true
        }
    ],
    external: ['aurelia', '@aurelia/fetch-client', 'socket.io-client', 'resumablejs', 'quill']
};