<template>
  <q-dialog v-model="formEscritura">
    <EscrituraForm
      :title="title"
      :edit="edit"
      :id="editId"
      ref="escrituraFormRef"
      @save="save"
    ></EscrituraForm>
  </q-dialog>

  <q-page>
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />
        <q-breadcrumbs-el label="Escrituras" icon="mdi-pencil-box" />
      </q-breadcrumbs>
    </div>

    <q-separator />

    <div class="q-gutter-xs q-pa-sm">
      <q-btn
        color="primary"
        :disable="loading"
        :label="$q.screen.lt.sm ? '' : 'Agregar'"
        icon-right="add"
        @click="
          {
            formEscritura = true;
            edit = false;
            title = 'Añadir Escritura';
          }
        "
      />
    </div>

    <q-table
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="Escrituras"
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

      <template v-slot:header="props">
        <q-tr :props="props">
          <q-th v-for="col in props.cols" :key="col.name" :props="props">
            {{ col.label }}
          </q-th>
          <q-th auto-width>Acciones</q-th>
        </q-tr>
      </template>

      <template v-slot:body="props">
        <q-tr :props="props">
          <!-- <q-td v-for="col in props.cols" :key="col.name" :props="props">
            <div v-if="['favorecidos', 'otorgantes'].includes(col.name)" v-html="col.value"></div>
            {{ col.value }}
          </q-td> -->
          <q-td v-for="col in props.cols" :key="col.name" :props="props">
            <div v-if="['favorecidos', 'otorgantes'].includes(col.name)" v-html="col.value"></div>
            <div v-else>{{ col.value }}</div>
          </q-td>
          <q-td auto-width>
            <q-btn
              size="sm"
              outline
              color="green"
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
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </q-page>
</template>

<script setup>

import { ref, onMounted } from "vue";
import EscrituraService from "src/services/EscrituraService";
import { useQuasar } from "quasar";
import EscrituraForm from "src/pages/Admin/Escrituras/EscrituraForm.vue";

const $q = useQuasar();

// Definición de columnas para la tabla
const columns = [
  {
    name: "id",
    label: "ID",
    align: "center",
    field: (row) => row.id,
    sortable: true,
  },
  {
    name: "bien",
    label: "Bien",
    align: "center",
    field: (row) => row.bien,
    sortable: true,
  },
  // {
  //   name: "subserie",
  //   label: "Subserie",
  //   align: "center",
  //   field: (row) => row.subserie.nombre,
  //   sortable: true,
  // },
  {
    name: "Cod. Escritura",
    label: "Cod. Escritura",
    align: "center",
    field: (row) => row.cod_escritura,
    sortable: true,
  },
  {
    name: "cod_folioInicial",
    label: "Folio Inicial",
    align: "center",
    field: (row) => row.cod_folioInicial,
    sortable: true
  },
  // {
  //   name: "cod_folioFinal",
  //   label: "Folio Final",
  //   align: "center",
  //   field: (row) => row.cod_folioFinal,
  //   sortable: true
  // },
  // {
  //   name: "favorecidos",
  //   label: "Favorecidos",
  //   align: "center",
  //   field: (row) => row.favorecidos.map(f => f.nombre_completo).join('<br>'), // Mostrar nombres
  //   sortable: false,
  // },
  // {
  //   name: "otorgantes",
  //   label: "Otorgantes",
  //   align: "center",
  //   field: (row) => row.otorgantes.map(o => o.nombre_completo).join('<br> '), // Mostrar nombres
  //   sortable: false,
  // },
  // {
  //   name: "favorecidos",
  //   label: "Favorecidos",
  //   align: "center",
  //   field: (row) => row.favorecidos.map(f => f.nombre_completo).join('<br>'), // Mostrar nombres con salto de línea
  //   sortable: false,
  //   format: val => val, // Asegura que se interprete como HTML
  // },
  // {
  //   name: "otorgantes",
  //   label: "Otorgantes",
  //   align: "center",
  //   field: (row) => row.otorgantes.map(o => o.nombre_completo).join('<br>'), // Mostrar nombres con salto de línea
  //   sortable: false,
  //   format: val => val, // Asegura que se interprete como HTML
  // },
  {
    name: "favorecidos",
    label: "Favorecidos",
    align: "center",
    field: row => {
      const list = row.favorecidos.map(f => f.nombre_completo);
      return list.length === 1 ? list[0] : list.join('<br>');
    },
    sortable: false,
    format: val => val,
  },
  {
    name: "otorgantes",
    label: "Otorgantes",
    align: "center",
    field: row => {
      const list = row.otorgantes.map(o => o.nombre_completo);
      return list.length === 1 ? list[0] : list.join('<br>');
    },
    sortable: false,
    format: val => val,
  },

  {
    name: "fecha",
    label: "Fecha",
    align: "center",
    field: (row) => row.fecha,
    sortable: true,
  },
  // {
  //   name: "libro",
  //   label: "Libro",
  //   align: "center",
  //   field: (row) => row.libro.protocolo,
  //   sortable: true,
  // },


    // NUEVO (Seguro) -------------------------------------------------------------
  // {
  //   name: "subserie",
  //   label: "Subserie",
  //   field: (row) => row.subserie?.nombre || "Sin subserie", // Operador opcional
  //   sortable: true
  // },

  {
    name: "subserie",
    label: "Subserie",
    align: "center",
    field: (row) => row.sub_serie?.nombre || "Sin especificar", // Operador opcional
    sortable: true
  },

  {
    name: "libro",
    label: "Libro",
    field: (row) => row.libro?.protocolo || "Sin libro",    // Fallback claro
    sortable: true
  }
];

// Variables reactivas
const tableRef = ref();
const formEscritura = ref(false);
const escrituraFormRef = ref();
const title = ref("");
const edit = ref(false);
const editId = ref();
const rows = ref([]);
const filter = ref("");
const loading = ref(false);
const pagination = ref({
  sortBy: "id",
  descending: false,
  page: 1,
  rowsPerPage: 7,
  rowsNumber: 10,
});

// Función que se ejecuta en cada request de la tabla


// NUEVO (Corregido):
async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  const currentFilter = props.filter;
  loading.value = true;

  try {
    const response = await EscrituraService.getData({
      params: {
        page: page,
        perPage: rowsPerPage, // Nombre estándar
        search: currentFilter,
        sortBy: descending ? `-${sortBy}` : sortBy
      }
    });

    // Manejo de diferentes estructuras de respuesta
    const datos = response.data?.data?.rows ||
                response.data?.rows ||
                response.data ||
                [];

    // Actualización completa de paginación
    pagination.value = {
      ...props.pagination,
      rowsNumber: response.data?.total ||
      response.data?.data?.total ||
      datos.length,
      page: page,
      rowsPerPage: rowsPerPage,
      sortBy: sortBy,
      descending: descending
    };

    // Asignación segura de datos
    rows.value = datos.map(item => ({
      ...item,
      subserie: item.sub_serie || { nombre: 'N/A' },
      libro: item.libro || { protocolo: 'N/A' }
    }));

  } catch (error) {
    console.error("Error cargando datos:", error);
    $q.notify({
      type: "negative",
      message: error.response?.data?.message || "Error cargando escrituras",
      position: "top-right",
      timeout: 5000
    });
  } finally {
    loading.value = false;
  }
}



onMounted(() => {
  tableRef.value.requestServerInteraction();
});

// Función para guardar (se cierra el diálogo y se refresca la tabla)
const save = () => {
  formEscritura.value = false;
  tableRef.value.requestServerInteraction();
  $q.notify({
    type: "positive",
    message: "Guardado con Éxito.",
    position: "top-right",
    progress: true,
    timeout: 1000,
  });
};

async function editar(id) {
  title.value = 'Editar Escritura';

  formEscritura.value = true;
  edit.value = true;
  editId.value = id;

  try {
    const response = await EscrituraService.get(id);
    // Validar la estructura de la respuesta
    if (!response ) {
      throw new Error("Respuesta de API inválida");
    }

    const data = response;

    // Convertir y forzar tipos de datos:
    // - La propiedad anio se usa como string
    // - Mes y día se convierten a número con un fallback (por ejemplo, a 1 si no está definido)
    // - Subserie y libro se asignan como números (suponiendo que la API retorna valores numéricos o cadenas numéricas)
    escrituraFormRef.value.form.setData({
      ...data,
      anio: data.anio ? data.anio.toString() : "",
      mes: data.mes ? Number(data.mes) : 1,
      dia: data.dia ? Number(data.dia) : 1,
      subserie_id: data.subserie_id ? Number(data.subserie_id) : null,
      libro_id: data.libro_id ? Number(data.libro_id) : null,
      favorecidos: response.favorecidos.map(f => f.id), // Asegurar que sean IDs
      otorgantes: response.otorgantes.map(o => o.id),  // Asegurar que sean IDs
    });
  } catch (error) {
    console.error("Error cargando escritura:", error);
    $q.notify({
      type: "negative",
      message: "Datos corruptos en la respuesta",
      position: "top-right"
    });
  }
}


async function eliminar(id) {
  $q.dialog({
    title: "¿Estás seguro de eliminar este registro?",
    message: "Este proceso es irreversible.",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await EscrituraService.delete(id); // Aquí debería ejecutarse la eliminación
      tableRef.value.requestServerInteraction(); // Actualizar la tabla
      $q.notify({
        type: "positive",
        message: "Eliminado con Éxito.",
        position: "top-right",
        progress: true,
        timeout: 1000,
      });
    } catch (error) {
      console.error("Error al eliminar registro:", error);
      $q.notify({
        type: "negative",
        message: "Error al eliminar el registro.",
        position: "top-right",
      });
    }
  });
}


</script>
