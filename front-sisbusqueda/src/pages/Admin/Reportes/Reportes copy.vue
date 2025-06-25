<template>
  <q-page class="q-pa-md">
    <div class="q-mb-md">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" to="/" />
        <q-breadcrumbs-el label="Reportes" icon="assessment" />
      </q-breadcrumbs>
      <q-separator class="q-my-sm" />
    </div>

    <!-- Filtros de fecha -->
    <div class="row q-gutter-md items-center q-mb-md">
      <q-input filled v-model="fechaInicio" type="date" label="Desde" dense style="max-width: 180px;" />
      <q-input filled v-model="fechaFin" type="date" label="Hasta" dense style="max-width: 180px;" />
      <q-btn color="primary" label="Filtrar" @click="filtrarVentas" :disable="loading" />
      <q-space />
      <q-btn color="green" icon="file_download" label="Excel" @click="exportarExcel" :disable="ventasFiltradas.length === 0" class="q-mr-sm" />
      <q-btn color="red" icon="picture_as_pdf" label="PDF" @click="exportarPDF" :disable="ventasFiltradas.length === 0" />
    </div>

    <!-- Tabla de ventas -->
    <q-table
      :rows="ventasFiltradas"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :rows-per-page-options="[10, 20, 50]"
      title="Ventas"
      class="my-sticky-header-table"
    >
      <template v-slot:no-data>
        <div class="full-width row flex-center text-grey q-gutter-sm">
          <q-icon name="warning" size="2em" />
          <span v-if="loading">Cargando datos...</span>
          <span v-else>No se encontraron ventas</span>
        </div>
      </template>
    </q-table>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import * as XLSX from 'xlsx'
import jsPDF from 'jspdf'
import 'jspdf-autotable'

// Columnas de la tabla
const columns = [
  { name: 'id', label: 'ID', align: 'left', field: row => row.id, sortable: true },
  { name: 'nombre', label: 'Nombre', align: 'left', field: row => row.nombre, sortable: true },
  { name: 'producto', label: 'Producto', align: 'left', field: row => row.producto, sortable: true },
  { name: 'fecha', label: 'Fecha', align: 'left', field: row => row.fecha, sortable: true },
  { name: 'monto', label: 'Monto', align: 'right', field: row => row.monto, sortable: true }
]

const $q = useQuasar()
const loading = ref(false)
const fechaInicio = ref('')
const fechaFin = ref('')
const ventas = ref([
  { id: 1, nombre: 'Juan Pérez', producto: 'Producto A', fecha: '2025-06-01', monto: 120 },
  { id: 2, nombre: 'Ana Ruiz', producto: 'Producto B', fecha: '2025-06-10', monto: 200 },
  { id: 3, nombre: 'Luis Soto', producto: 'Producto C', fecha: '2025-06-15', monto: 150 },
  // ...más datos
])
const ventasFiltradas = ref([])

onMounted(() => {
  ventasFiltradas.value = ventas.value
})

function filtrarVentas() {
  ventasFiltradas.value = ventas.value.filter(v => {
    return (!fechaInicio.value || v.fecha >= fechaInicio.value) &&
           (!fechaFin.value || v.fecha <= fechaFin.value)
  })
}

function exportarExcel() {
  const ws = XLSX.utils.json_to_sheet(ventasFiltradas.value)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Ventas')
  XLSX.writeFile(wb, 'reporte_ventas.xlsx')
}

function exportarPDF() {
  const doc = new jsPDF()
  doc.text('Reporte de Ventas', 10, 10)
  doc.autoTable({
    head: [['ID', 'Nombre', 'Producto', 'Fecha', 'Monto']],
    body: ventasFiltradas.value.map(v => [v.id, v.nombre, v.producto, v.fecha, v.monto])
  })
  doc.save('reporte_ventas.pdf')
}
</script>

<style scoped>
.my-sticky-header-table {
  height: calc(100vh - 220px);
}
</style>
