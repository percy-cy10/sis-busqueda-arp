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

            <div class="col-12">
              <div class="row q-col-gutter-md">

                <!-- Grupo Subserie -->
                <div class="col-6">
                  <div class="row items-start q-gutter-sm">
                    <q-select
                      class="col"
                      v-model="form.subserie_id"
                      :options="subseries"
                      label="Subserie *"
                      :error="!!form.errors.subserie_id"
                      option-label="nombre"
                      option-value="id"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                    <q-btn
                      class="btn-alto"
                      dense
                      icon="person_add"
                      color="primary"
                      @click="abrirFormulario('SubSerie')"
                      size="md"
                    />
                  </div>
                </div>

                <!-- Grupo Libro -->
                <div class="col-6">
                  <div class="row items-start q-gutter-sm">
                    <q-select
                      class="col"
                      v-model="form.libro_id"
                      :options="libros"
                      label="Libro *"
                      :error="!!form.errors.libro_id"
                      option-label="protocolo"
                      option-value="id"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </div>
                </div>

              </div>
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

        <!-- Sección de Otorgante y Favorecido -->
        <q-separator color="primary" class="q-my-s" />
        <q-badge class="q-mb-sm text-subtitle2">Datos de Otorgante y Favorecido</q-badge>



        <!-- <div class="col-12">
          <div class="row q-col-gutter-md">

            <div class="col-6">
              <div class="row items-stretch q-gutter-sm">
                <q-select
                  class="col"
                  v-model="form.otorgante_id"
                  :options="otorgantesOptions"
                  label="Otorgante *"
                  option-label="nombre_completo"
                  option-value="id"
                  dense
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Seleccione un otorgante']"
                />
                <q-btn
                  class="boton-expandido"
                  dense
                  icon="person_add"
                  color="primary"
                  @click="abrirFormulario('Otorgante')"
                  size="md"
                  flat
                />
              </div>
            </div>


            <div class="col-6">
              <div class="row items-stretch q-gutter-sm">
                <q-select
                  class="col"
                  v-model="form.favorecido_id"
                  :options="favorecidosOptions"
                  label="Favorecido *"
                  option-label="nombre_completo"
                  option-value="id"
                  dense
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Seleccione un favorecido']"
                />
                <q-btn
                  class="boton-expandido"
                  dense
                  icon="person_add"
                  color="primary"
                  @click="abrirFormulario('Favorecido')"
                  size="md"
                  flat
                />
              </div>
            </div>

          </div>
        </div> -->

        <!-- Grupo Otorgante -->
        <div class="col-6">
          <div class="row items-start q-gutter-sm">
            <q-select
              class="col"
              v-model="form.otorgantes"
              :options="otorgantesOptions"
              label="Otorgantes *"
              option-label="nombre_completo"
              option-value="id"
              dense
              outlined
              emit-value
              map-options
              multiple
              :rules="[val => val && val.length > 0 || 'Seleccione al menos un otorgante']"
            />
            <q-btn
              class="btn-alto"
              dense
              icon="person_add"
              color="primary"
              @click="abrirFormulario('Otorgante')"
              size="md"
            />
          </div>
        </div>

        <!-- Grupo Favorecido -->
        <div class="col-6">
          <div class="row items-start q-gutter-sm">
            <q-select
              class="col"
              v-model="form.favorecidos"
              :options="favorecidosOptions"
              label="Favorecidos *"
              option-label="nombre_completo"
              option-value="id"
              dense
              outlined
              emit-value
              map-options
              multiple
              :rules="[val => val && val.length > 0 || 'Seleccione al menos un favorecido']"
            />
            <q-btn
              class="btn-alto"
              dense
              icon="person_add"
              color="primary"
              @click="abrirFormulario('Favorecido')"
              size="md"
            />
          </div>
        </div>



        <!-- <div class="col-12">
          <div class="row q-col-gutter-md">


            <div class="col-6">
              <div class="row items-start q-gutter-sm">
                <q-select
                  class="col"
                  v-model="form.otorgante_id"
                  :options="otorgantesOptions"
                  label="Otorgante *"
                  option-label="nombre_completo"
                  option-value="id"
                  dense
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Seleccione un otorgante']"
                />
                <q-btn
                  class="btn-alto"
                  dense
                  icon="person_add"
                  color="primary"
                  @click="abrirFormulario('Otorgante')"
                  size="md"
                />
              </div>
            </div>


            <div class="col-6">
              <div class="row items-start q-gutter-sm">
                <q-select
                  class="col"
                  v-model="form.favorecido_id"
                  :options="favorecidosOptions"
                  label="Favorecido *"
                  option-label="nombre_completo"
                  option-value="id"
                  dense
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Seleccione un favorecido']"
                />
                <q-btn
                  class="btn-alto"
                  dense
                  icon="person_add"
                  color="primary"
                  @click="abrirFormulario('Favorecido')"
                  size="md"
                />
              </div>
            </div>

          </div>
        </div> -->



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

// Importaciones adicionales
import OtorganteForm from 'src/pages/Admin/Otorgantes/OtorganteForm.vue';
import FavorecidoForm from 'src/pages/Admin/Favorecidos/FavorecidoForm.vue';
import OtorganteService from 'src/services/OtorganteService';
import FavorecidoService from 'src/services/FavorecidoService';

import SelectLibro from 'src/components/SelectLibro.vue'
import SelectSubSerie from 'src/components/SelectSubSerie.vue'
import SelectFavorecido from 'src/components/SelectFavorecido.vue'
import SelectOtrogante from 'src/components/SelectOtrogante.vue'

// Nuevas variables y métodos
const otorgantesOptions = ref([]);
const favorecidosOptions = ref([]);


// const emits = defineEmits(['save']);
// const props = defineProps({ title: String, id: Number, edit: { type: Boolean, default: false } });
const $q = useQuasar();
const emits = defineEmits(['save']);
const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false }
});


// Métodos para manejar los formularios
const abrirFormulario = (tipo) => {
  tipoFormulario.value = tipo;
  dialog.value = true;
};


const handleSave = () => {
  dialog.value = false;
  actualizarListados();
};


const actualizarListados = async () => {
  try {
    const [otorgantesRes, favorecidosRes] = await Promise.all([
      OtorganteService.getData(),
      FavorecidoService.getData()
    ]);

        otorgantesOptions.value = otorgantesRes.data || []; // Accede a response.data
    favorecidosOptions.value = favorecidosRes.data || []; // Accede a response.data
  } catch (error) {
    console.error("Error actualizando listados:", error);
    $q.notify({ type: 'negative', message: 'Error actualizando registros' });
  }
};


// Funciones de búsqueda mejoradas
const buscarOtorgantes = async (val, update) => {
  if (val === '') {
    update(() => {
      otorgantesOptions.value = otorgantesOptions.value.slice(0, 10);
    });
    return;
  }

  try {
    const resultados = await OtorganteService.buscar(val);
    update(() => {
      otorgantesOptions.value = resultados.data;
    });
  } catch (error) {
    console.error("Error buscando otorgantes:", error);
  }
};

const buscarFavorecidos = async (val, update) => {
  if (val === '') {
    update(() => {
      favorecidosOptions.value = favorecidosOptions.value.slice(0, 10);
    });
    return;
  }

  try {
    const resultados = await FavorecidoService.buscar(val);
    update(() => {
      favorecidosOptions.value = resultados.data;
    });
  } catch (error) {
    console.error("Error buscando favorecidos:", error);
  }
};


// const $q = useQuasar();
const dialog = ref(false);
const tipoFormulario = ref('');


const form = useForm('post', '/api/escrituras', {
  subserie_id: null,
  libro_id: null,
  otorgantes: [], // Array para múltiples otorgantes
  favorecidos: [],
  bien: '',
  anio: '',
  mes: '',
  dia: '',
  fecha: '',
  cod_folioInicial: '',
  cod_escritura: '',
  cod_folioFinal: '',
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

  await actualizarListados();

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
  max-width: 1000px;
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

.btn-alto {
  height: 40px;
  width: auto;
  align-self: stretch;
  padding: 0 12px;
  justify-content: center;
}


.boton-expandido {
  height: 40px;
  width: auto;
  align-self: stretch;
  padding: 0 12px;
  justify-content: center;

}
</style>
