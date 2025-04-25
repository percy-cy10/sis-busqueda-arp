<template>
  <q-select
    dense
    outlined
    v-model="selected"
    :options="favorecidos"
    option-label="nombre_completo"
    option-value="id"
    label="Favorecido *"
    emit-value
    map-options
    :loading="loading"
  >
    <template v-slot:prepend>
      <q-icon name="mdi-account" />
    </template>
  </q-select>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FavorecidoService from 'src/services/FavorecidoService'

const favorecidos = ref([])   // List of favorecidos
const selected = ref(null)    // Selected favorecido id
const loading = ref(false)    // Loading state

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await FavorecidoService.getData()
    favorecidos.value = data
  } finally {
    loading.value = false
  }
})

defineExpose({
  selected
})
</script>
