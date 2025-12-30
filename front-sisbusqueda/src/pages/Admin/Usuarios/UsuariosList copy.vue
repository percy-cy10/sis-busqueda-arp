<template>
  <q-page>
    <!-- Diálogo de formulario -->
    <q-dialog v-model="formUser">
      <UsuariosForm
        :title="title"
        :edit="edit"
        :id="editId"
        ref="usuariosformRef"
        @save="save"
      />
    </q-dialog>

    <!-- Encabezado y controles -->
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />
        <q-breadcrumbs-el label="Usuarios" icon="mdi-account" />
      </q-breadcrumbs>
    </div>
    <q-separator />

    <!-- Botón de agregar -->
    <div class="q-gutter-xs q-pa-sm">
      <q-btn
        color="primary"
        :disable="loading"
        :label="$q.screen.lt.sm ? '' : 'Agregar'"
        icon-right="add"
        @click="openForm"
      />
    </div>

    <!-- Tabla principal -->
    <q-table
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="Usuarios"
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
      <!-- Estado como badge mejorado -->
      <!-- Reemplazar el texto de estado con badge de colores -->
      <template v-if="col.name === 'estado'">
        <q-badge
          class="estado-badge"
          :style="{
            backgroundColor: props.row.estado ? '#e8f5e9' : '#ffebee',
            color: props.row.estado ? '#2e7d32' : '#c62828',
          }"
        >
          {{ props.row.estado ? "ACTIVO" : "INACTIVO" }}
        </q-badge>
      </template>
      <template v-else>
        {{ col.value }}
      </template>

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

      <!-- Encabezados de tabla -->
      <template v-slot:header="props">
        <q-tr :props="props">
          <q-th v-for="col in props.cols" :key="col.name" :props="props">
            {{ col.label }}
          </q-th>
          <q-th auto-width>Acciones</q-th>
        </q-tr>
      </template>

      <!-- Filas y acciones -->
      <template v-slot:body="props">
        <q-tr :props="props">
          <q-td v-for="col in props.cols" :key="col.name" :props="props">
            {{ col.value }}
          </q-td>
          <q-td auto-width>
            <q-btn
              size="sm"
              outline
              color="blue"
              round
              @click="editar(props.row.id)"
              icon="edit"
              class="q-mr-xs"
            />
            <q-btn
              size="sm"
              outline
              color="red"
              round
              @click="eliminar(props.row.id)"
              icon="delete"
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
              <q-tooltip>{{
                props.row.estado ? "Desactivar" : "Activar"
              }}</q-tooltip>
            </q-btn>
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useQuasar } from "quasar";
import UsuarioService from "src/services/UsuarioService";
import UsuariosForm from "src/pages/Admin/Usuarios/UsuariosForm.vue";

const $q = useQuasar();
const tableRef = ref();
const formUser = ref(false);
const usuariosformRef = ref();
const title = ref("");
const edit = ref(false);
const editId = ref();
const rows = ref([]);
const filter = ref("");
const loading = ref(false);

// Configuración de columnas
// const columns = [
//   {
//     name: 'id',
//     label: 'Id',
//     align: 'center',
//     field: row => row.id,
//     sortable: true
//   },
//   {
//     name: 'name',
//     label: 'Usuario',
//     align: 'center',
//     field: row => row.name,
//     sortable: true
//   },
//   {
//     name: 'email',
//     label: 'Email',
//     align: 'center',
//     field: row => row.email,
//     sortable: true
//   },
//   {
//     name: 'area',
//     label: 'Area',
//     align: 'center',
//     field: row => row.area?.nombre || 'N/A',
//     sortable: true
//   },
//   {
//     name: 'estado',
//     label: 'Estado',
//     align: 'center',
//     field: row => row.estado ? 'ACTIVO' : 'INACTIVO',
//     sortable: true
//   },

// ]
const columns = [
  {
    name: "id",
    label: "Id",
    align: "center",
    field: (row) => row.id,
    sortable: true,
  },
  {
    name: "name",
    label: "Usuario",
    align: "center",
    field: (row) => row.name,
    sortable: true,
  },
  {
    name: "email",
    label: "Email",
    align: "center",
    field: (row) => row.email,
    sortable: true,
  },
  {
    name: "dni",
    label: "DNI",
    align: "center",
    field: (row) => row.dni,
    sortable: true,
  },
  {
    name: "nivel",
    label: "Nivel",
    align: "center",
    field: (row) => row.nivel,
    sortable: true,
  },
  {
    name: "area",
    label: "Área",
    align: "center",
    field: (row) => row.area?.nombre || "N/A",
    sortable: true,
  },
  {
    name: "estado",
    label: "Estado",
    align: "center",
    field: (row) => (row.estado ? "ACTIVO" : "INACTIVO"),
    sortable: true,
  },
];

// Configuración de paginación
const pagination = ref({
  sortBy: "id",
  descending: false,
  page: 1,
  rowsPerPage: 7,
  rowsNumber: 10,
});

// Métodos
const openForm = () => {
  formUser.value = true;
  edit.value = false;
  title.value = "Añadir Usuario";
};

const save = () => {
  formUser.value = false;
  tableRef.value.requestServerInteraction();
  showNotification("positive", "Guardado con éxito");
};

const editar = async (id) => {
  title.value = "Editar Usuario";
  formUser.value = true;
  edit.value = true;
  editId.value = id;

  try {
    const { user, rolesSelected } = await UsuarioService.get(id);
    usuariosformRef.value.setFormData({
      id: user.id,
      name: user.name,
      email: user.email,
      estado: Boolean(user.estado),
      rolesSelected,
    });
  } catch (error) {
    showNotification("negative", "Error al cargar datos del usuario");
  }
};

const eliminar = async (id) => {
  $q.dialog({
    title: "Confirmación",
    message: "¿Estás seguro de eliminar este registro?",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await UsuarioService.delete(id);
      tableRef.value.requestServerInteraction();
      showNotification("positive", "Eliminado con éxito");
    } catch (error) {
      showNotification("negative", "Error al eliminar usuario");
    }
  });
};

const toggleEstado = async (usuario) => {
  const estadoOriginal = usuario.estado;
  try {
    loading.value = true;
    usuario.estado = !estadoOriginal;
    rows.value = [...rows.value];

    await UsuarioService.toggleEstado(usuario.id);
    showNotification(
      "positive",
      `Estado cambiado a ${usuario.estado ? "ACTIVO" : "INACTIVO"}`
    );
  } catch (error) {
    usuario.estado = estadoOriginal;
    rows.value = [...rows.value];
    showNotification("negative", "Error al cambiar estado");
  } finally {
    loading.value = false;
  }
};

const onRequest = async (props) => {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  loading.value = true;

  try {
    const { data, total } = await UsuarioService.getData({
      params: {
        rowsPerPage,
        page,
        search: props.filter,
        order_by: descending ? `-${sortBy}` : sortBy,
      },
    });

    // // Conversión robusta del estado
    rows.value = data.map((usuario) => ({
      ...usuario,
      estado: [1, "1", true, "true"].includes(usuario.estado), // Maneja múltiples formatos
    }));

    updatePagination({ page, rowsPerPage, sortBy, descending, total });
  } catch (error) {
    showNotification("negative", "Error al cargar datos");
  } finally {
    loading.value = false;
  }
};

// Helpers
const updatePagination = ({ page, rowsPerPage, sortBy, descending, total }) => {
  pagination.value = {
    ...pagination.value,
    page,
    rowsPerPage,
    sortBy,
    descending,
    rowsNumber: total || rows.value.length,
  };
};

const showNotification = (type, message) => {
  $q.notify({
    type,
    message,
    position: "top-right",
    progress: true,
    timeout: 1000,
  });
};

// Ciclo de vida
onMounted(() => {
  tableRef.value.requestServerInteraction();
});
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
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.q-table {
  height: calc(100vh - 220px);
}
.q-badge--estado {
  font-size: 0.85rem; /* Tamaño de letra un poquito más pequeño */
  padding: 6px 12px; /* Más espacio interno */
  font-weight: bold; /* Texto en negrita */
  border-radius: 8px; /* Bordes redondeados */
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Sombra suave */
  text-transform: uppercase; /* Texto en mayúsculas */
}

.q-badge {
  font-size: 0.9em;
  padding: 4px 8px;
  text-transform: uppercase;
  font-weight: bold;
}

.q-tooltip {
  font-size: 0.9em;
  white-space: pre-line;
}

/* Estilos mejorados para botones */
.q-table__container .q-btn {
  margin: 0 4px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.q-table__container .q-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.q-table__container .q-btn[color="green"] {
  background: linear-gradient(145deg, #4caf50, #81c784);
}

.q-table__container .q-btn[color="red"] {
  background: linear-gradient(145deg, #f44336, #e57373);
}

.q-table__container .q-btn[color="blue"] {
  background: linear-gradient(145deg, #2196f3, #64b5f6);
}

.q-table__container .q-btn[color="orange"] {
  background: linear-gradient(145deg, #ff9800, #ffb74d);
}

.q-btn {
  transition: transform 0.2s, box-shadow 0.2s;
}

.q-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
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
  background-color: #e8f5e9; /* verde claro */
  color: #2e7d32; /* verde oscuro */
  box-shadow: 0 2px 4px rgba(46, 125, 50, 0.2);
}

.estado-badge.inactivo {
  background-color: #ffebee; /* rojo claro */
  color: #c62828; /* rojo oscuro */
  box-shadow: 0 2px 4px rgba(198, 40, 40, 0.2);
}
</style>
