<template>
  <q-select
    dense
    outlined
    v-model="selected"
    :options="libros"
    option-label="protocolo"
    option-value="id"
    label="Libro *"
    emit-value
    map-options
    :loading="loading"
  >
    <template v-slot:prepend>
      <q-icon name="mdi-book" />
    </template>
  </q-select>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import LibroService from 'src/services/LibroService'

const libros = ref([])   // List of books
const selected = ref(null)  // Selected book id
const loading = ref(false)  // Loading state

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await LibroService.getData()
    libros.value = data
  } finally {
    loading.value = false
  }
})

defineExpose({
  selected
})
</script>
