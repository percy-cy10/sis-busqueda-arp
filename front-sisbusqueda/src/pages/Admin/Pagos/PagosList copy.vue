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

  <!-- Diálogo para mostrar la boleta PDF del pago seleccionado -->
  <q-dialog v-model="showBoleta" persistent>
    <q-card class="my-card">
      <q-card-section class="row items-center q-pa-none">
        <div class="col">
          <span class="text-h6 q-ml-md">Boleta de Pago</span>
        </div>
        <q-btn flat round icon="close" @click="showBoleta = false" class="q-ml-auto q-mr-sm" />
      </q-card-section>
      <q-separator />
      <q-card-section>
        <!-- Componente visual de la boleta -->
        <PagoBoletaPDF
          v-if="pagoSeleccionado"
          :pago="pagoSeleccionado"
          ref="boletaPdfRef"
        />
        <!-- Botón para descargar el PDF generado desde el HTML -->
        <q-btn
          color="primary"
          icon="download"
          label="Descargar PDF"
          class="q-mt-md"
          @click="descargarBoletaPDF"
        />
        <q-btn
          color="secondary"
          icon="print"
          label="Imprimir Boleta"
          @click="imprimirBoleta"
        />
      </q-card-section>
    </q-card>
  </q-dialog>

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
              <span v-if="props.row.solicitud?.bien"> - {{ props.row.solicitud.bien}}</span>
            </div>
            <div class="text-caption text-grey">
              Solicitante: {{ props.row.solicitud?.solicitante?.nombre_completo || '-' }}
            </div>
          </div>
        </q-td>
      </template>
      <template v-slot:body-cell-user="props">
        <q-td :props="props">
          <div>
            <div>{{ props.row.user?.name || 'Sin usuario' }}</div>
            <div class="text-caption text-grey">{{ props.row.user?.email }}</div>
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
      <!-- Columna de acciones -->
      <template v-slot:body-cell-actions="props">
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
            @click="mostrarBoleta(props.row)"
            :disable="loading"
          >
            <q-tooltip>Boleta</q-tooltip>
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
import { useQuasar } from 'quasar'
import PagoService from 'src/services/PagoService'
import PagosForm from './PagosForm.vue'
import ModelPago from 'src/components/ModelPago.vue'
import PagoBoletaPDF from 'src/components/PagoBoletaPDF.vue'


const $q = useQuasar()

// Columnas de la tabla
const columns = [
  { name: 'id', label: 'ID', align: 'left', field: row => row.id, sortable: true, style: 'width: 80px' },
  { name: 'nombre_completo', label: 'Nombre', align: 'left', field: 'nombre_completo', sortable: true },
  { name: 'tipo_documento', label: 'Tipo Doc', align: 'left', field: 'tipo_documento', sortable: true },
  { name: 'num_documento', label: 'N° Doc', align: 'left', field: 'num_documento', sortable: true },
  { name: 'total', label: 'Total', align: 'right', field: 'total', sortable: true },
  { name: 'solicitud', label: 'Solicitud', align: 'left' },
  { name: 'user', label: 'Usuario', align: 'left' },
  { name: 'tupas', label: 'Tupas', align: 'left' },
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
const boletaPdfRef = ref(null)

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
const onRequest = async (props) => {
  loading.value = true
  try {
    pagination.value = props.pagination
    const pagos = await PagoService.getData()
    rows.value = pagos
    pagination.value.rowsNumber = pagos.length
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

// Simular pago
function imprimirBoleta() {
  boletaPdfRef.value?.imprimirBoleta()
}

function descargarBoletaPDF() {
  boletaPdfRef.value?.generarPDF()
}

function mostrarBoleta(row) {
  if (!row.tupas && !row.items) {
    $q.notify({ type: 'warning', message: 'Este pago no tiene detalles para mostrar.' })
    return
  }
  pagoSeleccionado.value = row
  showBoleta.value = true
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
</style>
