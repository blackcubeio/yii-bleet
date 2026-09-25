export default `<template>
    <dialog ref="dialogElement"
            class="fixed inset-0 z-50 size-auto max-h-none max-w-none overflow-hidden backdrop:bg-transparent bg-transparent transform translate-x-full transition ease-in-out duration-300">
        <div class="absolute inset-0 pl-10 sm:pl-16 overflow-hidden">
            <div class="ml-auto flex flex-col h-full w-full sm:w-2/3 sm:min-w-md transform bg-white shadow-xl">

                <!-- Loader -->
                <div if.bind="loading" class="flex items-center justify-center h-full">
                    <svg class="animate-spin size-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Contenu -->
                <template else>
                    <!-- Header (fixed) -->
                    <div class="shrink-0">
                        <au-compose if.bind="headerView" template.bind="headerView"></au-compose>
                    </div>

                    <!-- Content (scrollable) -->
                    <div class="flex-1 overflow-y-auto">
                        <au-compose if.bind="contentView" template.bind="contentView"></au-compose>
                    </div>

                    <!-- Footer (fixed) -->
                    <div class="shrink-0">
                        <au-compose if.bind="footerView" template.bind="footerView"></au-compose>
                    </div>
                </template>

            </div>
        </div>
    </dialog>
</template>`
