<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [String, Number],
        required: true,
    },
    value: {
        default: null,
    },
    name: {
        required: true,
    },
    label: {
        required: true,
    },
});

const isChecked = computed(() => props.checked == props.value);

const handleChange = (event) => {
    if (event.target.checked) {
        emit('update:checked', props.value);
    }
}
</script>

<template>
    <label :for="props.name + '-' + value" >
    <input
        :id="props.name + '-' + value"
        type="radio"
        :checked="isChecked"
        @change="handleChange"
        class="form-radio h-4 w-4 text-indigo-600 border-gray-300 shadow-sm focus:ring-indigo-500"
    /> {{ props.label }}
    </label>
</template>
