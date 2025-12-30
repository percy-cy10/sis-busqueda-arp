<template>
  <q-dialog v-model="formLibros" persistent>
    <LibrosForm
      :title="title"
      :edit="edit"
      :id="editId"
      ref="librosFormRef"
      @save="handleSave"
      @close="formLibros = false"
    />
  </q-dialog>

  <q-page class="q-pa-md">
    <!-- Header y Breadcrumbs -->
    <div class="q-mb-md">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" to="/" />
        <q-breadcrumbs-el label="Libros" icon="menu_book" />
      </q-breadcrumbs>
      <q-separator class="q-my-sm" />
    </div>

    <!-- Barra de Herramientas -->
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

    <!-- Tabla de Libros -->
    <!-- <q-table
      flat
      bordered
      :rows="rows"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :filter="filter"
      v-model:pagination="pagination"
      @request="onRequest"
      :rows-per-page-options="[5, 10, 20]"
    > -->

    <q-table
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="Libros"
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

      <!-- Columna de Notario: se muestra solo el id -->
      <template v-slot:body-cell-notario="props">
        <q-td :props="props">
          <div v-if="props.row.notario_data" class="text-weight-medium">
            {{ props.row.notario_data.nombre_completo }} <!-- Cambiado de id a nombre_completo -->
          </div>
          <q-badge v-else color="grey-6" class="q-px-sm">
            Sin asignar
          </q-badge>
        </q-td>
      </template>

<!-- Columna de Notario: se muestra solo el id -->

      <template v-slot:body-cell-estado="props">
        <q-td :props="props" class="text-center">
          <q-badge
            :color="props.row.estado ? 'green-6' : 'red-5'"
            text-color="white"
            rounded
            class="q-badge--estado"
          >
            {{ props.row.estado ? 'ACTIVO' : 'INACTIVO' }}
          </q-badge>
        </q-td>
      </template>


      <!-- Columna de Acciones -->
      <template v-slot:body-cell-actions="props">
        <q-td auto-width class="q-gutter-x-sm">


          <!-- Botón de Editar -->
          <q-btn
            size="sm"
            outline
            round

            color="blue"
            icon="edit"
            @click="editarLibro(props.row)"
            :disable="loading"
          >
            <q-tooltip>Editar</q-tooltip>
          </q-btn>

          <!-- Botón de Eliminar -->
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

          <!-- Botón de Estado -->
          <q-btn
            size="sm"
            outline
            round

            :color="props.row.estado ? 'green' : 'gray'"
            :icon="props.row.estado ? 'toggle_on' : 'toggle_off'"
            @click="toggleEstado(props.row)"
            :disable="loading"
          >
            <q-tooltip>{{ props.row.estado ? 'Desactivar' : 'Activar' }}</q-tooltip>
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
import { ref, onMounted, nextTick } from 'vue';
import { useQuasar } from 'quasar';
import LibroService from 'src/services/LibroService';
import NotarioService from 'src/services/NotarioService';
import LibrosForm from 'src/pages/Admin/Libros/LibrosForm.vue';

const $q = useQuasar();

// Configuración de columnas
const columns = [
  {
    name: 'id',
    required: true,
    label: 'ID',
    align: 'left',
    field: row => row.id,
    sortable: true,
    style: 'width: 80px'
  },
  {
    name: 'protocolo',
    label: 'Protocolo',
    align: 'left',
    field: 'protocolo',
    sortable: true
  },
  {
    name: 'notario',
    label: 'Notario',
    align: 'left',
    // Se muestra únicamente el id del notario
    field: row => row.notario_data?.id || 'Sin asignar'
  },
  {
    name: 'estado',
    label: 'Estado',
    align: 'center',
    field: 'estado',
    style: 'width: 100px'
  },
  {
    name: 'actions',
    label: 'Acciones',
    align: 'center',
    style: 'min-width: 180px'
  }
];

// Estado reactivo
const rows = ref([]);
const loading = ref(false);
const filter = ref('');
const formLibros = ref(false);
const librosFormRef = ref(null);
const title = ref('');
const edit = ref(false);
const editId = ref(null);

// Configuración de paginación
const pagination = ref({
  sortBy: 'id',
  descending: false,
  page: 1,
  rowsPerPage: 7,
  rowsNumber: 10,
});

// Cargar datos iniciales
onMounted(() => {
  loadInitialData();
});

// Función para cargar datos iniciales
const loadInitialData = async () => {
  try {
    loading.value = true;
    await onRequest({
      pagination: pagination.value,
      filter: filter.value
    });
  } catch (error) {
    console.error('Error al cargar datos iniciales:', error);
    $q.notify({
      type: 'negative',
      message: 'Error al cargar datos iniciales',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
};



// const onRequest = async (props) => {
//   loading.value = true;
//   try {
//     pagination.value = props.pagination;

//     const [notariosResponse, librosResponse] = await Promise.all([
//       NotarioService.getAll({ params: { search: filter.value } }),
//       LibroService.getData({
//         params: {
//           page: pagination.value.page,
//           rowsPerPage: pagination.value.rowsPerPage,
//           search: props.filter,
//           sortBy: pagination.value.sortBy,
//           descending: pagination.value.descending
//         }
//       })
//     ]);

//     // 🔑 Ahora notariosResponse es un array directamente
//     const notariosMap = (notariosResponse || []).reduce((map, notario) => {
//       if (notario?.id) {
//         map[notario.id] = notario;
//       }
//       return map;
//     }, {});


//     rows.value = (librosResponse.data || []).map(libro => ({
//       ...libro,
//       notario_data: libro.notario_id ? notariosMap[libro.notario_id] : null,
//       estado: Boolean(libro.estado)
//     }));

//     pagination.value.rowsNumber = librosResponse.total || 0;
//   } catch (error) {
//     console.error('Error al cargar datos:', error);
//     $q.notify({
//       type: 'negative',
//       message: 'Error al cargar datos',
//       caption: error.message,
//       position: 'top'
//     });
//     rows.value = [];
//     pagination.value.rowsNumber = 0;
//   } finally {
//     loading.value = false;
//   }
// };




// Función para abrir formulario de creación
const onRequest = async (props) => {
  loading.value = true;
  try {
    pagination.value = props.pagination;

    const [notariosResponse, librosResponse] = await Promise.all([
      NotarioService.getAll({ params: { search: filter.value } }),
      LibroService.getData({
        page: pagination.value.page,
        per_page: pagination.value.rowsPerPage, // 👈 Laravel espera per_page
        search: filter.value,                   // 👈 filtro backend
        sortBy: pagination.value.sortBy,
        descending: pagination.value.descending
      })
    ]);

    const notariosMap = (notariosResponse || []).reduce((map, notario) => {
      if (notario?.id) map[notario.id] = notario;
      return map;
    }, {});

    rows.value = (librosResponse.data || []).map(libro => ({
      ...libro,
      notario_data: libro.notario_id ? notariosMap[libro.notario_id] : null,
      estado: Boolean(libro.estado)
    }));

    pagination.value.rowsNumber = librosResponse.total || 0;
  } catch (error) {
    console.error('Error al cargar datos:', error);
    rows.value = [];
    pagination.value.rowsNumber = 0;
  } finally {
    loading.value = false;
  }
};



const openForm = () => {
  formLibros.value = true;
  edit.value = false;
  editId.value = null;
  title.value = 'Nuevo Libro';
};

// // Función para editar libro

// ... imports y estado reactivo ...

const editarLibro = async (libro) => {
  try {
    const libroCompleto = await LibroService.get(libro.id);

    formLibros.value = true;
    edit.value = true;
    editId.value = libro.id;
    title.value = 'Editar Libro';

    await nextTick();

    if (librosFormRef.value?.setFormData) {
      librosFormRef.value.setFormData(libroCompleto);
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Error al cargar datos del libro',
      caption: error.message
    });
  }
};


// Función para cambiar estado del libro
const toggleEstado = async (libro) => {
  const estadoOriginal = libro.estado;
  try {
    loading.value = true;

    // Actualización optimista en la UI
    libro.estado = !estadoOriginal;
    rows.value = [...rows.value];

    // Llamada al servicio para cambiar el estado en la base de datos
    const response = await LibroService.toggleEstado(libro.id);

    // Si la respuesta incluye la propiedad 'estado', se actualiza; de lo contrario se asume éxito
    if (response && response.hasOwnProperty('estado')) {
      libro.estado = response.estado;
    }
    rows.value = [...rows.value];

    $q.notify({
      type: 'positive',
      message: `Estado cambiado a ${libro.estado ? 'ACTIVO' : 'INACTIVO'}`,
      position: 'top-right',
      timeout: 1000
    });
  } catch (error) {
    console.error('Error al cambiar estado:', error);
    // Revertir en caso de error
    libro.estado = estadoOriginal;
    rows.value = [...rows.value];
    $q.notify({
      type: 'negative',
      message: 'Error al cambiar estado',
      position: 'top-right',
      timeout: 2000
    });
  } finally {
    loading.value = false;
  }
};

// Función para confirmar eliminación
const confirmarEliminar = (id) => {
  $q.dialog({
    title: 'Confirmar Eliminación',
    message: '¿Está seguro de eliminar este libro?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    eliminarLibro(id);
  });
};

// Función para eliminar libro
const eliminarLibro = async (id) => {
  try {
    loading.value = true;
    await LibroService.delete(id);
    $q.notify({
      type: 'positive',
      message: 'Libro eliminado correctamente',
      position: 'top-right'
    });
    // Recargar datos después de la eliminación
    await onRequest({
      pagination: pagination.value,
      filter: filter.value
    });
  } catch (error) {
    console.error('Error al eliminar libro:', error);
    $q.notify({
      type: 'negative',
      message: 'Error al eliminar libro',
      caption: error.message,
      position: 'top-right'
    });
  } finally {
    loading.value = false;
  }
};

// Función para manejar el guardado del formulario
const handleSave = async () => {
  try {
    formLibros.value = false;
    $q.notify({
      type: 'positive',
      message: edit.value ? 'Libro actualizado correctamente' : 'Libro creado correctamente',
      position: 'top-right',
      timeout: 1000
    });
    // Recargar datos para actualizar la lista
    await onRequest({
      pagination: pagination.value,
      filter: filter.value
    });
  } catch (error) {
    console.error('Error al guardar:', error);
    $q.notify({
      type: 'negative',
      message: 'Error al guardar los cambios',
      position: 'top-right'
    });
    formLibros.value = true;
  }
};
</script>

<style scoped>
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
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.q-table__container .q-btn[color="green"] {
  background: linear-gradient(145deg, #4CAF50, #81C784);
}

.q-table__container .q-btn[color="red"] {
  background: linear-gradient(145deg, #F44336, #E57373);
}

.q-table__container .q-btn[color="blue"] {
  background: linear-gradient(145deg, #2196F3, #64B5F6);
}

.q-table__container .q-btn[color="orange"] {
  background: linear-gradient(145deg, #FF9800, #FFB74D);
}
</style>
