<template>
  <q-page class="q-pa-md">
    <q-tabs v-model="tab" class="text-white bg-primary" align="left">
      <q-tab name="nombre" label="Buscar por Nombre" />
      <q-tab name="juridico" label="Buscar datos Jurídicos" />
      <q-tab name="fecha" label="Buscar por Fecha" />
      <q-tab name="bien" label="Buscar por Bien" />
    </q-tabs>
    <q-separator />

    <!-- Buscar por Nombre -->
    <div v-if="tab === 'nombre'" class="q-mt-md">
      <q-form @submit.prevent="buscarPorNombre">
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <SelectOtorgante v-model="filtros.otorgante_id" />
          </div>
          <div class="col-12 col-md-6">
            <SelectFavorecido v-model="filtros.favorecido_id" />
          </div>
          <div class="col-12">
            <q-btn label="Buscar" color="primary" type="submit" />
          </div>
        </div>
      </q-form>
    </div>

    <!-- Buscar por Jurídico -->
    <div v-else-if="tab === 'juridico'" class="q-mt-md">
      <q-form @submit.prevent="buscarPorJuridico">
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <SelectInput
              label="Notarios"
              dense
              outlined
              clearable
              v-model="filtros.notario_id"
              :options="notarios"
              :option-label="notario => notario.nombre_completo"
              option-value="id"
              :requerido="true"
            />
          </div>
          <div class="col-12">
            <q-btn label="Buscar" color="primary" type="submit" />
          </div>
        </div>
      </q-form>
    </div>

    <!-- Buscar por Fecha -->
    <div v-else-if="tab === 'fecha'" class="q-mt-md">
      <q-form @submit.prevent="buscarPorFecha">
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-4">
            <InputAnio v-model="filtros.anio" />
          </div>
          <div class="col-12 col-md-4">
            <InputMes v-model="filtros.mes" :modelAnio="filtros.anio" />
          </div>
          <div class="col-12 col-md-4">
            <InputDia v-model="filtros.dia" :modelAnio="filtros.anio" :modelMes="filtros.mes" />
          </div>
          <div class="col-12">
            <q-btn label="Buscar" color="primary" type="submit" />
          </div>
        </div>
      </q-form>
    </div>

    <!-- Buscar por Bien -->
    <div v-else-if="tab === 'bien'" class="q-mt-md">
      <q-form @submit.prevent="buscarPorBien">
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <InputNombreBien v-model="filtros.nombre_bien" />
          </div>
          <div class="col-12">
            <q-btn label="Buscar" color="primary" type="submit" />
          </div>
        </div>
      </q-form>
    </div>

    <q-separator class="q-my-md" />

    <!-- Tabla de resultados -->
    <q-table
      :rows="resultados"
      :columns="columns"
      row-key="id"
      flat
      bordered
      dense
      :loading="loading"
      no-data-label="No se encontraron resultados"
    >
      <template #body-cell-favorecidos="props">
        <q-td :props="props">
          <div v-html="props.value"></div>
        </q-td>
      </template>
      <template #body-cell-otorgantes="props">
        <q-td :props="props">
          <div v-html="props.value"></div>
        </q-td>
      </template>
    </q-table>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from "vue";
import InputAnio from 'src/components/InputAnio.vue'
import InputMes from 'src/components/InputMes.vue'
import InputDia from 'src/components/InputDia.vue'
import InputNombreBien from 'src/components/InputNombreBien.vue'
import SelectOtorgante from "src/components/SelectOtrogante.vue";
import SelectFavorecido from "src/components/SelectFavorecido.vue";
import SelectInput from "src/components/SelectInput.vue";
import EscrituraService from "src/services/EscrituraService";
import NotarioServive from "src/services/NotarioService";

const tab = ref('nombre');
const filtros = ref({
  otorgante_id: null,
  favorecido_id: null,
  notario_id: null,
  anio: null,
  mes: null,
  dia: null,
  nombre_bien: ''
});

const resultados = ref([]);
const loading = ref(false);
const notarios = ref([]);

// Columnas con lógica de EscrituraList.vue
const columns = [
  { name: "id", label: "ID", align: "center", field: row => row.id, sortable: true },
  { name: "bien", label: "Bien", align: "center", field: row => row.bien, sortable: true },
  { name: "cod_escritura", label: "Cod. Escritura", align: "center", field: row => row.cod_escritura, sortable: true },
  { name: "cod_folioInicial", label: "Folio Inicial", align: "center", field: row => row.cod_folioInicial, sortable: true },
  {
    name: "favorecidos",
    label: "Favorecidos",
    align: "center",
    field: row => {
      if (!Array.isArray(row.favorecidos)) return '';
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
      if (!Array.isArray(row.otorgantes)) return '';
      const list = row.otorgantes.map(o => o.nombre_completo);
      return list.length === 1 ? list[0] : list.join('<br>');
    },
    sortable: false,
    format: val => val,
  },
  { name: "fecha", label: "Fecha", align: "center", field: row => row.fecha, sortable: true },
  {
    name: "subserie",
    label: "Subserie",
    align: "center",
    field: row => row.sub_serie?.nombre || "Sin especificar",
    sortable: true
  },
  {
    name: "libro",
    label: "Libro",
    align: "center",
    field: row => row.libro?.protocolo || "Sin libro",
    sortable: true
  }
];

// Cargar notarios para el select
onMounted(async () => {
  try {
    const response = await NotarioServive.getData();
    notarios.value = Array.isArray(response.data) ? response.data : [];
  } catch (e) {
    notarios.value = [];
  }
});

// Buscar por Nombre (Otorgante/Favorecido)
async function buscarPorNombre() {
  loading.value = true;
  try {
    const params = {};
    if (filtros.value.otorgante_id) params.otorgante_id = filtros.value.otorgante_id.id;
    if (filtros.value.favorecido_id) params.favorecido_id = filtros.value.favorecido_id.id;
    const { data } = await EscrituraService.getData({ params });
    resultados.value = Array.isArray(data) ? data : (data?.rows || []);
  } finally {
    loading.value = false;
  }
}

// Buscar por Notario
async function buscarPorJuridico() {
  loading.value = true;
  try {
    const params = {};
    if (filtros.value.notario_id) params.notario_id = filtros.value.notario_id.id;
    const { data } = await EscrituraService.getData({ params });
    resultados.value = Array.isArray(data) ? data : (data?.rows || []);
  } finally {
    loading.value = false;
  }
}

// Buscar por Bien
async function buscarPorBien() {
  loading.value = true;
  try {
    const params = {};
    if (filtros.value.nombre_bien) params.bien = filtros.value.nombre_bien;
    const { data } = await EscrituraService.getData({ params });
    resultados.value = Array.isArray(data) ? data : (data?.rows || []);
  } finally {
    loading.value = false;
  }
}

// Buscar por Fecha
async function buscarPorFecha() {
  loading.value = true;
  try {
    const params = {};
    if (filtros.value.anio) params.anio = filtros.value.anio;
    if (filtros.value.mes) params.mes = filtros.value.mes;
    if (filtros.value.dia) params.dia = filtros.value.dia;
    const { data } = await EscrituraService.getData({ params });
    resultados.value = Array.isArray(data) ? data : (data?.rows || []);
  } finally {
    loading.value = false;
  }
}
</script>
