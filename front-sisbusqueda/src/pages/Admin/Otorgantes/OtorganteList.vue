<template>
  <q-page class="q-pa-md">
    <div class="q-mb-md">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />
        <q-breadcrumbs-el label="Otorgantes" icon="mdi-account-heart" />
      </q-breadcrumbs>
      <q-separator class="q-my-sm" />
    </div>

    <div class="q-gutter-xs q-pa-sm">
      <q-btn
        color="primary"
        icon="add"
        label="Nuevo Otorgante"
        @click="abrirFormulario"
      />
    </div>

    <q-table
      :rows="rows"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :filter="filter"
      v-model:pagination="pagination"
      @request="onRequest"
      title="Otorgantes"
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table"
    >
      <template v-slot:top-right>
        <q-input
          outlined
          dense
          debounce="500"
          v-model="filter"
          placeholder="Buscar"
          clearable
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td auto-width class="q-gutter-x-sm">
          <q-btn
            icon="edit"
            size="sm"
            color="primary"
            round
            outline
            @click="editar(props.row)"
          />
          <q-btn
            icon="delete"
            color="negative"
            size="sm"
            round
            outline
            @click="eliminar(props.row.id)"
          />
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="showForm">
      <OtorganteForm
        :title="formTitle"
        :id="selectedId"
        :edit="isEdit"
        ref="otorganteFormRef"
        @save="refreshTable"
      />
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import { useQuasar } from "quasar";
import OtorganteService from "src/services/OtorganteService";
import OtorganteForm from "./OtorganteForm.vue";

const $q = useQuasar();
const columns = [
  { name: "id", label: "ID", field: "id", align: "center", sortable: true },
  { name: "nombre_completo", label: "Nombre Completo", field: "nombre_completo", align: "center", sortable: true },
  { name: "tipo", label: "Tipo", field: "tipo", align: "center", sortable: true },
  { name: "actions", label: "Acciones", align: "center" },
];

const rows = ref([]);
const filter = ref("");
const loading = ref(false);
const pagination = ref({
  sortBy: "id",
  descending: false,
  page: 1,
  rowsPerPage: 7,
  rowsNumber: 0,
});

const showForm = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);
const formTitle = ref("Nuevo otorgante");
const otorganteFormRef = ref();

const onRequest = async (props) => {
  loading.value = true;
  try {
    const { page, rowsPerPage, sortBy, descending } = props.pagination;
    const order_by = descending ? `-${sortBy}` : sortBy;
    const { data, total } = await OtorganteService.getData({
      params: {
        page,
        rowsPerPage,
        search: filter.value,
        order_by,
      },
    });

    rows.value = data;
    pagination.value.rowsNumber = total;
    pagination.value.page = page;
    pagination.value.rowsPerPage = rowsPerPage;
    pagination.value.sortBy = sortBy;
    pagination.value.descending = descending;
  } catch (error) {
    $q.notify({ type: "negative", message: "Error cargando datos" });
  } finally {
    loading.value = false;
  }
};

const abrirFormulario = () => {
  showForm.value = true;
  isEdit.value = false;
  formTitle.value = "Nuevo otorgante";
};

const editar = async (row) => {
  selectedId.value = row.id;
  isEdit.value = true;
  formTitle.value = "Editar otorgante";
  showForm.value = true;

  await nextTick();
  otorganteFormRef.value.loadData(row.id);
};

const eliminar = async (id) => {
  $q.dialog({
    title: "Confirmar eliminación",
    message: "¿Está seguro?",
    cancel: true,
  }).onOk(async () => {
    try {
      await OtorganteService.delete(id);
      refreshTable();
      $q.notify({
        type: "positive",
        message: "Eliminado correctamente",
        position: "top-right",
        progress: true,
        timeout: 1000
      });
    } catch (error) {
      $q.notify({
        type: "negative",
        message: "Error al eliminar",
        position: "top-right",
        progress: true,
        timeout: 1000
      });
    }
  });
};

const refreshTable = () => {
  showForm.value = false;
  onRequest({ pagination: pagination.value });
};

onMounted(() => onRequest({ pagination: pagination.value }));
</script>

<style scoped>
.my-sticky-header-table {
  max-height: 70vh;
}
</style>
