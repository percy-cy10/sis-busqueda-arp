<template>
  <!-- ========================== -->
  <!-- CAMPOS PARA EXPEDIENTES -->
  <!-- ========================== -->
  <template v-if="solicitudForm.tipo_tramite === 'expedientes'">
    <!-- TIPO DE EXPEDIENTE -->
    <div class="col-12 q-pa-sm">
      <div class="text-subtitle2 q-mb-sm">
        Tipo de Expediente <span class="text-red-7">(*)</span>
      </div>

      <!-- BOTONES DE TIPOS DE EXPEDIENTE -->
      <div class="row q-col-gutter-sm q-mb-md">
        <div
          class="col-12 col-sm-6"
          v-for="tipo in tiposExpediente"
          :key="tipo.value"
        >
          <q-card
            class="tipo-expediente-card cursor-pointer text-center q-pa-md"
            :class="{
              'tipo-expediente-selected':
                solicitudForm.tipo_expediente === tipo.value,
              'tipo-expediente-default':
                solicitudForm.tipo_expediente !== tipo.value,
            }"
            flat
            bordered
            @click="seleccionarTipoExpediente(tipo.value)"
          >
            <q-card-section class="q-pa-sm">
              <q-icon
                :name="tipo.icon"
                size="40px"
                :color="
                  solicitudForm.tipo_expediente === tipo.value
                    ? 'white'
                    : tipo.color
                "
                class="q-mb-sm"
              />
              <div
                class="text-subtitle2 text-weight-medium"
                :class="
                  solicitudForm.tipo_expediente === tipo.value
                    ? 'text-white'
                    : 'text-grey-8'
                "
              >
                {{ tipo.label }}
              </div>
            </q-card-section>

            <q-badge
              v-if="solicitudForm.tipo_expediente === tipo.value"
              color="white"
              text-color="primary"
              label="✓ Seleccionado"
              class="absolute-top-right q-ma-xs"
            />
          </q-card>
        </div>
      </div>

      <div v-if="errorTipoExpediente" class="text-negative q-mt-sm">
        ⚠ Por favor seleccione un tipo de expediente
      </div>
    </div>

    <!-- ========================== -->
    <!-- CAMPOS DINÁMICOS -->
    <!-- ========================== -->
    <div class="col-12 row" v-if="solicitudForm.tipo_expediente">
      <!-- MATERIA DEL PROCESO -->
      <div class="col-12 q-pa-sm">
        <div class="text-subtitle2 q-mb-sm">
          Materia del Proceso <span class="text-red-7">(*)</span>
        </div>

        <q-select
          ref="materiaSelect"
          class="col-12"
          dense
          outlined
          clearable
          use-input
          fill-input
          hide-selected
          input-debounce="0"
          v-model="solicitudForm.materia_proceso"
          :options="materiasFiltradas"
          @filter="filtrarMaterias"
          @input-value="setInputValue"
          placeholder="Seleccione o escriba la materia del proceso..."
          :rules="[
            (val) => (val && val !== '') || 'Ingrese la materia del proceso',
          ]"
        >
          <template v-slot:no-option>
            <q-item>
              <q-item-section class="text-grey">
                Escriba para agregar una nueva materia
              </q-item-section>
            </q-item>
          </template>


        </q-select>
      </div>

      <!-- DEMANDANTE -->
      <q-input
        class="col-12 col-md-6 q-pa-sm"
        label="Demandante"
        dense
        outlined
        clearable
        v-model="solicitudForm.demandante"
        :rules="[
          (val) =>
            (val && val.trim() !== '') || 'Ingrese el nombre del demandante',
        ]"
      />

      <!-- DEMANDADO -->
      <q-input
        class="col-12 col-md-6 q-pa-sm"
        label="Demandado"
        dense
        outlined
        clearable
        v-model="solicitudForm.demandado"
        :rules="[
          (val) =>
            (val && val.trim() !== '') || 'Ingrese el nombre del demandado',
        ]"
      />

      <!-- CAUSANTE -->
      <q-input
        class="col-12 col-md-6 q-pa-sm"
        label="Causante"
        dense
        outlined
        clearable
        v-model="solicitudForm.causante"
        placeholder="Opcional - Ingrese el nombre del causante si aplica"
      />

      <!-- JUZGADO -->
      <q-input
        class="col-12 col-md-6 q-pa-sm"
        label="Juzgado"
        dense
        outlined
        clearable
        v-model="solicitudForm.juzgado"
        :rules="[
          (val) =>
            (val && val.trim() !== '') || 'Ingrese el nombre del juzgado',
        ]"
      />

      <!-- SECRETARIO -->
      <q-input
        class="col-12 col-md-6 q-pa-sm"
        label="Secretario"
        dense
        outlined
        clearable
        v-model="solicitudForm.secretario"
        placeholder="Opcional - Ingrese el nombre del secretario si aplica"
      />

      <!-- ===================== -->
      <!-- UBICACIÓN Y FECHA -->
      <!-- ===================== -->
      <div class="row q-col-gutter-sm q-mb-xs">
        <!-- Ubicación del Contrato -->
        <div class="col-12 col-md-8">
          <div class="text-caption q-pb-xs">
            Lugar del Contrato <span class="text-red-7">(*)</span>
          </div>
          <div class="row q-col-gutter-xs">
            <SelectUbigeoPlus
              class="col-3"
              dense
              outlined
              clearable
              v-model="solicitudForm.ubigeo_cod_soli"
              :loading="loading"
              :requerido="true"
            />
          </div>
        </div>

        <!-- Fecha del Contrato -->
        <div class="col-12 col-md-4">
          <div class="text-caption q-pb-xs">
            Fecha del Contrato <span class="text-red-7">(*)</span>
          </div>
          <div class="row q-col-gutter-xs">
            <InputAnio
              class="col-4"
              dense
              outlined
              v-model="solicitudForm.anio"
              :RangoAnios="[1900, new Date().getFullYear()]"
              :requerido="true"
              :rules="[
                (val) => (val !== null && val !== '') || 'Ingrese el año',
              ]"
            />
            <InputMes
              class="col-4"
              dense
              outlined
              clearable
              v-model="solicitudForm.mes"
              :modelAnio="solicitudForm.anio"
              :readonly="solicitudForm.anio === null"
              :rules="[
                (val) => (val !== null && val !== '') || 'Ingrese el mes',
              ]"
            />
            <InputDia
              class="col-4"
              dense
              outlined
              v-model="solicitudForm.dia"
              :modelAnio="solicitudForm.anio"
              :modelMes="solicitudForm.mes"
              :readonly="
                solicitudForm.anio === null || solicitudForm.mes === null
              "
              :rules="[
                (val) => (val !== null && val !== '') || 'Ingrese el día',
              ]"
            />
          </div>
        </div>
      </div>

      <!-- OBSERVACIONES -->
      <q-input
        class="col-12 q-pa-sm"
        label="Observaciones"
        dense
        outlined
        clearable
        type="textarea"
        rows="3"
        v-model="solicitudForm.mas_datos"
        placeholder="Describa las observaciones relevantes del expediente..."
      />
    </div>
  </template>
</template>

<script setup>
import { ref, watch, nextTick } from "vue";
import SelectUbigeoPlus from "src/components/SelectUbigeoPlus.vue";
import InputAnio from "src/components/InputAnio.vue";
import InputMes from "src/components/InputMes.vue";
import InputDia from "src/components/InputDia.vue";

// ==============================
// TIPOS DE EXPEDIENTE
// ==============================
const tiposExpediente = [
  { label: "CIVIL", value: "civil", icon: "balance", color: "blue" },
  { label: "PENAL", value: "penal", icon: "gavel", color: "red" },
];

// ==============================
// PROPS
// ==============================
const props = defineProps({
  solicitudForm: Object,
  errorTipoExpediente: Boolean,
});

// ==============================
// REFS
// ==============================
const materiaSelect = ref(null);

// ==============================
// LISTAS DE MATERIAS
// ==============================
const materiasCivil = ref([
  "INSCRIPCIÓN DE PARTIDAS DE NACIMIENTO",
  "INSCRIPCIÓN DE PARTIDAS DE DEFUNCIÓN",
  "DESALOJO",
  "ESCRITURAS IMPERFECTAS DE COMPRA Y VENTA",
  "INVENTARIO DE BIENES",
  "EXPROPIACIÓN DE PREDIOS",
]);

const materiasPenal = ref([
  "HOMICIDIO",
  "LESIONES",
  "ROBO",
  "HURTO",
  "ESTAFA",
]);

const materiasFiltradas = ref([]);
const inputValue = ref("");

// ==============================
// CAMBIO DE TIPO DE EXPEDIENTE
// ==============================
watch(
  () => props.solicitudForm.tipo_expediente,
  (nuevoTipo) => {
    materiasFiltradas.value =
      nuevoTipo === "civil" ? [...materiasCivil.value] : [...materiasPenal.value];
    props.solicitudForm.materia_proceso = "";
    inputValue.value = "";
  },
  { immediate: true }
);

// ==============================
// FILTRADO DE MATERIAS
// ==============================
function filtrarMaterias(val, update) {
  update(() => {
    const tipo = props.solicitudForm.tipo_expediente;
    const base = tipo === "civil" ? materiasCivil.value : materiasPenal.value;

    if (!val) {
      materiasFiltradas.value = [...base];
    } else {
      const filtro = val.toUpperCase();
      materiasFiltradas.value = base.filter((m) =>
        m.toUpperCase().includes(filtro)
      );
    }
  });
}

// ==============================
// ESTABLECER VALOR DE ENTRADA
// ==============================
function setInputValue(val) {
  inputValue.value = val;
  // Si el usuario escribe y presiona Enter o sale del campo, actualizar el valor
  if (val && val.trim() !== '') {
    props.solicitudForm.materia_proceso = val.toUpperCase().trim();
  }
}

// ==============================
// EDITAR MATERIA ACTUAL
// ==============================
function editarMateriaActual() {
  if (materiaSelect.value && props.solicitudForm.materia_proceso) {
    // Poner el foco en el input y seleccionar el texto para edición
    nextTick(() => {
      const input = materiaSelect.value.$el.querySelector('input');
      if (input) {
        input.focus();
        input.select();
      }
    });
  }
}

// ==============================
// ELIMINAR MATERIA ACTUAL
// ==============================
function eliminarMateriaActual() {
  props.solicitudForm.materia_proceso = "";
  inputValue.value = "";
}

// ==============================
// SELECCIONAR TIPO DE EXPEDIENTE
// ==============================
function seleccionarTipoExpediente(tipo) {
  props.solicitudForm.tipo_expediente = tipo;
  props.solicitudForm.materia_proceso = "";
  inputValue.value = "";
}
</script>

<style scoped>
.tipo-expediente-card {
  border-radius: 12px;
  transition: all 0.3s ease;
}
.tipo-expediente-selected {
  background-color: var(--q-primary);
  color: white;
  box-shadow: 0 0 12px rgba(0, 0, 0, 0.2);
}
.tipo-expediente-default:hover {
  background-color: rgba(0, 0, 0, 0.05);
}
</style>
