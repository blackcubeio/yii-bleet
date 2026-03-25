export default `<template>
    <dialog ref="dialogElement"
            class="fixed inset-0 z-50 size-auto max-h-none max-w-none overflow-y-auto transition ease-in-out duration-300 bg-transparent backdrop:bg-transparent opacity-0">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg">

                <!-- Loader -->
                <div if.bind="loading" class="flex items-center justify-center py-12">
                    <svg class="animate-spin size-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Contenu -->
                <template else>
                    <!-- Header -->
                    <au-compose if.bind="headerView" template.bind="headerView"></au-compose>

                    <!-- Content -->
                    <au-compose if.bind="contentView" template.bind="contentView"></au-compose>

                    <!-- Footer -->
                    <au-compose if.bind="footerView" template.bind="footerView"></au-compose>
                </template>

            </div>
        </div>
    </dialog>
</template>`
