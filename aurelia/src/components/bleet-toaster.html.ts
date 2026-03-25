export default `<template class="fixed top-4 right-0 z-70 pr-4 flex flex-col gap-4 pointer-events-none">
    <bleet-toast repeat.for="[id, toast] of toasts"
                class="w-90 max-w-full translate-x-full opacity-0 transition-all duration-500 ease-in-out pointer-events-auto"
        id.bind="id"
                 color.bind="toast.color"
                 icon.bind="toast.icon"
                 title.bind="toast.title"
                 content.bind="toast.content"
                 duration.bind="toast.duration"
    ></bleet-toast>
</template>`