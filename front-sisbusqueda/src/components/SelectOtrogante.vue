<template>
  <q-select
    dense
    outlined
    v-model="selected"
    :options="otorgantes"
    option-label="nombre_completo"
    option-value="id"
    label="Otorgante *"
    emit-value
    map-options
    :loading="loading"
  >
    <template v-slot:prepend>
      <q-icon name="mdi-account-outline" />
    </template>
  </q-select>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OtorganteService from 'src/services/OtorganteService'

const otorgantes = ref([])   // List of otorgantes
const selected = ref(null)   // Selected otorgante id
const loading = ref(false)   // Loading state

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await OtorganteService.getData()
    otorgantes.value = data
  } finally {
    loading.value = false
  }
})

defineExpose({
  selected
})
</script>
