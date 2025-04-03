<template>
  <q-dialog v-model="formPermisos">
    <BusquedasForm
      :title="title"
      :edit="edit"
      :id="editId"
      ref="busquedasformRef"
      @save="save"
    ></BusquedasForm>
  </q-dialog>
  <q-page>
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />

        <q-breadcrumbs-el label="Historial de Registro de Busqueda" icon="mdi-key" />
      </q-breadcrumbs>
    </div>
    <q-separator />
    <div class="q-gutter-xs q-pa-sm">
      <!-- <q-btn
        color="primary"
        :disable="loading"
        :label="$q.screen.lt.sm ? '' : 'Agregar'"
        icon-right="add"
        @click="
          {
            formPermisos = true;
            edit = false;
            title = 'Añadir Busqueda';
          }
        "
      /> -->
    </div>
    <div class="q-gutter-xs q-pa-sm">
    <div class="row">
      <SelectInput dense clearable outlined 
      class="col-4 q-px-xs" 
      label="Usuario Registrado"
      v-model="usarioregistrado"
      :options="BusquedaService" OptionLabel="name" OptionValue="id" 
      />
    </div>
    </div>
    <q-table
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="Historial de Registro de Busqueda"
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
            />
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
import BusquedaService from "src/services/BusquedaService";
import { useQuasar } from "quasar";
import BusquedasForm from "src/pages/Solicitudes/Registros/BusquedasForm.vue";
import { useRouter } from "vue-router";
import SelectInput from "src/components/SelectInput.vue";
import UsuarioService from "src/services/UsuarioService";
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
    field: (row) => row.solicitud.id.toString().padStart(5, '0'),
    name: "solicitud.id",
    label: "Numero de solicitud",
    align: "center",
    sortable_: true,
  },
  {
    field: (row) => row.solicitud.otorgantes,
    name: "solicitud.otorgantes",
    label: "S-Otoragante",
    align: "left",
    sortable_: true,
    search: true,
  },
  {
    field: (row) => row.solicitud.favorecidos,
    name: "solicitud.favorecidos",
    label: "S-Favorecido",
    align: "left",
    sortable_: true,
    search: true,
  },
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
  // {
  //   field: (row) => row.cantidad_copia,
  //   name: "cantidad_copia",
  //   label: "Cantidad de Copia",
  //   align: "center",
  //   sortable_: true,
  //   search: true,
  // },
  // {
  //   field: (row) => row.solicitud.estado,
  //   name: "estado",
  //   label: "Estado",
  //   align: "center",
  //   sortable_: true,
  // },
  {
    field: (row) => row.updated_at,
    name: "updated_at",
    label: "Fecha actualizacion",
    align: "center",
    sortable_: true,
  },
];

const form = ref({
  solicitud: {
        sub_serie: {},
        notario: {},
        solicitante: {},
        user: {},
    },
    user: {},
  });
const usarioregistrado = ref();
const tableRef = ref();
const formPermisos = ref(false);
const busquedasformRef = ref();
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
  rowsPerPage: 15,
  rowsNumber: 10,
});

async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  const filter = props.filter;
  loading.value = true;

  const fetchCount = rowsPerPage === 0 ? 0 : rowsPerPage;
  const order_by = descending ? "-" + sortBy : sortBy;
  const { data, total = 0 } = await BusquedaService.getData({
    params: { rowsPerPage: fetchCount, page, search: filter, order_by },
  });
  // console.log(data);
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

// async function data2 () {
//   form.value = (await BusquedaService.getData()).data;
//   console.log(form.value);
// }

// data2();

// onMounted(async() => {
//   loading.value = true;
//   const res =  await(BusquedaService.getData({ params: { rowsPerPage: 0, order_by: 'users.name' } })).data;
//   form.value = res;
//   // console.log(form.value);
//   loading.value = false;
// });

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
    name: "Busquedashow",
    params: { id: id },
  });
}

async function editar(id) {
  title.value = "Editar Sub ";
  formPermisos.value = true;
  edit.value = true;
  editId.value = id;
  const row = await BusquedaService.get(id);
  console.log(row);

  busquedasformRef.value.form.setData({
    id: row.id,
  });
}

async function eliminar(id) {
  $q.dialog({
    title: "¿Estas seguro de eliminar este registro?",
    message: "Este proceso es irreversible.",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    await BusquedaService.delete(id);
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
