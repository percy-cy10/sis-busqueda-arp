<template>
    <q-dialog v-model="formPermisos">
      <NotariosForm
        :title="title"
        :edit="edit"
        :id="editId"
        ref="notariosformRef"
        @save="save"
      ></NotariosForm>
    </q-dialog>
    <q-page>
      <div class="q-pa-md q-gutter-sm">
        <q-breadcrumbs>
          <q-breadcrumbs-el icon="home" />

          <q-breadcrumbs-el label="Notarios" icon="mdi-key" />
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
              formPermisos = true;
              edit = false;
              title = 'Añadir Notario';
            }
          "
        />
      </div>

      <q-table
        :rows-per-page-options="[7, 10, 15]"
        class="my-sticky-header-table htable q-ma-sm"
        title="Permisos"
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
  import NotarioService from "src/services/NotarioService";
  import { useQuasar } from "quasar";
  import NotariosForm from "src/pages/Admin/Notarios/NotariosForm.vue";
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
      name: "nombre_completo",
      label: "Nombre Completo",
      align: "center",
      field: (row) => row.nombre_completo,
      sortable: true,
    },
    {
      name: "año_inicio",
      label: "Año Inicio",
      align: "center",
      field: (row) => row.año_inicio,
      sortable: true,
    },
    {
      name: "año_final",
      label: "Año Final",
      align: "center",
      field: (row) => row.año_final,
      sortable: true,
    },
    {
      name: "ubigeo.nombre",
      label: "Ubigeo",
      align: "center",
      field: (row) => row.ubigeo?.nombre,
      sortable: true,
    },

  ];

  const tableRef = ref();
  const formPermisos = ref(false);
  const notariosformRef = ref();
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
    const { data, total = 0 } = await NotarioService.getData({
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
  async function editar(id) {
    title.value = "Editar Notario";
    formPermisos.value = true;
    edit.value = true;
    editId.value = id;
    const row = await NotarioService.get(id);
    console.log(row);

    notariosformRef.value.form.setData({
      id: row.id,
      nombres: row.nombres,
      apellido_paterno: row.apellido_paterno,
      apellido_materno: row.apellido_materno,
      año_inicio: row.año_inicio,
      año_final: row.año_final,
      ubigeo_cod: row.ubigeo_cod,
    });
    notariosformRef.value.setValue(row);
  }

  async function eliminar(id) {
    $q.dialog({
      title: "¿Estas seguro de eliminar este registro?",
      message: "Este proceso es irreversible.",
      cancel: true,
      persistent: true,
    }).onOk(async () => {
      await NotarioService.delete(id);
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

  <!-- <style lang="sass" scoped>
    .my-sticky-header-table
      /* height or max-height is important */
      height: 80vh

      .q-table__top,
      .q-table__bottom,
      thead tr:first-child th
        /* bg color is important for th; just specify one */


      thead tr th
        position: sticky
        z-index: 1
        background-color: $primary
        color: white
      thead tr:first-child th
        top: 0

      /* this is when the loading indicator appears */
      &.q-table--loading thead tr:last-child th
        /* height of all previous header rows */
        top: 48px

      /* prevent scrolling behind sticky top row on focus */
      tbody
        /* height of all previous header rows */
        scroll-margin-top: 48px

    .htable
      #height: calc(100vh - 157px)
    </style>
     -->
