<template>
  <q-page>
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />

        <q-breadcrumbs-el label="Data Sia" icon="mdi-account-key" />
      </q-breadcrumbs>
    </div>
    <q-separator />
    <div class="row">
      <SelectInput class="col-4 q-px-xs" label="Notarios" dense clearable
        v-model="nombreNotario" :options="GenerateListService" :GenerateList="{ column: 'notario', table: 'sia' }" />
      <SelectInput class="col-4 q-px-xs" label="Otorgantes" dense clearable
        v-model="nombreOtorgante" :options="GenerateListService" :GenerateList="{ column: 'otorgante', table: 'sia' }" />
      <SelectInput class="col-4 q-px-xs" label="Series" dense clearable
        v-model="nombreSubserie" :options="GenerateListService" :GenerateList="{ column: 'serie', table: 'sia' }" />
    </div>
    <q-table
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="Tabla Sia"
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
            <span v-if="col.sortable_" class="span-icono" @click="props.sort(col.name)">
              <q-icon class="q-table__sort-icon icon-sort" style="" name="arrow_downward" />
              {{ col.label }}
            </span>
            <span v-else>{{ col.label }}</span>
            <q-icon v-if="col.search" class="q-pa-xs q-mx-xs cursor-pointer" :class="$q.dark.isActive ? 'btn-buscar-dark' : 'btn-buscar'" name="search" size="xs">
              <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                <q-input clearable class="q-px-sm" dense debounce="500" v-model="busColum[col.name]" placeholder="Buscar">
                  <template v-slot:append> <q-icon name="search" /> </template>
                </q-input>
              </q-popup-proxy>
            </q-icon>
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
      <template v-slot:loading>
        <q-inner-loading showing color="primary" />
      </template>
      <template v-slot:no-data="{ icon, message, filter }">
        <div class="full-width row flex-center q-gutter-sm text-subtitle1">
          <span>{{ message }}</span>
          <q-icon size="2em" :name="filter ? 'filter_b_and_w' : icon" />
        </div>
      </template>
    </q-table>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import SiaService from "src/services/arp_v1/SiaService";
import { useQuasar } from "quasar";
import GenerateListService from "src/services/arp_v1/GenerateListService";
import SelectInput from "src/components/SelectInput.vue";
const $q = useQuasar();

async function verDat(){
  const dato = await SiaService.getData({
    params: { rowsPerPage: 100, page:1, search: 'Manuel', order_by:'id' },
  })
  console.log(dato);
}

// verDat();

const columns = [
  { field: (row) => row.id, name: "id", label: "Id", align: "center", sortable_: true, },
  { field: (row) => row.notario, name: "notario", label: "Notario", align: "center", sortable_: true,},
  { field: (row) => row.otorgante, name: "otorgante", label: "otorgante", align: "center", sortable_: true,},
  { field: (row) => row.favorecido, name: "favorecido", label: "favorecido", align: "center", sortable_: true,},
  { field: (row) => row.fecha, name: "fecha", label: "fecha", align: "center", sortable_: true,},
  { field: (row) => row.serie, name: "serie", label: "serie", align: "center", sortable_: true,},
  { field: (row) => row.folio, name: "folio", label: "folio", align: "center", sortable_: true,},
  { field: (row) => row.escritura, name: "escritura", label: "escritura", align: "center", sortable_: true,},
  { field: (row) => row.bien, name: "bien", label: "bien", align: "center", sortable_: true,},
];
const nombreNotario = ref();
const nombreOtorgante = ref();
const nombreSubserie = ref();
const busColum = ref({});
watch(nombreNotario, (newValue, oldValue) => {
  tableRef.value.requestServerInteraction();
});
watch(nombreOtorgante, (newValue, oldValue) => {
  tableRef.value.requestServerInteraction();
});
watch(nombreSubserie, (newValue, oldValue) => {
  tableRef.value.requestServerInteraction();
});
const tableRef = ref();
const formRole = ref(false);
const rolesformRef = ref();
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
  const order_by = filter? '': descending ? "-" + sortBy : sortBy;
  const filtros = {notario: nombreNotario.value, otorgante: nombreOtorgante.value, serie: nombreSubserie.value};
  const { data, total = 0 } = await SiaService.getData({
    params: { rowsPerPage: fetchCount, page, search: filter, order_by, filter_by:filtros,},
  });;
  // console.log(data);
  // clear out existing data and add new
  rows.value.splice(0, rows.value.length, ...data);
  // don't forget to update local pagination object
  for (const key in columns) {
    if (columns[key].name === "index") {
      let cantidad = (page - 1) * fetchCount;
      rows.value.forEach((row, index) => {
        row.index = index + 1 + cantidad;
      });
      break;
    }
  }
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
  formRole.value = false;
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
  title.value = "Editar Rol";
  formRole.value = true;
  edit.value = true;
  editId.value = id;
  const row = await SiaService.get(id);
  console.log(row);

  rolesformRef.value.form.setData({
    id: row.rol.id,
    name: row.rol.name,
    permisosSelected: row.permisosSelected,
  });
}

async function eliminar(id) {
  $q.dialog({
    title: "¿Estas seguro de eliminar este registro?",
    message: "Este proceso es irreversible.",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    await SiaService.delete(id);
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

<style lang="sass">
.my-sticky-header-table
  /* height or max-height is important */
  height: 80vh
  min-height: 40vh
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
.span-icono
  cursor: pointer
  &:hover .icon-sort
    opacity: .64 !important

.btn-buscar:hover
  color: $grey-9
  border-radius: 8px
  background-color: $grey-2

.btn-buscar-dark:hover
  // border: 1px solid $grey-10
  border-radius: 8px
  background-color: $grey-9
</style>
