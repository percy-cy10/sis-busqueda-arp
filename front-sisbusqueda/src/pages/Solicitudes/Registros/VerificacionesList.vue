<template>
  <q-dialog v-model="formPermisos">
    <Verificaciones
      :title="title"
      :edit="edit"
      :id="editId"
      ref="verificacionesformRef"
      @save="save"
    ></Verificaciones>
  </q-dialog>
  <q-page>
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />

        <q-breadcrumbs-el
          label="Historial de Registro de Verificaciones"
          icon="mdi-key"
        />
      </q-breadcrumbs>
    </div>
    <q-separator />
    <!-- <div class="q-gutter-xs q-pa-sm">
        <q-btn
          color="primary"
          :disable="loading"
          :label="$q.screen.lt.sm ? '' : 'Agregar'"
          icon-right="add"
          @click="
            {
              formPermisos = true;
              edit = false;
              title = 'Añadir Verificaciones';
            }
          "
        />
      </div> -->

    <q-table
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="Historial de Registro de Verificaciones"
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
          <q-th auto-width> Acciones </q-th>
        </q-tr>
      </template>

      <template v-slot:body="props">
        <q-tr :props="props">
          <q-td v-for="col in props.cols" :key="col.name" :props="props">
            {{ col.value }}
          </q-td>
          <q-td auto-width>
            <q-btn
              size="sm"
              outline
              color="cyan-4"
              round
              @click="show(props.row.id)"
              icon="visibility"
              class="q-mr-xs"
            >
            </q-btn>
            <!-- <q-btn
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
              /> -->
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from "vue";
import VerificacionService from "src/services/VerificacionService";
import { useQuasar } from "quasar";
import Verificaciones from "src/pages/Solicitudes/Registros/VerificacionesForm.vue";
import { useRouter } from "vue-router";

const $q = useQuasar();
const router = useRouter();

const columns = [
  {
    field: (row) => row.id,
    name: "id",
    label: "ID",
    align: "left",
    sortable_: true,
    search: true,
  },
  {
    field: (row) => row.registro_busqueda.solicitud.id.toString().padStart(5, "0"),
    name: "registro_busqueda.solicitud.id",
    label: "Numero de Solicitud - Busqueda",
    align: "center",
    sortable_: true,
  },
  {
    field: (row) => row.registro_busqueda.solicitud.otorgantes,
    name: "registro_busqueda.solicitud.otorgantes",
    label: "S-Otoragante",
    align: "left",
    sortable_: true,
    search: true,
  },
  {
    field: (row) => row.registro_busqueda.solicitud.favorecidos,
    name: "registro_busqueda.solicitud.favorecidos",
    label: "S-Favorecido",
    align: "left",
    sortable_: true,
    search: true,
  },
  {
    field: (row) => row.registro_busqueda.user.name,
    name: "registro_busqueda.user.name",
    label: "Busqueda Realizada",
    align: "center",
    sortable_: true,
  },
  // {
  //   field: (row) => row.solicitud.otorgantes,
  //   name: "solicitud.otorgantes",
  //   label: "S-Otoragante",
  //   align: "left",
  //   sortable_: true,
  //   search: true,
  // },
  // {
  //   field: (row) => row.solicitud.favorecidos,
  //   name: "solicitud.favorecidos",
  //   label: "S-Favorecido",
  //   align: "left",
  //   sortable_: true,
  //   search: true,
  // },
  {
    field: (row) => row.user.name,
    name: "user.name",
    label: "Usuario Registrado",
    align: "left",
    sortable_: true,
    search: true,
  },
  {
    field: (row) => row.observaciones,
    name: "observaciones",
    label: "Observaciones",
    align: "center",
    sortable_: true,
    search: true,
  },
  {
    field: (row) => row.updated_at,
    name: "updated_at",
    label: "Fecha actualizacion",
    align: "center",
    sortable_: true,
  },
];

const tableRef = ref();
const formPermisos = ref(false);
const verififormRef = ref();
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

async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  const filter = props.filter;
  loading.value = true;

  const fetchCount = rowsPerPage === 0 ? 0 : rowsPerPage;
  const order_by = descending ? "-" + sortBy : sortBy;
  const { data, total = 0 } = await VerificacionService.getData({
    params: { rowsPerPage: fetchCount, page, search: filter, order_by },
  });
  console.log(data);
  // clear out existing data and add new
  rows.value.splice(0, rows.value.length, ...data);
  // don't forget to update local pagination object
  !total
    ? (pagination.value.rowsNumber = data.length)
    : (pagination.value.rowsNumber = total);
  pagination.value.page = page;
  pagination.value.rowsPerPage = rowsPerPage;
  pagination.value.sortBy = sortBy;
  pagination.value.descending = descending;
  // ...and turn of loading indicator
  loading.value = false;
}

onMounted(() => {
  tableRef.value.requestServerInteraction();
});

const save = () => {
  formPermisos.value = false;
  tableRef.value.requestServerInteraction();
  $q.notify({
    type: "positive",
    message: "Guardado con Exito.",
    position: "top-right",
    progress: true,
    timeout: 1000,
  });
};

async function show(id) {
  router.push({
    name: "Verificacioneshow",
    params: { id: id },
  });
}

async function editar(id) {
  title.value = "Editar Sub ";
  formPermisos.value = true;
  edit.value = true;
  editId.value = id;
  const row = await VerificacionService.get(id);
  console.log(row);

  verififormRef.value.form.setData({
    id: row.id,
    nombre: row.nombre,
  });
}

async function eliminar(id) {
  $q.dialog({
    title: "¿Estas seguro de eliminar este registro?",
    message: "Este proceso es irreversible.",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    await VerificacionService.delete(id);
    tableRef.value.requestServerInteraction();
    $q.notify({
      type: "positive",
      message: "Eliminado con Exito.",
      position: "top-right",
      progress: true,
      timeout: 1000,
    });
  });
}
</script>
