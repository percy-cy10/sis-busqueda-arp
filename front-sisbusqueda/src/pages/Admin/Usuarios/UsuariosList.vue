<template>
  <q-page>
    <!-- Diálogo para añadir/editar usuario -->
    <q-dialog v-model="formUser">
      <UsuariosForm
        :title="title"
        :edit="edit"
        :id="editId"
        ref="usuariosformRef"
        @save="save"
      />
    </q-dialog>

    <!-- Navegación -->
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />
        <q-breadcrumbs-el label="Usuarios" icon="mdi-account" />
      </q-breadcrumbs>
    </div>
    <q-separator />

    <!-- Botón agregar usuario -->
    <div class="q-gutter-xs q-pa-sm">
      <q-btn
        color="primary"
        :disable="loading"
        :label="$q.screen.lt.sm ? '' : 'Agregar'"
        icon-right="add"
        @click="openForm"
      />
    </div>

    <!-- Tabla de usuarios -->
    <q-table
      class="my-sticky-header-table htable q-ma-sm"
      title="Usuarios"
      ref="tableRef"
      :rows="rows"
      :columns="columns"
      row-key="id"
      v-model:pagination="pagination"
      :loading="loading"
      :filter="filter"
      :rows-per-page-options="[7, 10, 15]"
      binary-state-sort
      @request="onRequest"
    >
      <!-- Buscador -->
      <template v-slot:top-right>
        <q-input
          active-class="text-white"
          standout="bg-primary"
          color="white"
          dense
          debounce="500"
          v-model="filter"
          placeholder="Buscar"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </template>

      <!-- Encabezado de tabla -->
      <template v-slot:header="props">
        <q-tr :props="props">
          <q-th v-for="col in props.cols" :key="col.name" :props="props">
            {{ col.label }}
          </q-th>
          <q-th auto-width>Acciones</q-th>
        </q-tr>
      </template>

      <!-- Cuerpo de tabla -->
      <template v-slot:body="props">
        <q-tr :props="props">
          <q-td v-for="col in props.cols" :key="col.name" :props="props">
            <template v-if="col.name === 'estado'">
              <q-badge
                class="estado-badge"
                :class="props.row.estado ? 'activo' : 'inactivo'"
              >
                {{ props.row.estado ? 'ACTIVO' : 'INACTIVO' }}
              </q-badge>
            </template>
            <template v-else>
              {{ col.value }}
            </template>
          </q-td>

          <!-- Acciones -->
          <q-td auto-width>
            <q-btn
              size="sm"
              outline
              color="blue"
              round
              icon="edit"
              @click="editar(props.row.id)"
              class="q-mr-xs"
            />
            <q-btn
              size="sm"
              outline
              color="red"
              round
              icon="delete"
              @click="eliminar(props.row.id)"
            />
            <q-btn
              size="sm"
              outline
              round
              :color="props.row.estado ? 'green' : 'gray'"
              :icon="props.row.estado ? 'toggle_on' : 'toggle_off'"
              @click="toggleEstado(props.row)"
              :disable="loading"
            >
              <q-tooltip>
                {{ props.row.estado ? 'Desactivar' : 'Activar' }}
              </q-tooltip>
            </q-btn>
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import UsuarioService from 'src/services/UsuarioService'
import UsuariosForm from 'src/pages/Admin/Usuarios/UsuariosForm.vue'

// Referencias y estados
const $q = useQuasar()
const tableRef = ref()
const formUser = ref(false)
const usuariosformRef = ref()
const title = ref('')
const edit = ref(false)
const editId = ref()
const rows = ref([])
const filter = ref('')
const loading = ref(false)

// Columnas de la tabla
const columns = [
  { name: 'id', label: 'ID', align: 'center', field: row => row.id, sortable: true },
  { name: 'name', label: 'Usuario', align: 'center', field: row => row.name, sortable: true },
  { name: 'email', label: 'Email', align: 'center', field: row => row.email, sortable: true },
  { name: 'dni', label: 'DNI', align: 'center', field: row => row.dni, sortable: true },
  { name: 'nivel', label: 'Nivel', align: 'center', field: row => row.nivel, sortable: true },
  { name: 'area', label: 'Área', align: 'center', field: row => row.area?.nombre || 'N/A', sortable: true },
  { name: 'estado', label: 'Estado', align: 'center', field: row => row.estado ? 'ACTIVO' : 'INACTIVO', sortable: true }
]

// Paginación inicial
const pagination = ref({
  sortBy: 'id',
  descending: false,
  page: 1,
  rowsPerPage: 7,
  rowsNumber: 10
})

// Abrir formulario para nuevo usuario
const openForm = () => {
  formUser.value = true
  edit.value = false
  title.value = 'Añadir Usuario'
}

// Guardar usuario
const save = () => {
  formUser.value = false
  tableRef.value.requestServerInteraction()
  showNotification('positive', 'Guardado con éxito')
}

// Editar usuario existente
const editar = async id => {
  title.value = 'Editar Usuario'
  formUser.value = true
  edit.value = true
  editId.value = id

  try {
    const { user, rolesSelected } = await UsuarioService.get(id)
    usuariosformRef.value.setValue({
      id: user.id,
      name: user.name,
      email: user.email,
      estado: Boolean(user.estado),
      dni: user.dni ?? '',
      nivel: user.nivel ?? '',
      area_id: user.area_id ?? null,
      rolesSelected,
      password: '' // No mostrar password
    })
  } catch (error) {
    showNotification('negative', 'Error al cargar datos del usuario')
  }
}

// Eliminar usuario
const eliminar = async id => {
  $q.dialog({
    title: 'Confirmación',
    message: '¿Estás seguro de eliminar este registro?',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await UsuarioService.delete(id)
      tableRef.value.requestServerInteraction()
      showNotification('positive', 'Eliminado con éxito')
    } catch (error) {
      showNotification('negative', 'Error al eliminar usuario')
    }
  })
}

// Cambiar estado (activo/inactivo)
const toggleEstado = async usuario => {
  const estadoOriginal = usuario.estado
  try {
    loading.value = true
    usuario.estado = !estadoOriginal
    rows.value = [...rows.value]

    await UsuarioService.toggleEstado(usuario.id)
    showNotification('positive', `Estado cambiado a ${usuario.estado ? 'ACTIVO' : 'INACTIVO'}`)
  } catch (error) {
    usuario.estado = estadoOriginal
    rows.value = [...rows.value]
    showNotification('negative', 'Error al cambiar estado')
  } finally {
    loading.value = false
  }
}

// Solicita datos al servidor
// const onRequest = async props => {
//   const { page, rowsPerPage, sortBy, descending } = props.pagination
//   loading.value = true

//   try {
//     const { data, total } = await UsuarioService.getData({
//       params: {
//         rowsPerPage,
//         page,
//         search: props.filter,
//         order_by: descending ? `-${sortBy}` : sortBy
//       }
//     })

//     rows.value = data.map(usuario => ({
//       ...usuario,
//       estado: [1, '1', true, 'true'].includes(usuario.estado)
//     }))

//     updatePagination({ page, rowsPerPage, sortBy, descending, total })
//   } catch (error) {
//     showNotification('negative', 'Error al cargar datos')
//   } finally {
//     loading.value = false
//   }
// }

const onRequest = async props => {
  const { page, rowsPerPage, sortBy, descending } = props.pagination
  loading.value = true

  try {
    const { data, total } = await UsuarioService.getData({
      rowsPerPage,
      page,
      search: filter.value, // Usar el filtro reactivo
      order_by: descending ? `-${sortBy}` : sortBy
    })

    rows.value = data.map(usuario => ({
      ...usuario,
      estado: [1, '1', true, 'true'].includes(usuario.estado)
    }))

    updatePagination({ page, rowsPerPage, sortBy, descending, total })
  } catch (error) {
    showNotification('negative', 'Error al cargar datos')
  } finally {
    loading.value = false
  }
}

// Actualiza la paginación
const updatePagination = ({ page, rowsPerPage, sortBy, descending, total }) => {
  pagination.value = {
    ...pagination.value,
    page,
    rowsPerPage,
    sortBy,
    descending,
    rowsNumber: total || rows.value.length
  }
}

// Notificación
const showNotification = (type, message) => {
  $q.notify({
    type,
    message,
    position: 'top-right',
    progress: true,
    timeout: 1000
  })
}

// Inicializa la tabla al montar
onMounted(() => {
  tableRef.value.requestServerInteraction()
})
</script>

<style scoped>
.q-table {
  height: calc(100vh - 220px);
}

.estado-badge {
  font-size: 0.9rem;
  padding: 6px 14px;
  border-radius: 12px;
  letter-spacing: 0.5px;
  min-width: 90px;
  display: inline-block;
  text-transform: uppercase;
  font-weight: bold;
  transition: all 0.3s ease;
}

.estado-badge.activo {
  background-color: #e8f5e9;
  color: #2e7d32;
  box-shadow: 0 2px 4px rgba(46, 125, 50, 0.2);
}

.estado-badge.inactivo {
  background-color: #ffebee;
  color: #c62828;
  box-shadow: 0 2px 4px rgba(198, 40, 40, 0.2);
}

.q-table__container .q-btn {
  margin: 0 4px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.q-table__container .q-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
</style>
