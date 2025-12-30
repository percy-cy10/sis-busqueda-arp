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

      <!-- Filtro por estado -->
      <q-select
        filled
        v-model="estadoFiltro"
        :options="estadoOptions"
        label="Estado"
        dense
        style="max-width: 180px;"
        clearable
      />

      <!-- Selector de usuarios (solo para administrador) -->
      <q-select
        v-if="usuarioActual.id === 1"
        filled
        v-model="usuarioFiltro"
        :options="usuariosOptions"
        option-label="name"
        option-value="id"
        label="Usuario"
        dense
        style="max-width: 180px;"
        clearable
        emit-value
        map-options
      />

      <q-btn color="primary" label="Filtrar" @click="filtrarPagos" :disable="loading" />
      <q-space />
      <q-btn color="green" icon="file_download" label="Excel" @click="exportarExcel" :disable="pagosFiltrados.length === 0" class="q-mr-sm" />
      <q-btn color="red" icon="picture_as_pdf" label="PDF" @click="exportarPDF" :disable="pagosFiltrados.length === 0" />
    </div>

    <!-- Dashboard -->
    <div class="row q-gutter-md q-mb-md">
      <q-card class="q-pa-md" style="min-width:200px;">
        <div class="text-h6">Total Pagos</div>
        <div class="text-h5 text-primary">S/ {{ totalPagado }}</div>
      </q-card>
      <q-card class="q-pa-md" style="min-width:200px;">
        <div class="text-h6">Cantidad de Pagos</div>
        <div class="text-h5 text-primary">{{ pagosFiltrados.length }}</div>
      </q-card>
      <q-card class="q-pa-md" style="min-width:200px;">
        <div class="text-h6">Usuario</div>
        <div class="text-h6 text-primary">{{ nombreUsuarioReporte }}</div>
      </q-card>
    </div>

    <!-- Tabla de pagos -->
    <q-table
      :rows="pagosFiltrados"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :rows-per-page-options="[10, 20, 50]"
      :title="tituloTabla"
      class="my-sticky-header-table"
    >
      <!-- SLOT para mostrar HTML en la columna TUPAS -->
      <template v-slot:body-cell-tupas="props">
        <q-td :props="props">
          <div v-html="props.row.tupas && props.row.tupas.length
            ? props.row.tupas.map(t => `${t.denominacion} (${t.pivot?.cantidad ?? 1})`).join('<br/>')
            : '-'">
          </div>
        </q-td>
      </template>

      <!-- SLOT para mostrar quién actualizó -->
      <template v-slot:body-cell-actualizado_por="props">
        <q-td :props="props">
          <div>
            <div>{{ props.row.actualizador?.name || 'Sistema' }}</div>
            <div class="text-caption text-grey">{{ formatFecha(props.row.updated_at) }}</div>
          </div>
        </q-td>
      </template>

      <template v-slot:no-data>
        <div class="full-width row flex-center text-grey q-gutter-sm">
          <q-icon name="warning" size="2em" />
          <span v-if="loading">Cargando datos...</span>
          <span v-else>No se encontraron pagos</span>
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
import autoTable from 'jspdf-autotable'
import PagoService from 'src/services/PagoService'
import UsuarioService from 'src/services/UsuarioService'

const $q = useQuasar()
const loading = ref(false)
const fechaInicio = ref('')
const fechaFin = ref('')
const estadoFiltro = ref(null)
const usuarioFiltro = ref(null) // Solo para administrador
const usuariosOptions = ref([]) // Lista de usuarios para el selector
const pagos = ref([])
const pagosFiltrados = ref([])

const estadoOptions = [
  { label: 'Pagados', value: 0 },
  { label: 'Anulados', value: null }
]

const columns = [
  { name: 'id', label: 'ID', align: 'left', field: row => row.id, sortable: true },
  { name: 'boleta_id', label: 'ID Boleta', align: 'left', field: row => row.boleta_id, sortable: true },
  { name: 'solicitud_code', label: 'Solicitud', align: 'left', field: row => row.solicitud?.solicitud_code, sortable: true },
  {
    name: 'tupas',
    label: 'Concepto / Denominacion',
    align: 'left',
    field: row => row.tupas
  },
  { name: 'nombre_completo', label: 'Nombre', align: 'left', field: row => row.nombre_completo, sortable: true },
  { name: 'tipo_documento', label: 'Tipo Doc', align: 'left', field: row => row.tipo_documento, sortable: true },
  { name: 'num_documento', label: 'N° Doc', align: 'left', field: row => row.num_documento, sortable: true },
  {
    name: 'total',
    label: 'Total',
    align: 'center',
    field: row => row.estado === null ? 'Anulado' : `S/. ${row.total}`,
    sortable: true,
    style: 'font-weight: bold'
  },
  {
    name: 'actualizado_por',
    label: 'Actualizado Por',
    align: 'left',
    field: row => row.actualizador?.name
  }
]

function getUserFromStorage() {
  try {
    const raw = localStorage.getItem('user')
    return raw ? JSON.parse(raw) : {}
  } catch (e) {
    return {}
  }
}

const usuarioActual = ref(getUserFromStorage())

// Computed para el título de la tabla
const tituloTabla = computed(() => {
  if (usuarioActual.value.id === 1 && usuarioFiltro.value) {
    const usuario = usuariosOptions.value.find(u => u.id === usuarioFiltro.value)
    return `Pagos Realizados por ${usuario ? usuario.name : 'Usuario'}`
  }
  return 'Mis Pagos Realizados'
})

// Computed para el nombre del usuario en el dashboard
const nombreUsuarioReporte = computed(() => {
  if (usuarioActual.value.id === 1) {
    if (usuarioFiltro.value) {
      const usuario = usuariosOptions.value.find(u => u.id === usuarioFiltro.value)
      return usuario ? usuario.name : 'Todos'
    }
    return 'Todos los usuarios'
  }
  return usuarioActual.value.name || 'Caja'
})

function formatFecha(fecha) {
  if (!fecha) return '-';
  return new Date(fecha).toLocaleDateString('es-PE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

onMounted(async () => {
  // Si es administrador, cargar la lista de usuarios
  if (usuarioActual.value.id === 1) {
    await cargarUsuarios()
  }
  await cargarPagos()
  filtrarPagos()
})

async function cargarUsuarios() {
  try {
    const response = await UsuarioService.getData({ rowsPerPage: -1 })
    usuariosOptions.value = response.data.map(u => ({ id: u.id, name: u.name }))
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Error al cargar usuarios' })
  }
}

async function cargarPagos() {
  loading.value = true
  try {
    let params = {
      page: 1,
      rowsPerPage: 1000,
    }

    // Si el usuario es administrador y ha seleccionado un usuario, usamos ese filtro
    if (usuarioActual.value.id === 1 && usuarioFiltro.value) {
      params.user_id = usuarioFiltro.value
    } else if (usuarioActual.value.id !== 1) {
      // Para usuarios no administradores, usar el filtro por actualizados por mi
      params.solo_actualizados_por_mi = true
    }
    // Si es administrador y no hay usuario seleccionado, no se aplica filtro por usuario

    const response = await PagoService.getData(params)
    pagos.value = response.data
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Error al cargar pagos' })
    pagos.value = []
  } finally {
    loading.value = false
  }
}

function filtrarPagos() {
  let filtered = pagos.value.filter(p =>
    (!fechaInicio.value || p.created_at.slice(0,10) >= fechaInicio.value) &&
    (!fechaFin.value || p.created_at.slice(0,10) <= fechaFin.value)
  )

  // Aplicar filtro por estado si está seleccionado
  if (estadoFiltro.value !== null && estadoFiltro.value !== undefined) {
    filtered = filtered.filter(p => p.estado === estadoFiltro.value.value)
  } else {
    // Por defecto, mostrar solo pagados (0) y anulados (null), excluir pendientes (1)
    filtered = filtered.filter(p => p.estado !== 1)
  }

  pagosFiltrados.value = filtered
}

const totalPagado = computed(() =>
  pagosFiltrados.value
    .filter(p => p.estado !== null) // Excluir anulados del total
    .reduce((sum, p) => sum + Number(p.total), 0)
)

function exportarExcel() {
  const data = pagosFiltrados.value.map(p => ({
    ID: p.id,
    'ID Boleta': p.boleta_id,
    Solicitud: p.solicitud.solicitud_code,
    'Concepto / Denominación': p.tupas && p.tupas.length
      ? p.tupas.map(t => `${t.denominacion} (${t.pivot?.cantidad ?? 1})`).join('; ')
      : '-',
    Nombre: p.nombre_completo,
    'Tipo Doc': p.tipo_documento,
    'N° Doc': p.num_documento,
    Total: p.estado === null ? 'Anulado' : `S/. ${p.total}`,
    'Actualizado Por': p.actualizador?.name || 'Sistema',
    'Fecha Actualización': p.updated_at ? formatFecha(p.updated_at) : ''
  }))

  const rango = [
    [`Reporte de Pagos - Usuario: ${nombreUsuarioReporte.value}`],
    [`Rango: ${fechaInicio.value || '...'} a ${fechaFin.value || '...'}`],
    [`Estado: ${estadoFiltro.value ? estadoFiltro.value.label : 'Pagados y Anulados'}`],
    [], // fila vacía para separar
  ]

  const ws = XLSX.utils.aoa_to_sheet(rango)
  XLSX.utils.sheet_add_json(ws, data, {
    origin: 'A5',
    skipHeader: false
  })

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Pagos')
  XLSX.writeFile(wb, `reporte_pagos_${new Date().toISOString().split('T')[0]}.xlsx`)
}

function exportarPDF() {
  const doc = new jsPDF()
  const fechaHora = new Date().toLocaleString()
  const watermark = nombreUsuarioReporte.value

  // === Encabezado de informe ===
  doc.setFontSize(12)
  doc.setFont(undefined, 'bold')
  doc.text('HOJA DE INGRESOS: RECURSOS DIRECTAMENTE RECAUDADOS', 14, 15)

  doc.setFontSize(10)
  doc.setFont(undefined, 'normal')
  doc.text('PROGRAMA', 14, 22)
  doc.text(': 003 ADMINISTRACION', 50, 22)

  doc.text('SUB PROGRAMA', 14, 28)
  doc.text(': 006 ADMINISTRACION GENERAL', 50, 28)

  doc.text('ACTIVIDAD', 14, 34)
  doc.text(': 100498 CONSERVACION Y VIGILANCIA DE DOCUMENTOS', 50, 34)

  doc.text('COMPONENTE', 14, 40)
  doc.text(': 30166 ARCHIVO REGIONAL PUNO', 50, 40)

  doc.text(`Fecha: ${fechaHora.split(',')[0]}`, 160, 15)
  doc.text(`Usuario: ${watermark}`, 160, 20)

  doc.setFont(undefined, 'italic')
  doc.text(`Rango: ${fechaInicio.value || '...'} a ${fechaFin.value || '...'}`, 14, 48)
  doc.text(`Estado: ${estadoFiltro.value ? estadoFiltro.value.label : 'Pagados y Anulados'}`, 14, 53)

  // === Tabla ===
  autoTable(doc, {
    startY: 58,
    head: [['N°', 'N° Boleta', 'N° Solicitud', 'Concepto / Denominación', 'Nombre Completo', 'Tipo Doc', 'N° Doc', 'Total', 'Actualizado Por']],
    body: pagosFiltrados.value.map(p => [
      p.id,
      p.boleta_id,
      p.solicitud.solicitud_code,
      p.tupas && p.tupas.length
        ? p.tupas.map(t => `${t.denominacion} (${t.pivot?.cantidad ?? 1})`).join('; ')
        : '-',
      p.nombre_completo,
      p.tipo_documento,
      p.num_documento,
      p.estado === null ? 'Anulado' : `S/. ${p.total}`,
      p.actualizador?.name || 'Sistema'
    ]),
    styles: { fontSize: 9 },
    theme: 'striped',
    didDrawPage: function (data) {
      // Marca de agua
      doc.saveGraphicsState && doc.saveGraphicsState()
      if (doc.setGState) {
        doc.setGState(new doc.GState({ opacity: 0.1 }))
      } else {
        doc.setTextColor(150, 150, 150)
      }
      doc.setFontSize(40)
      doc.text(watermark, doc.internal.pageSize.width / 2, doc.internal.pageSize.height / 2, {
        align: 'center',
        angle: 30
      })
      if (doc.restoreGraphicsState) doc.restoreGraphicsState()
      doc.setTextColor(0, 0, 0)
    }
  })

  // === Paginación ===
  const pageCount = doc.internal.getNumberOfPages()
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i)
    doc.setFontSize(9)
    doc.text(`Exportado: ${fechaHora}`, 14, doc.internal.pageSize.height - 10)
    doc.text(`Página ${i} de ${pageCount}`, doc.internal.pageSize.width - 50, doc.internal.pageSize.height - 10)
  }

  // === Resumen ===
  const finalY = doc.lastAutoTable.finalY || 60
  doc.setFontSize(11)
  doc.text(`Total Pagado: S/ ${totalPagado.value}`, 14, finalY + 10)
  doc.text(`Cantidad de Ventas: ${pagosFiltrados.value.length}`, 14, finalY + 18)
  doc.text(`Usuario: ${watermark}`, 14, finalY + 26)

  doc.save(`reporte_pagos_${new Date().toISOString().split('T')[0]}.pdf`)
}
</script>

<style scoped>
.my-sticky-header-table {
  height: calc(100vh - auto);
}
</style>
