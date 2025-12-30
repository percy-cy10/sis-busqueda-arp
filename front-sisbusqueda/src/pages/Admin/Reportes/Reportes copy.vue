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
    </div>

    <!-- Tabla de pagos -->
    <q-table
      :rows="pagosFiltrados"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :rows-per-page-options="[10, 20, 50]"
      title="Pagos realizados"
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

const $q = useQuasar()
const loading = ref(false)
const fechaInicio = ref('')
const fechaFin = ref('')
const pagos = ref([])
const pagosFiltrados = ref([])

const columns = [
  { name: 'id', label: 'ID', align: 'left', field: row => row.id, sortable: true },
  { name: 'boleta_id', label: 'ID Boleta', align: 'left', field: row => row.boleta_id, sortable: true },
  { name: 'solicitud', label: 'Solicitud', align: 'left', field: row => row.solicitud_id, sortable: true },
  {
    name: 'tupas',
    label: 'Concepto / Denominacion',
    align: 'left',
    field: row => row.tupas // importante mantener si quieres exportar
  },
  { name: 'nombre_completo', label: 'Nombre', align: 'left', field: row => row.nombre_completo, sortable: true },
  { name: 'tipo_documento', label: 'Tipo Doc', align: 'left', field: row => row.tipo_documento, sortable: true },
  { name: 'num_documento', label: 'N° Doc', align: 'left', field: row => row.num_documento, sortable: true },

  // { name: 'total', label: 'Total', align: 'center', field: row => `S/. ${row.total}`, sortable: true, style: 'font-weight: bold' }
    {
    name: 'total',
    label: 'Total',
    align: 'center',
    field: row => row.estado === null ? 'Anulado' : `S/. ${row.total}`,
    sortable: true,
    style: 'font-weight: bold'
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

onMounted(async () => {
  await cargarPagos()
  filtrarPagos()
})

async function cargarPagos() {
  loading.value = true
  try {
    const response = await PagoService.getData({ page: 1, rowsPerPage: 1000, solo_mios: true })
    pagos.value = response.data
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Error al cargar pagos' })
    pagos.value = []
  } finally {
    loading.value = false
  }
}

function filtrarPagos() {
  pagosFiltrados.value = pagos.value.filter(p =>
    p.estado !== 1 &&
    (!fechaInicio.value || p.created_at.slice(0,10) >= fechaInicio.value) &&
    (!fechaFin.value || p.created_at.slice(0,10) <= fechaFin.value)
  )
}
// function filtrarPagos() {
//   pagosFiltrados.value = pagos.value.filter(p =>
//     // p.estado === 0// solo pagos con estado 0
//     (!fechaInicio.value || p.created_at.slice(0,10) >= fechaInicio.value) &&
//     (!fechaFin.value || p.created_at.slice(0,10) <= fechaFin.value)
//   )
// }


// const totalPagado = computed(() =>
//   pagosFiltrados.value.reduce((sum, p) => sum + Number(p.total), 0)
// )
const totalPagado = computed(() =>
  pagosFiltrados.value
    .filter(p => p.estado !== null) // Excluir anulados del total
    .reduce((sum, p) => sum + Number(p.total), 0)
)
// function getUserFromStorage() {
//   try {
//     const raw = localStorage.getItem('user')
//     return raw ? JSON.parse(raw) : {}
//   } catch (e) {
//     return {}
//   }
// }
// const usuarioActual = ref(getUserFromStorage())

// Nuevo: Mapa de usuarios por ID (puedes cargarlo desde tu backend si tienes muchos usuarios)
const usuariosPorId = ref({})


onMounted(async () => {
  await cargarUsuarios()
  await cargarPagos()
  filtrarPagos()
})

// function exportarExcel() {
//   const ws = XLSX.utils.json_to_sheet(pagosFiltrados.value)
//   const wb = XLSX.utils.book_new()
//   XLSX.utils.book_append_sheet(wb, ws, 'Pagos')
//   XLSX.writeFile(wb, 'reporte_pagos.xlsx')
// }

// function exportarPDF() {
//   const doc = new jsPDF()
//   doc.setFontSize(16)
//   doc.text('Reporte de Pagos', 14, 20)

//   const fechaHora = new Date().toLocaleString()
//   const watermark = usuarioActual.value.nombre || 'Caja'

//   autoTable(doc, {
//     startY: 30,
//     head: [['ID', 'Boleta','Solicitud','Tupas',  'Nombre', 'Tipo Doc', 'N° Doc', 'Total']],
//     body: pagosFiltrados.value.map(p => [
//       p.id,
//       p.boleta_id,
//       p.nombre_completo,
//       p.tipo_documento,
//       p.num_documento,
//       `S/. ${p.total}`
//     ]),
//     styles: { fontSize: 10 },
//     theme: 'striped',
//     didDrawPage: function (data) {
//       const pageCount = doc.internal.getNumberOfPages()
//       doc.setFontSize(10)
//       doc.text(`Exportado: ${fechaHora}`, data.settings.margin.left, doc.internal.pageSize.height - 10)
//       doc.text(`Página ${doc.internal.getCurrentPageInfo().pageNumber} de ${pageCount}`, doc.internal.pageSize.width - 50, doc.internal.pageSize.height - 10)

//       // Marca de agua
//       doc.saveGraphicsState && doc.saveGraphicsState()
//       if (doc.setGState) {
//         doc.setGState(new doc.GState({ opacity: 0.1 }))
//       } else {
//         doc.setTextColor(150, 150, 150)
//       }
//       doc.setFontSize(40)
//       doc.text(watermark, doc.internal.pageSize.width / 2, doc.internal.pageSize.height / 2, { align: 'center', angle: 30 })
//       if (doc.restoreGraphicsState) doc.restoreGraphicsState()
//       doc.setFontSize(10)
//       doc.setTextColor(0, 0, 0)
//     }
//   })

//   // Resumen al final
//   let finalY = doc.lastAutoTable.finalY || 40
//   doc.setFontSize(12)
//   doc.text(`Total Pagado: S/ ${totalPagado.value}`, 14, finalY + 10)
//   doc.text(`Cantidad de Ventas: ${pagosFiltrados.value.length}`, 14, finalY + 18)
//   doc.text(`Usuario: ${usuarioActual.value.nombre || 'Caja'}`, 14, finalY + 26)

//   doc.save('reporte_pagos.pdf')
// }

function exportarExcel() {
  // Construir los datos para la tabla
  const data = pagosFiltrados.value.map(p => ({
    ID: p.id,
    'ID Boleta': p.boleta_id,
    Solicitud: p.solicitud_id,
    'Concepto / Denominación': p.tupas && p.tupas.length
      ? p.tupas.map(t => `${t.denominacion} (${t.pivot?.cantidad ?? 1})`).join('; ')
      : '-',
    Nombre: p.nombre_completo,
    'Tipo Doc': p.tipo_documento,
    'N° Doc': p.num_documento,
    Total: p.estado === null ? 'Anulado' : `S/. ${p.total}`,
    // Usuario: usuariosPorId.value[p.usuario_id]?.nombre || 'Caja'
  }))

  // Crear hoja con el mensaje de rango de fechas
  const rango = [
    [`Rango exportado: ${fechaInicio.value || '...'} a ${fechaFin.value || '...'} - Usuario: ${usuariosPorId.value[usuarioActual.value.id]?.nombre || 'Caja'}`],
    [], // fila vacía para separar visualmente
  ]

  // Crear hoja de trabajo y agregar el rango primero
  const ws = XLSX.utils.aoa_to_sheet(rango)

  // Insertar los datos como tabla, comenzando en la fila siguiente (A3)
  XLSX.utils.sheet_add_json(ws, data, {
    origin: 'A3',
    skipHeader: false // Incluye los encabezados de columna
  })

  // Crear el libro y agregar la hoja
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Pagos')

  // Exportar como archivo Excel
  XLSX.writeFile(wb, 'reporte_pagos.xlsx')
}


// function exportarExcel() {
//   // Agregar columnas personalizadas
//   const data = pagosFiltrados.value.map(p => ({
//     ID: p.id,
//     'ID Boleta': p.boleta_id,
//     Solicitud: p.solicitud_id,
//     'Concepto / Denominacion': p.tupas && p.tupas.length
//       ? p.tupas.map(t => `${t.denominacion} (${t.pivot?.cantidad ?? 1})`).join('; ')
//       : '-',
//     Nombre: p.nombre_completo,
//     'Tipo Doc': p.tipo_documento,
//     'N° Doc': p.num_documento,
//     Total: `S/. ${p.total}`,
//     // Usuario: usuariosPorId.value[p.usuario_id]?.nombre || 'Caja'
//   }))

//   // Agregar rango de fechas como primera fila
//   const rango = [
//     [`Rango exportado: ${fechaInicio.value || '...'} a ${fechaFin.value || '...'} - Usuario: ${usuariosPorId.value[usuarioActual.value.id]?.nombre || 'Caja'}`]
//   ]
//   const ws = XLSX.utils.aoa_to_sheet(rango)
//   XLSX.utils.sheet_add_json(ws, data, { origin: -1 })
//   const wb = XLSX.utils.book_new()
//   XLSX.utils.book_append_sheet(wb, ws, 'Pagos')
//   XLSX.writeFile(wb, 'reporte_pagos.xlsx')
// }


function exportarPDF() {
  const doc = new jsPDF()
  const fechaHora = new Date().toLocaleString()
  const watermark = usuariosPorId.value[usuarioActual.value.id]?.nombre || 'Caja'

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

  doc.setFont(undefined, 'italic')
  doc.text(`Rango exportado: ${fechaInicio.value || '...'} a ${fechaFin.value || '...'}`, 14, 48)

  // === Tabla ===
  autoTable(doc, {
    startY: 54,
    head: [['N°', 'N° Boleta', 'N° Solicitud', 'Concepto / Denominación', 'Nombre Completo', 'Tipo Doc', 'N° Doc', 'Total']],
    body: pagosFiltrados.value.map(p => [
      p.id,
      p.boleta_id,
      p.solicitud_id,
      p.tupas && p.tupas.length
        ? p.tupas.map(t => `${t.denominacion} (${t.pivot?.cantidad ?? 1})`).join('; ')
        : '-',
      p.nombre_completo,
      p.tipo_documento,
      p.num_documento,
      p.estado === null ? 'Anulado' : `S/. ${p.total}`
    ]),
    styles: { fontSize: 9 },
    theme: 'striped',
    didDrawPage: function (data) {
      // Marca de agua (en cada página)
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

  // === Agregar paginación de forma correlativa al final ===
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

  doc.save('reporte_pagos.pdf')
}
// function exportarPDF() {
//   const doc = new jsPDF()
//   const fechaHora = new Date().toLocaleString()
//   const watermark = usuariosPorId.value[usuarioActual.value.id]?.nombre || 'Caja'

//   // === Encabezado estilo imagen ===
//   doc.setFontSize(12)
//   doc.setFont(undefined, 'bold')
//   doc.text('HOJA DE INGRESOS: RECURSOS DIRECTAMENTE RECAUDADOS', 14, 15)

//   doc.setFontSize(10)
//   doc.setFont(undefined, 'normal')
//   doc.text('PROGRAMA', 14, 22)
//   doc.text(': 003 ADMINISTRACION', 50, 22)

//   doc.text('SUB PROGRAMA', 14, 28)
//   doc.text(': 006 ADMINISTRACION GENERAL', 50, 28)

//   doc.text('ACTIVIDAD', 14, 34)
//   doc.text(': 100498 CONSERVACION Y VIGILANCIA DE DOCUMENTOS', 50, 34)

//   doc.text('COMPONENTE', 14, 40)
//   doc.text(': 30166 ARCHIVO REGIONAL PUNO', 50, 40)

//   doc.text(`Fecha: ${fechaHora.split(',')[0]}`, 160, 15) // Fecha en la parte superior derecha

//   // === Rango exportado ===
//   doc.setFont(undefined, 'italic')
//   doc.text(`Rango exportado: ${fechaInicio.value || '...'} a ${fechaFin.value || '...'}`, 14, 48)

//   // === Tabla de pagos ===
//   autoTable(doc, {
//     startY: 54,
//     head: [['N°', 'N° Boleta', 'N° Solicitud', 'Concepto / Denominación', 'Nombre Completo', 'Tipo Doc', 'N° Doc', 'Total']],
//     body: pagosFiltrados.value.map(p => [
//       p.id,
//       p.boleta_id,
//       p.solicitud_id,
//       p.tupas && p.tupas.length
//         ? p.tupas.map(t => `${t.denominacion} (${t.pivot?.cantidad ?? 1})`).join('; ')
//         : '-',
//       p.nombre_completo,
//       p.tipo_documento,
//       p.num_documento,
//       `S/. ${p.total}`
//     ]),
//     styles: { fontSize: 9 },
//     theme: 'striped',
//     didDrawPage: function (data) {
//       const pageCount = doc.internal.getNumberOfPages()
//       doc.setFontSize(9)
//       doc.text(`Exportado: ${fechaHora}`, data.settings.margin.left, doc.internal.pageSize.height - 10)
//       doc.text(`Página ${doc.internal.getCurrentPageInfo().pageNumber} de ${pageCount}`, doc.internal.pageSize.width - 50, doc.internal.pageSize.height - 10)

//       // Marca de agua
//       doc.saveGraphicsState && doc.saveGraphicsState()
//       if (doc.setGState) {
//         doc.setGState(new doc.GState({ opacity: 0.1 }))
//       } else {
//         doc.setTextColor(150, 150, 150)
//       }
//       doc.setFontSize(40)
//       doc.text(watermark, doc.internal.pageSize.width / 2, doc.internal.pageSize.height / 2, {
//         align: 'center',
//         angle: 30
//       })
//       if (doc.restoreGraphicsState) doc.restoreGraphicsState()
//       doc.setFontSize(10)
//       doc.setTextColor(0, 0, 0)
//     }
//   })

//   // === Resumen al final ===
//   let finalY = doc.lastAutoTable.finalY || 60
//   doc.setFontSize(11)
//   doc.text(`Total Pagado: S/ ${totalPagado.value}`, 14, finalY + 10)
//   doc.text(`Cantidad de Ventas: ${pagosFiltrados.value.length}`, 14, finalY + 18)
//   doc.text(`Usuario: ${watermark}`, 14, finalY + 26)

//   doc.save('reporte_pagos.pdf')
// }





</script>

<style scoped>
.my-sticky-header-table {
  height: calc(100vh - auto);
}
</style>
