<template>
  <!-- Diálogo para formulario de pagos -->
  <q-dialog v-model="formPagos" persistent>
    <PagosForm
      :title="title"
      :edit="edit"
      :id="editId"
      @save="handleSave"
      @close="formPagos = false"
    />

  </q-dialog>
  <q-dialog v-model="showPagoModal" persistent>
    <ModelPago
      :items="pagoItems"
      :total="pagoTotal"
      :showModal="showPagoModal"
      @close="showPagoModal = false"
      @confirm="onConfirmPago"
    />
  </q-dialog>


  <!-- Diálogo para mostrar la boleta PDF del pago seleccionado -->
   <PagoBoletaPDF ref="boletaPdfRef" style="display:none" />


  <q-page class="q-pa-md">
    <!-- Breadcrumbs y separador -->
    <div class="q-mb-md">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" to="/" />
        <q-breadcrumbs-el label="Pagos" icon="inventory" />
      </q-breadcrumbs>
      <q-separator class="q-my-sm" />
    </div>

    <!-- Barra de acciones: agregar y buscar -->
    <div class="row justify-between items-center q-mb-md">
      <q-btn
        color="primary"
        icon="add"
        label="Agregar"
        @click="openForm"
        :disable="loading"
      />
      <q-input
        v-model="filter"
        placeholder="Buscar..."
        dense
        outlined
        clearable
        style="width: 300px"
      >
        <template v-slot:append>
          <q-icon name="search" />
        </template>
      </q-input>
    </div>

    <!-- Tabla de pagos -->
    <q-table
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="Pagos"
      ref="tableRef"
      :rows="rows"
      :columns="columns"
      row-key="id"
      v-model:pagination="pagination"
      :loading="loading"
      :filter="filter"
      binary-state-sort
      @request="onRequest"
    >
      <!-- Columnas personalizadas -->
      <template v-slot:body-cell-solicitud="props">
        <q-td :props="props">
          <div>
            <div>
              <strong>N°{{ props.row.solicitud?.id }}</strong>
              <!-- <span v-if="props.row.solicitud?.bien"> - {{ props.row.solicitud.bien}}</span> -->
            </div>
            <!-- <div class="text-caption text-grey">
              Solicitante: {{ props.row.solicitud?.solicitante?.nombre_completo || '-' }}
            </div> -->
          </div>
        </q-td>
      </template>
      <template v-slot:body-cell-user="props">
        <q-td :props="props">
          <div>
            <div>{{ props.row.user?.name || 'Sin usuario' }}</div>
            <!-- <div class="text-caption text-grey">{{ props.row.user?.email }}</div> -->
          </div>
        </q-td>
      </template>
      <template v-slot:body-cell-tupas="props">
        <q-td :props="props">
          <div v-if="props.row.tupas && props.row.tupas.length">
            <div v-for="tupa in props.row.tupas" :key="tupa.id" class="q-mb-xs">
              <div>
                <strong>{{ tupa.denominacion }}</strong>
                <span class="text-caption text-grey"> (S/ {{ tupa.costo }})</span>
              </div>
              <div class="text-caption">
                Cantidad: {{ tupa.pivot.cantidad }} &nbsp;|&nbsp;
                Subtotal: <strong>S/ {{ tupa.pivot.Subtotal }}</strong>
              </div>
            </div>
          </div>
          <span v-else>-</span>
        </q-td>
      </template>
      <template v-slot:body-cell-estado="props">
        <q-td :props="props">
          <!-- <q-badge :color="props.row.estado === 1 ? 'orange' : 'green'" outline>
            {{ props.row.estado === 1 ? 'Pendiente' : 'Pagado' }}
          </q-badge> -->
          <template v-if="props.row.estado === null">
            <q-badge color="red" outline>Anulado</q-badge>
          </template>
          <template v-else>
            <q-badge :color="props.row.estado === 1 ? 'orange' : 'green'" outline>
              {{ props.row.estado === 1 ? 'Pendiente' : 'Pagado' }}
            </q-badge>
          </template>
        </q-td>
      </template>
      <!-- Columna de acciones -->
      <!-- <template v-slot:body-cell-actions="props">
        <q-td auto-width class="q-gutter-x-sm">
          <q-btn
            size="sm"
            outline
            round
            color="blue"
            icon="edit"
            @click="editarPago(props.row)"
            :disable="loading"
          >
            <q-tooltip>Editar</q-tooltip>
          </q-btn>
          <q-btn
            size="sm"
            outline
            round
            color="red"
            icon="delete"
            @click="confirmarEliminar(props.row.id)"
            :disable="loading"
          >
            <q-tooltip>Eliminar</q-tooltip>
          </q-btn>
          <q-btn
            size="sm"
            outline
            round
            color="green"
            icon="payments"
            @click="pagarPago(props.row)"
            :disable="loading"
          >
            <q-tooltip>Pagar</q-tooltip>
          </q-btn>
          <q-btn
            size="sm"
            outline
            round
            color="orange"
            icon="picture_as_pdf"
            @click="generarBoletaPDF(props.row)"
            :disable="loading"
          >
            <q-tooltip>Boleta</q-tooltip>
          </q-btn>
        </q-td>
      </template> -->
      <template v-slot:body-cell-actions="props">
        <q-td auto-width class="q-gutter-x-sm">
          <!-- <q-btn
            v-if="props.row.estado === 1"
            size="sm"
            outline
            round
            color="blue"
            icon="edit"
            @click="editarPago(props.row)"
            :disable="loading"
          >
            <q-tooltip>Editar</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.estado === 1"
            size="sm"
            outline
            round
            color="red"
            icon="delete"
            @click="confirmarEliminar(props.row.id)"
            :disable="loading"
          >
            <q-tooltip>Eliminar</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.estado === 1"
            size="sm"
            outline
            round
            color="green"
            icon="payments"
            @click="pagarPago(props.row)"
            :disable="loading"
          >
            <q-tooltip>Pagar</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.estado !== 1"
            size="sm"
            outline
            round
            color="orange"
            icon="picture_as_pdf"
            @click="generarBoletaPDF(props.row)"
            :disable="loading"
          >
            <q-tooltip>Boleta</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.estado !== null"
            size="sm"
            outline
            round
            color="grey"
            icon="cancel"
            @click="confirmarAnular(props.row)"
            :disable="loading"
          >
            <q-tooltip>Anular</q-tooltip>
          </q-btn> -->
          <q-btn
            v-if="props.row.estado === 1"
            size="sm"
            outline
            round
            color="blue"
            icon="edit"
            @click="editarPago(props.row)"
            :disable="loading"
          >
            <q-tooltip>Editar</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.estado === 1"
            size="sm"
            outline
            round
            color="red"
            icon="delete"
            @click="confirmarEliminar(props.row.id)"
            :disable="loading"
          >
            <q-tooltip>Eliminar</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.estado === 1"
            size="sm"
            outline
            round
            color="green"
            icon="payments"
            @click="pagarPago(props.row)"
            :disable="loading"
          >
            <q-tooltip>Pagar</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.estado !== 1 && props.row.estado !== null"
            size="sm"
            outline
            round
            color="orange"
            icon="picture_as_pdf"
            @click="generarBoletaPDF(props.row)"
            :disable="loading"
          >
            <q-tooltip>Boleta</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.estado !== null && props.row.estado !== 1 && props.row.estado !== O"
            size="sm"
            outline
            round
            color="grey"
            icon="cancel"
            @click="confirmarAnular(props.row)"
            :disable="loading"
          >
            <q-tooltip>Anular</q-tooltip>
          </q-btn>

        </q-td>
      </template>

      <!-- Mensaje cuando no hay datos -->
      <template v-slot:no-data>
        <div class="full-width row flex-center text-grey q-gutter-sm">
          <q-icon name="warning" size="2em" />
          <span v-if="loading">Cargando datos...</span>
          <span v-else>No se encontraron registros</span>
        </div>
      </template>
    </q-table>
  </q-page>
</template>

<script setup>
// Importaciones principales
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'
import PagoService from 'src/services/PagoService'
import PagosForm from './PagosForm.vue'

import ModelPago from 'src/components/ModelPago.vue'

import PagoBoletaPDF from 'src/components/PagoBoletaPDF.vue' // Solo importa el componente


const $q = useQuasar()

// Columnas de la tabla
const columns = [
  { name: 'id', label: 'ID', align: 'left', field: row => row.id, sortable: true, style: 'width: 50px' },
  { name: 'boleta_id', label: 'ID Boleta', align: 'left', field: row => row.boleta_id, sortable: true,  style: 'font-weight: bold'},
  // { name: 'nombre_completo', label: 'Nombre', align: 'left', field: 'nombre_completo', sortable: true class:'wrap-nombre' },
  { name: 'nombre_completo', label: 'Nombre', align: 'left', field: row => row.nombre_completo,
    sortable: true, style: 'max-width: 150px; white-space: normal; word-break: break-word;', // <-- Cambia aquí
    classes: 'wrap-nombre'  },
  { name: 'tipo_documento', label: 'Tipo Doc', align: 'left', field: 'tipo_documento', sortable: true, style: 'max-width: 10px; white-space: normal; word-break: break-word;' },
  { name: 'num_documento', label: 'N° Doc', align: 'left', field: 'num_documento', sortable: true },
  { name: 'total', label: 'Total', align: 'right', field: 'total', sortable: true },
  { name: 'solicitud', label: 'Solicitud', align: 'left' },
  { name: 'user', label: 'Usuario', align: 'left' },
  { name: 'tupas', label: 'Concepto / Denominación', align: 'left' },
  { name: 'estado', label: 'Estado', align: 'left', field: row => row.estado, sortable: true }, // <-- NUEVA COLUMNA
  { name: 'actions', label: 'Acciones', align: 'center', style: 'min-width: 180px' }
]

// Variables reactivas
const rows = ref([])
const loading = ref(false)
const filter = ref('')
const formPagos = ref(false)
const title = ref('')
const edit = ref(false)
const editId = ref(null)
const boletaPdfRef = ref(null)
const pagination = ref({
  sortBy: 'id',
  descending: false,
  page: 1,
  rowsPerPage: 7,
  rowsNumber: 10,
})



// Para mostrar la boleta PDF
const showBoleta = ref(false)
const pagoSeleccionado = ref(null)
const showPagoModal = ref(false)
const pagoItems = ref([])
const pagoTotal = ref(0)

// Carga inicial de datos
onMounted(() => {
  loadInitialData()
})

const loadInitialData = async () => {
  try {
    loading.value = true
    await onRequest({
      pagination: pagination.value,
      filter: filter.value
    })
  } finally {
    loading.value = false
  }
}

// Petición de datos de la tabla
// const onRequest = async (props) => {
//   loading.value = true
//   try {
//     pagination.value = props.pagination
//     const pagos = await PagoService.getData()
//     rows.value = pagos
//     pagination.value.rowsNumber = pagos.length
//   } catch (error) {
//     $q.notify({
//       type: 'negative',
//       message: 'Error al cargar datos',
//       caption: error.message,
//       position: 'top'
//     })
//     rows.value = []
//     pagination.value.rowsNumber = 0
//   } finally {
//     loading.value = false
//   }
// }

const onRequest = async (props) => {
  loading.value = true
  try {
    // Extrae los parámetros de paginación y filtro
    const { page, rowsPerPage, sortBy, descending } = props.pagination
    const filterValue = props.filter

    // Llama al backend con los parámetros correctos
    const response = await PagoService.getData({
      page,
      rowsPerPage,
      sortBy,
      descending,
      filter: filterValue
    })

    // Si tu backend retorna un objeto paginado tipo Laravel:
    // {
    //   current_page: 1,
    //   data: [...],
    //   per_page: 7,
    //   total: 100,
    //   ...
    // }
    // rows.value = response.data
    // pagination.value = {
    //   ...pagination.value,
    //   page: response.current_page,
    //   rowsPerPage: response.per_page,
    //   rowsNumber: response.total,
    //   sortBy,
    //   descending
    // }
    rows.value = response.data
    pagination.value = {
      ...pagination.value,
      page: response.current_page,
      rowsPerPage: response.per_page,
      rowsNumber: response.total,
      sortBy,
      descending
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Error al cargar datos',
      caption: error.message,
      position: 'top'
    })
    rows.value = []
    pagination.value.rowsNumber = 0
  } finally {
    loading.value = false
  }
}
// ...anular..
const confirmarAnular = (pago) => {
  $q.dialog({
    title: 'Confirmar Anulación',
    message: '¿Está seguro de anular este recibo? Esta acción no se puede deshacer.',
    cancel: true,
    persistent: true
  }).onOk(() => {
    anularPago(pago.id)
  })
}

const anularPago = async (id) => {
  try {
    loading.value = true
    await PagoService.anular(id)
    $q.notify({
      type: 'positive',
      message: 'Pago anulado correctamente',
      position: 'top-right'
    })
    await onRequest({
      pagination: pagination.value,
      filter: filter.value
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Error al anular pago',
      caption: error.message,
      position: 'top-right'
    })
  } finally {
    loading.value = false
  }
}
// ...existing code...
// Abrir formulario de nuevo pago
const openForm = () => {
  formPagos.value = true
  edit.value = false
  editId.value = null
  title.value = 'Nuevo Pago'
}

// Editar pago existente
const editarPago = async (pago) => {
  formPagos.value = true
  edit.value = true
  editId.value = pago.id
  title.value = 'Editar Pago'
  await nextTick()
}

// Confirmar eliminación
const confirmarEliminar = (id) => {
  $q.dialog({
    title: 'Confirmar Eliminación',
    message: '¿Está seguro de eliminar este pago?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    eliminarPago(id)
  })
}

// Eliminar pago
const eliminarPago = async (id) => {
  try {
    loading.value = true
    await PagoService.delete(id)
    $q.notify({
      type: 'positive',
      message: 'Pago eliminado correctamente',
      position: 'top-right'
    })
    await onRequest({
      pagination: pagination.value,
      filter: filter.value
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Error al eliminar pago',
      caption: error.message,
      position: 'top-right'
    })
  } finally {
    loading.value = false
  }
}

// Guardar pago (nuevo o editado)
const handleSave = async () => {
  formPagos.value = false
  $q.notify({
    type: 'positive',
    message: edit.value ? 'Pago actualizado correctamente' : 'Pago creado correctamente',
    position: 'top-right',
    timeout: 1000
  })
  await onRequest({
    pagination: pagination.value,
    filter: filter.value
  })
}

function generarBoletaPDF(pago) {
  formPagos.value = false
  if (!pago.tupas && !pago.items) {
    $q.notify({ type: 'warning', message: 'Este pago no tiene detalles para mostrar.' })
    return
  }
  // Llama al método expuesto usando la ref
  boletaPdfRef.value.generarPDFPago(pago)
}

function pagarPago(pago) {
  pagoItems.value = (pago.tupas || []).map(tupa => ({
    id: tupa.id,
    description: tupa.denominacion,
    cantidad: tupa.pivot?.cantidad || 1,
    subtotal: tupa.pivot?.Subtotal || 0
  }))
  pagoTotal.value = Number(pago.total) // <-- Asegura que sea Number
  pagoSeleccionado.value = pago
  showPagoModal.value = true
}


// async function onConfirmPago({ amount, change }) {
//   if (!pagoSeleccionado.value) return;

//   try {
//     const response = await PagoService.toggleEstado(pagoSeleccionado.value.id);

//     $q.notify({
//       type: 'positive',
//       message: `Pago registrado. Boleta #${response.boleta_id}`,
//       caption: `Monto: S/ ${amount} | Vuelto: S/ ${change}`,
//       position: 'top',
//       timeout: 3000
//     });

//     await onRequest({
//       pagination: pagination.value,
//       filter: filter.value
//     });

//   } catch (error) {
//     $q.notify({
//       type: 'negative',
//       message: 'Error al procesar el pago',
//       caption: error.message,
//       position: 'top',
//       timeout: 5000
//     });
//   } finally {
//     showPagoModal.value = false;
//   }
// }


async function onConfirmPago({ amount, change }) {
  if (!pagoSeleccionado.value) return;

  try {
    const response = await PagoService.toggleEstado(pagoSeleccionado.value.id);

    $q.notify({
      type: 'positive',
      message: `Pago registrado. Boleta #${response.boleta_id}`,
      caption: `Monto: S/ ${amount} | Vuelto: S/ ${change}`,
      position: 'top',
      timeout: 3000
    });

    await onRequest({
      pagination: pagination.value,
      filter: filter.value
    });

  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Error al procesar el pago',
      caption: error.message,
      position: 'top',
      timeout: 5000
    });
  } finally {
    showPagoModal.value = false;
  }
}


</script>

<style scoped>
.q-table {
  height: calc(100vh - 220px);
}
.my-card {
  width: 100%;
  max-width: 80vw;
}
/* .wrap-nombre {
  white-space: normal;
  word-break: break-word;
  max-width: 200px;
} */

</style>
