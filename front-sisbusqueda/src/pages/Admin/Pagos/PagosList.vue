<template>
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
      :cargando="cargandoPago"
      @close="showPagoModal = false"
      @confirm="onConfirmPago"
    />
  </q-dialog>

  <PagoBoletaPDF ref="boletaPdfRef" style="display:none" />

  <q-page class="q-pa-md">
    <div class="q-mb-md">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" to="/" />
        <q-breadcrumbs-el label="Pagos" icon="inventory" />
      </q-breadcrumbs>
      <q-separator class="q-my-sm" />
    </div>

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

    <q-table
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="Todos los Pagos"
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
      <!-- Columna Solicitud -->
      <template v-slot:body-cell-solicitud="props">
        <q-td :props="props">
          <div>
            <div>
              <strong>N°{{ props.row.solicitud?.solicitud_code }}</strong>
            </div>
          </div>
        </q-td>
      </template>

      <!-- Columna Usuario -->
      <template v-slot:body-cell-user="props">
        <q-td :props="props">
          <div>
            <div>{{ props.row.user?.name || 'Sin usuario' }}</div>
          </div>
        </q-td>
      </template>

      <!-- Columna Documento (Tipo y Número combinados) -->
      <template v-slot:body-cell-documento="props">
        <q-td :props="props">
          <div>
            <div>{{ props.row.tipo_documento }}</div>
            <div class="text-caption text-grey">{{ props.row.num_documento }}</div>
          </div>
        </q-td>
      </template>

      <!-- Columna Auditoría (Creado y Actualizado combinados) -->
      <template v-slot:body-cell-auditoria="props">
        <q-td :props="props">
          <div class="q-mb-xs">
            <div class="text-caption text-weight-medium">Creado:
            {{ props.row.creador?.name || 'Sistema' }}</div>
            <div class="text-caption text-grey">{{ formatFecha(props.row.created_at) }}</div>
          </div>
          <div>
            <div class="text-caption text-weight-medium">Actualizado:
            {{ props.row.actualizador?.name || 'Sistema' }}</div>
            <div class="text-caption text-grey">{{ formatFecha(props.row.updated_at) }}</div>
          </div>
        </q-td>
      </template>

      <!-- Columna Tupas -->
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

      <!-- Columna Estado -->
      <template v-slot:body-cell-estado="props">
        <q-td :props="props">
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

      <!-- Columna Acciones -->
      <template v-slot:body-cell-actions="props">
        <q-td auto-width class="q-gutter-x-sm">
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
            v-if="props.row.estado === 0"
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
            v-if="props.row.estado !== null && props.row.estado !== 1"
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
import { ref, onMounted, nextTick } from 'vue'
import { useQuasar } from 'quasar'
import PagoService from 'src/services/PagoService'
import PagosForm from './PagosForm.vue'
import ModelPago from 'src/components/ModelPago.vue'
import PagoBoletaPDF from 'src/components/PagoBoletaPDF.vue'

import { redondearConDecimales } from "src/utils/ConvertMoney";

const $q = useQuasar()

// Columnas actualizadas con documento combinado y auditoría
const columns = [
  { name: 'id', label: 'ID', align: 'left', field: row => row.id, sortable: true, style: 'width: 50px' },
  { name: 'boleta_id', label: 'ID Boleta', align: 'left', field: row => row.boleta_id, sortable: true, style: 'font-weight: bold'},
  { name: 'nombre_completo', label: 'Nombre', align: 'left', field: row => row.nombre_completo, sortable: true, style: 'max-width: 150px; white-space: normal; word-break: break-word;', classes: 'wrap-nombre' },
  { name: 'documento', label: 'Documento', align: 'left' }, // Columna combinada para tipo y número
  { name: 'total', label: 'Total', align: 'right', field: 'total', sortable: true },
  { name: 'solicitud', label: 'Solicitud', align: 'left' },
  { name: 'auditoria', label: 'Auditoría', align: 'left' }, // Columna combinada para creado y actualizado
  { name: 'tupas', label: 'Concepto / Denominación', align: 'left' },
  { name: 'estado', label: 'Estado', align: 'left', field: row => row.estado, sortable: true },
  { name: 'actions', label: 'Acciones', align: 'center', style: 'min-width: 180px' }
]

const cargandoPago = ref(false)
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

const showPagoModal = ref(false)
const pagoItems = ref([])
const pagoTotal = ref(0)
const pagoSeleccionado = ref(null)

// Función para formatear fecha
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

const onRequest = async (props) => {
  loading.value = true
  try {
    const { page, rowsPerPage, sortBy, descending } = props.pagination
    const filterValue = props.filter

    const response = await PagoService.getData({
      page,
      rowsPerPage,
      sortBy,
      descending,
      filter: filterValue
    })

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
  boletaPdfRef.value.generarPDFPago(pago)
}

function pagarPago(pago) {
  pagoItems.value = (pago.tupas || []).map(tupa => ({
    id: tupa.id,
    description: tupa.denominacion,
    cantidad: tupa.pivot?.cantidad || 1,
    subtotal: tupa.pivot?.Subtotal || 0
  }))
  pagoTotal.value = Number(pago.total)
  pagoSeleccionado.value = pago
  showPagoModal.value = true
}

async function onConfirmPago({ amount, change }) {
  if (!pagoSeleccionado.value) return;

  cargandoPago.value = true;

  try {
    const montoRedondeado = redondearConDecimales(amount);
    const vueltoRedondeado = redondearConDecimales(change);

    const response = await PagoService.toggleEstado(pagoSeleccionado.value.id, {
      monto_pagado: montoRedondeado,
      vuelto: vueltoRedondeado
    });

    $q.notify({
      type: 'positive',
      message: `Pago registrado. Boleta #${response.boleta_id}`,
      caption: `Monto: S/ ${montoRedondeado} | Vuelto: S/ ${vueltoRedondeado}`,
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
    cargandoPago.value = false;
    showPagoModal.value = false;
  }
}

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
</script>

<style scoped>
.q-table {
  height: calc(100vh - 220px);
}
.my-card {
  width: 100%;
  max-width: 80vw;
}
.wrap-nombre {
  white-space: normal;
  word-break: break-word;
  max-width: 200px;
}
</style>
