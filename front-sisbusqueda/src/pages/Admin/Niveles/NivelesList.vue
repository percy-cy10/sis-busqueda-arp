<template>
  <q-dialog v-model="formDialog">
    <NivelesForm
      :title="formTitle"
      :edit="edit"
      :id="editId"
      ref="nivelesFormRef"
      @save="save"
    />
  </q-dialog>
  <q-page>
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />
        <q-breadcrumbs-el label="Niveles" icon="mdi-file-tree" />
      </q-breadcrumbs>
    </div>
    <q-separator />
    <div class="q-gutter-xs q-pa-sm">
      <q-btn
        color="primary"
        :disable="loading"
        :label="$q.screen.lt.sm ? '' : 'Agregar'"
        icon-right="add"
        @click="nuevo"
      />
    </div>
    <q-table
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="Niveles"
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
        />
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
              color="green"
              round
              icon="edit"
              class="q-mr-xs"
              @click="editar(props.row)"
            />
            <q-btn
              size="sm"
              outline
              color="red"
              round
              icon="delete"
              @click="eliminar(props.row.id)"
            />
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </q-page>
</template>

<!-- <script setup>
import { ref, onMounted } from "vue";
import NivelService from "src/services/NivelService";
import { useQuasar } from "quasar";
import NivelesForm from "./NivelesForm.vue";

const $q = useQuasar();
const columns = [
  { name: "id", label: "Id", align: "center", field: (row) => row.id, sortable: true },
  { name: "nombre", label: "Nombre", align: "center", field: (row) => row.nombre, sortable: true },
];
const tableRef = ref();
const formDialog = ref(false);
const nivelesFormRef = ref();
const formTitle = ref("");
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
  const filterValue = props.filter;
  loading.value = true;
  const fetchCount = rowsPerPage === 0 ? 0 : rowsPerPage;
  const order_by = descending ? "-" + sortBy : sortBy;
  const { data, total = 0 } = await NivelService.getData({
    params: { rowsPerPage: fetchCount, page, search: filterValue, order_by },
  });
  rows.value.splice(0, rows.value.length, ...data);
  pagination.value.rowsNumber = total || data.length;
  pagination.value.page = page;
  pagination.value.rowsPerPage = rowsPerPage;
  pagination.value.sortBy = sortBy;
  pagination.value.descending = descending;
  loading.value = false;
}

onMounted(() => {
  tableRef.value.requestServerInteraction();
});

function nuevo() {
  formTitle.value = "Nuevo Nivel";
  formDialog.value = true;
  edit.value = false;
  editId.value = null;
  nivelesFormRef.value.form.reset();
}

async function editar(row) {
  formTitle.value = "Editar Nivel";
  formDialog.value = true;
  edit.value = true;
  editId.value = row.id;
  const data = await NivelService.get(row.id);
  nivelesFormRef.value.setValue(data);
}

async function eliminar(id) {
  $q.dialog({
    title: "¿Estas seguro de eliminar este registro?",
    message: "Este proceso es irreversible.",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    await NivelService.delete(id);
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

function save() {
  formDialog.value = false;
  tableRef.value.requestServerInteraction();
  $q.notify({
    type: "positive",
    message: "Guardado con Exito.",
    position: "top-right",
    progress: true,
    timeout: 1000,
  });
}
</script> -->
<script setup>
import { ref, onMounted, nextTick } from "vue";
import NivelService from "src/services/NivelService";
import { useQuasar } from "quasar";
import NivelesForm from "./NivelesForm.vue";

const $q = useQuasar();
const columns = [
  {
    name: "id",
    label: "Id",
    align: "center",
    field: (row) => row.id,
    sortable: true,
  },
  {
    name: "nombre",
    label: "Nombre",
    align: "center",
    field: (row) => row.nombre,
    sortable: true,
  },
];
const tableRef = ref();
const formDialog = ref(false);
const nivelesFormRef = ref();
const formTitle = ref("");
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
  const filterValue = props.filter;
  loading.value = true;
  const fetchCount = rowsPerPage === 0 ? 0 : rowsPerPage;
  const order_by = descending ? "-" + sortBy : sortBy;
  const { data, total = 0 } = await NivelService.getData({
    params: { rowsPerPage: fetchCount, page, search: filterValue, order_by },
  });
  rows.value.splice(0, rows.value.length, ...data);
  pagination.value.rowsNumber = total || data.length;
  pagination.value.page = page;
  pagination.value.rowsPerPage = rowsPerPage;
  pagination.value.sortBy = sortBy;
  pagination.value.descending = descending;
  loading.value = false;
}

onMounted(() => {
  tableRef.value.requestServerInteraction();
});

function nuevo() {
  formTitle.value = "Nuevo Nivel";
  edit.value = false;
  editId.value = null;

  formDialog.value = true;
  nextTick(() => {
    if (nivelesFormRef.value && nivelesFormRef.value.form) {
      nivelesFormRef.value.form.reset();
    }
  });
}

async function editar(row) {
  try {
    formTitle.value = "Editar Nivel";
    edit.value = true;
    editId.value = row.id;

    const data = await NivelService.get(row.id);

    formDialog.value = true;
    await nextTick();

    if (nivelesFormRef.value) {
      nivelesFormRef.value.setValue({
        nombre: data.nombre,
      });
    }
  } catch (error) {
    console.error("Error editing nivel:", error);
    $q.notify({
      type: "negative",
      message: "Error al cargar el nivel",
      position: "top-right",
    });
  }
}

async function eliminar(id) {
  $q.dialog({
    title: "¿Estas seguro de eliminar este registro?",
    message: "Este proceso es irreversible.",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    await NivelService.delete(id);
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

async function save() {
  try {
    formDialog.value = false;
    await tableRef.value.requestServerInteraction();
    $q.notify({
      type: "positive",
      message: "Guardado con éxito",
      position: "top-right",
      timeout: 2000,
    });
  } catch (error) {
    console.error("Error saving:", error);
    $q.notify({
      type: "negative",
      message: "Error al guardar",
      position: "top-right",
    });
  }
}
</script>
