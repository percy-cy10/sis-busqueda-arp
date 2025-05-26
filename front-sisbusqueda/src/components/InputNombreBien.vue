<template>
  <q-input
    v-model="nombre"
    label="Nombre del Bien"
    dense
    outlined
    debounce="400"
    @update:model-value="emit('update:modelValue', nombre)"
    placeholder="Ingrese parte del nombre"
  />
</template>
<script setup>
import { ref, watch } from 'vue'
const emit = defineEmits(['update:modelValue'])
const props = defineProps({ modelValue: String })

const nombre = ref(props.modelValue || '')

watch(() => props.modelValue, val => { nombre.value = val })
watch(nombre, val => emit('update:modelValue', val))
</script>
