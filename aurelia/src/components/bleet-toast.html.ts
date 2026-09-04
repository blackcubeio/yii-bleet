export default `<template>
        <div class="rounded-md border-l-4 p-4 shadow-lg"
             class.bind="'border-'+color+'-300 bg-'+color+'-50'">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="size-5"
                         class.bind="'text-'+color+'-500'"
                         xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path if.bind="icon == 'information-circle'" fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/>
                        <path if.bind="icon == 'check-circle'" fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/>
                        <path if.bind="icon == 'exclamation-triangle'" fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/>
                        <path if.bind="icon == 'x-circle'" fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium"
                       if.bind="title"
                       class.bind="'text-'+color+'-700'">
                        \${title}
                    </p>
                    <div class.bind="title?'mt-2':''">
                        <p class="text-sm"
                            class.bind="'text-'+color+'-700'"
                        innerHTML.bind="content">
                        </p>
                    </div>
                </div>
                <div class="ml-auto pl-3">
                    <button type="button"
                            type="button"
                            class="inline-flex rounded-md cursor-pointer"
                            class.bind="'bg-'+color+'-50 text-'+color+'-500 hover:bg-'+color+'-100'"
                            click.trigger="onClickRemove($event)"
                            data-toast="close">
                        <span class="sr-only">Fermer</span>
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
</template>`