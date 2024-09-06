<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg" :class="maxSize">
            <slot></slot>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    size: {
        type: String,
        default: 'sm' // xs,sm,md,lg,xl,2xl,3xl,4xl,5xl,6xl,7xl
    }
});

const maxSize = computed(() => {
    return ['max-w-' + props.size, ' w-full'];
});

const emit = defineEmits(['update:show']);

const close = () => {
    emit('update:show', false);
};
</script>
