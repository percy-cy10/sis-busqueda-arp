<template>
  <q-card class="my-card">
    <!-- Título -->
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">{{ title }}</div>
    </q-card-section>

    <q-form @submit.prevent="submit">
      <q-card-section class="q-pa-md">

        <div class="row">
          <div class="col 2">

            <q-separator color="primary" class="q-my-s" />
            <q-badge class="q-mb-sm text-subtitle2">Bien y codifo de escritura</q-badge>

            <div class="input-group">
              <!-- Bien y codifo de escritura-->
              <q-input dense outlined v-model="form.bien" label="Bien *" :error="!!form.errors.bien" class=" input-third" />
              <div v-if="form.errors.bien" class="text-negative text-caption">{{ form.errors.bien }}</div>

              <q-input dense outlined v-model="form.cod_escritura" label="Codigo de Escritura" mask="E-######" :error="!!form.errors.cod_escritura" class=" input-third" />
              <div v-if="form.errors.cod_escritura" class="text-negative text-caption">{{ form.errors.cod_escritura }}</div>

            </div>

            <q-separator color="primary" class="q-my-s" />
            <q-badge class="q-mb-sm text-subtitle2">otros datos de la escritura</q-badge>


            <div class="input-group">
              <q-select



                class="input-half"
                v-model="form.subserie_id"
                :options="subseries"
                label="Subserie *"
                :error="!!form.errors.subserie_id"
                option-label="nombre"
                option-value="id"
                emit-value
                map-options
              />
              <q-select


              class="input-half"
              v-model="form.libro_id"
              :options="libros"
              label="Libro *"
              :error="!!form.errors.libro_id"
              option-label="protocolo"
              option-value="id"
              emit-value
              map-options
              />
            </div>

          </div>

          <div class="col 2">
            <q-separator color="primary" class="q-my-s" />
            <q-badge class="q-mb-sm text-subtitle2">Fecha</q-badge>


            <!-- Nuevo bloque de fecha con componentes personalizados -->
            <div class="col-10 col-md-5 q-pa-sm row input-group">
              <InputAnio :requerido="true" class="col-12 col-sm-4" dense outlined
                v-model="form.anio" :error="!!form.errors.anio"/>

              <InputMes :readonly="!form.anio" class="col-12 col-sm-4" dense outlined
                v-model="form.mes" :modelAnio="form.anio" :error="!!form.errors.mes"/>

              <InputDia :readonly="!form.anio || !form.mes" class="col-12 col-sm-3" dense outlined
                v-model="form.dia" v-model:modelAnio="form.anio" v-model:modelMes="form.mes"
                :error="!!form.errors.dia" />

              <!-- <InputDia
                :readonly="!form.anio || !form.mes"
                class="col-12 col-sm-3"
                dense
                outlined
                v-model="form.dia"
                v-model:modelAnio="form.anio"
                v-model:modelMes="form.mes"
                :error="!!form.errors.dia"
              /> -->
            </div>

            <q-separator color="primary" class="q-my-s" />
            <q-badge class="q-mb-sm text-subtitle2">Datos de Folios</q-badge>

            <!-- Folio Inicial y Final con Vuelvas -->

            <div class="input-group">

              <q-input class="input-half" :class="form.invalid('cod_folioInicial') ? 'q-mb-sm' : ''" dense outlined
                v-model="form.cod_folioInicial" label="Codigo Folio Inicial" mask="F-######" :loading="form.validating"
                @change="form.validate('cod_folioInicial')" :error="form.invalid('cod_folioInicial')">
                <template v-slot:label> Folio Inicial <span class="text-red-7 text-weight-bold">(*)</span></template>
                <template v-slot:append>
                  <q-toggle v-model="form.vueltaFI" size="xm" dense checked-icon="check" color="blue" unchecked-icon="clear" :disable="deshabili_FI" />
                  <div class="text-caption">Vuelta</div>
                </template>
                <template v-slot:error> {{ form.errors.cod_folioInicial }} </template>
              </q-input>

              <q-input class=" input-half" :class="form.invalid('cod_folioFinal') ? 'q-mb-sm' : ''" dense outlined
                v-model="form.cod_folioFinal" label="Codigo Folio Final" mask="F-######" :loading="form.validating"
                @change="form.validate('cod_folioFinal')" :error="form.invalid('cod_folioFinal')">
                <template v-slot:label> Folio Final <span class="text-red-7 text-weight-bold">(*)</span></template>
                <template v-slot:append>
                  <q-toggle v-model="form.vueltaFF" size="xm" dense checked-icon="check" color="blue" unchecked-icon="clear" :disable="deshabili_FF" />
                  <div class="text-caption">Vuelta</div>
                </template>
                <template v-slot:error> {{ form.errors.cod_folioFinal }} </template>
              </q-input>
            </div>

          </div>


        </div>

        <q-separator color="primary" class="q-my-s" />
        <q-badge class="q-mb-sm text-subtitle2">datos de otrosgante y favorecido</q-badge>



        <!-- Campo para Otorgante y Favorecido con botones de agregar -->
        <div class="row q-gutter-md q-mb-sm">
          <!-- Otorgante - Proporción 7/12 -->
          <div class="col-12 col-md-6">
            <div class="row items-center no-wrap q-gutter-xs">
              <q-input
                dense
                outlined
                v-model="form.otorgante"
                label="Otorgante *"
                :error="!!form.errors.otorgante"
                class="col"
              />
              <q-btn
                dense
                color="primary"
                icon="add"
                size="md"
                :label="$q.screen.lt.sm ? '' : 'Agregar'"
                :loading="loading"
                @click="abrirFormulario('otorgante')"
                class="self-center"
              >
                <q-tooltip>Agregar Otorgante</q-tooltip>
              </q-btn>
            </div>
            <div v-if="form.errors.otorgante" class="text-negative text-caption q-pl-xs">
              {{ form.errors.otorgante }}
            </div>
          </div>

          <!-- Favorecido - Proporción 5/12 -->
          <div class="col-12 col-md-5">
            <div class="row items-center no-wrap q-gutter-xs">
              <q-input
                dense
                outlined
                v-model="form.favorecido"
                label="Favorecido *"
                :error="!!form.errors.favorecido"
                class="col"
              />
              <q-btn
                dense
                color="primary"
                :label="$q.screen.lt.sm ? '' : 'Agregar'"
                icon="add"
                size="md"
                :loading="loading"
                @click="abrirFormulario('favorecido')"
                class="self-center"
              >
                <q-tooltip>Agregar Favorecido</q-tooltip>
              </q-btn>
            </div>
            <div v-if="form.errors.favorecido" class="text-negative text-caption q-pl-xs">
              {{ form.errors.favorecido }}
            </div>
          </div>
        </div>


        <q-separator color="primary" class="q-my-s" />
        <q-badge class="q-mb-sm text-subtitle2">Observaciones</q-badge>

        <q-input dense outlined v-model="form.observaciones" label="Observaciones" type="textarea" class="q-mb-s" />
      </q-card-section>

      <!-- Botones de Acción -->
      <q-card-actions align="right">
        <q-btn label="Cancelar" flat v-close-popup />
        <q-btn label="Guardar" :loading="form.processing" type="submit" color="positive" />
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script setup>

import { ref, watch, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { useForm } from "laravel-precognition-vue";
import SubSerieService from 'src/services/SubSerieService.js';
import LibroService from 'src/services/LibroService.js';
import EscrituraService from 'src/services/EscrituraService.js';
import InputAnio from 'src/components/InputAnio.vue';
import InputMes from 'src/components/InputMes.vue';
import InputDia from 'src/components/InputDia.vue';

// const emits = defineEmits(['save']);
// const props = defineProps({ title: String, id: Number, edit: { type: Boolean, default: false } });
const $q = useQuasar();
const emits = defineEmits(['save']);
const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false }
});


const form = useForm(props.edit ? 'put' : 'post',
  props.edit ? `/api/escrituras/${props.id}` : '/api/escrituras', {
    bien: '',
    subserie_id: null,
    anio: '',
    mes: '',
    dia: '',
    fecha: '',
    cod_folioInicial: '',
    cod_escritura: '',
    cod_folioFinal: '',
    libro_id: null,
    observaciones: '',
    vueltaFI: false,
    vueltaFF: false,
});


// const subseries = ref([]);
// const libros = ref([]);

const subseries = ref([]);
const libros = ref([]);
const deshabili_FI = ref(form.cod_folioInicial === '');
const deshabili_FF = ref(form.cod_folioFinal === '');
// const deshabili_FI = ref(form.cod_folioInicial === '');
// const deshabili_FF = ref(form.cod_folioFinal === '');


// Cargar Subseries y Libros

const loadSubseriesAndLibros = async () => {
  try {
    // Carga con validación de estructura
    const [subseriesRes, librosRes] = await Promise.all([
      SubSerieService.getData(),
      LibroService.getData()
    ]);

    subseries.value = subseriesRes.data?.data || subseriesRes.data || [];
    libros.value = librosRes.data?.data || librosRes.data || [];

  } catch (error) {
    console.error("Error cargando datos:", error);
  }
};


onMounted(async () => {
  console.log("onMounted: Iniciando carga de subseries y libros...");
  await loadSubseriesAndLibros();

  if (props.edit) {
    console.log("onMounted: Modo edición activado, llamando a EscrituraService.get con id =", props.id);

    try {
      const response = await EscrituraService.get(props.id);
      console.log("onMounted: Respuesta completa de API:", response);

      if (!response) {
        throw new Error("Datos no disponibles");
      }

      console.log("onMounted: Datos obtenidos y asignando al formulario...");
      form.setData({
        ...response,
        anio: response.anio ? response.anio.toString() : "",
        mes: response.mes ? Number(response.mes) : 1,
        dia: response.dia ? Number(response.dia) : 1,
        // dia: response.dia ? Number(response.dia) : 1, //
        // dia: response.dia !== undefined && response.dia !== null ? Number(response.dia) : 1, //



      });

      // Configurar toggles para folios según si terminan en " V"
      form.vueltaFI = (response.cod_folioInicial || '').trim().endsWith("V");
      deshabili_FI.value = !form.vueltaFI;

      form.vueltaFF = (response.cod_folioFinal || '').trim().endsWith("V");
      deshabili_FF.value = !form.vueltaFF;

      console.log("onMounted: Datos cargados correctamente en el formulario.");

    } catch (error) {
      console.error("onMounted: Error cargando datos:", error);
      $q.notify({ type: 'negative', message: 'Error al cargar la escritura' });
    }
  }
});






watch(() => form.cod_folioInicial, (newVal) => {
  if (newVal === '') {
    form.vueltaFI = false;
    deshabili_FI.value = true;
  } else {
    deshabili_FI.value = false;
  }
});

// watch(
//   () => [form.anio, form.mes, form.dia],
//   ([newAnio, newMes, newDia]) => {
//     if (newAnio && newMes && newDia) {
//       form.fecha = `${newAnio}-${String(newMes).padStart(2, "0")}-${String(newDia).padStart(2, "0")}`;
//     }
//   },
//   { immediate: true, deep: true } // <-- Activar deep
// );

watch(
  () => [form.anio, form.mes, form.dia],
  ([newAnio, newMes, newDia]) => {
    if (newAnio && newMes && newDia) {
      form.fecha = `${newAnio}-${String(newMes).padStart(2, "0")}-${String(newDia).padStart(2, "0")}`;
    }
  },
  { immediate: true, deep: true }
);

watch(() => form.cod_folioFinal, (newVal) => {
  if (newVal === '') {
    form.vueltaFF = false;
    deshabili_FF.value = true;
  } else {
    deshabili_FF.value = false;
  }
});


const submit = () => {
  // Limpiar "V" antes de añadirla
  if (form.vueltaFI) {
    form.cod_folioInicial = form.cod_folioInicial.replace(/ V$/, '') + ' V';
  }
  if (form.vueltaFF) {
    form.cod_folioFinal = form.cod_folioFinal.replace(/ V$/, '') + ' V';
  }

  form.submit()
    .then(() => {
      emits('save');
      form.reset();
    })
    .catch(error => {
      console.error('Submission error:', error);
    });
};

onMounted(loadSubseriesAndLibros);

defineExpose({
  form // Expone la instancia del formulario
});

</script>

<style scoped>
.my-card {
  width: 100%;
  max-width: 900px;
}
.input-group {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}
.input-half {
  flex: 1 1 48%;
}
.input-third {
  flex: 1 1 30%;
}
</style>
