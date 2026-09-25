export default `<template>
    <au-slot if.bind="!ajaxedView"></au-slot>
    <au-compose if.bind="ajaxedView" template.bind="ajaxedView"></au-compose>
</template>`;