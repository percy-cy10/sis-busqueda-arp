<template>
  <!-- content -->
  <q-card class="my-card" style="width: 1400px; max-width: 80vw">
    <q-card-section class="bg-primary text-white row">
      <div class="text-h6 row items-center">{{ title }}</div>
      <q-space />
      <q-btn icon="close" color="negative" round v-close-popup />
    </q-card-section>
    <q-form @submit.prevent="submit">
      <div class="row">
        <q-card-section class="col-12 col-md-6 row q-pa-md">
          <q-btn-toggle
            v-model="opcionVista"
            spread
            no-caps
            toggle-color="primary"
            color="white"
            text-color="grey"
            class="col-12"
            :options="[
              { label: 'Datos', value: 1 },
              { label: 'Ver en PDF', value: 2 },
            ]"
          />
          <div v-if="opcionVista === 1" class="full-width row">
            <template
              v-for="item in [
                {
                  label: 'SOLICITANTE',
                  value:
                    D_solicitud?.solicitante?.tipo_documento === 'DNI'
                      ? 'Nombres:'
                      : 'Asunto:',
                  data:
                    D_solicitud?.solicitante?.tipo_documento === 'DNI'
                      ? D_solicitud?.solicitante?.nombre_completo
                      : D_solicitud?.solicitante?.asunto,
                },
                {
                  label: 'DATOS DEL DOCUMENTO',
                  value: 'Escritura Pública:',
                  data: D_solicitud?.sub_serie?.nombre || '',
                },
                {
                  label: null,
                  value: 'Notario:',
                  data: D_solicitud?.notario?.nombre_completo || '',
                },
                {
                  label: null,
                  value: 'Otorgantes:',
                  data: D_solicitud?.otorgantes || '',
                },
                {
                  label: null,
                  value: 'Favorecidos:',
                  data: D_solicitud?.favorecidos || '',
                },
                {
                  label: null,
                  value: 'Lugar y Fecha:',
                  data: `${D_solicitud?.ubigeo?.nombre || ''}, Año:${
                    D_solicitud?.anio || ''
                  } Mes:${D_solicitud?.mes || ''} Día:${
                    D_solicitud?.dia || ''
                  }`,
                },
                { label: null, value: 'Bien:', data: D_solicitud?.bien || '' },
                {
                  label: null,
                  value: 'Otros Datos:',
                  data: D_solicitud?.mas_datos || '',
                },
              ]"
              :key="item.value"
            >
              <div
                v-if="item.label"
                class="col-12 q-py-sm text-weight-bold text-subtitle2"
              >
                {{ item.label }}
              </div>
              <div
                class="col-12 col-sm-3 items-center row q-py-sm q-pl-sm text-weight-bold"
              >
                {{ item.value }}
              </div>
              <div class="col-12 col-sm-9 items-center row q-py-sm q-pl-sm">
                {{ item.data }}
              </div>
            </template>
          </div>
          <div v-else class="full-width">
            <GenerarPDFSolicitud
              :datosSolicitudRow="D_solicitud"
              :datosBusqueda="D_busqueda"
              :ver="true"
              height="450px"
            />
          </div>
        </q-card-section>
        <q-card-section class="col-12 col-md-6 q-pa-md">
          <div class="row q-mb-md">
            <q-input
              class="col-12 col-sm-6 q-pa-sm"
              :class="form.invalid('cod_protocolo') ? 'q-mb-sm' : ''"
              dense
              outlined
              v-model="form.cod_protocolo"
              label="Protocolo"
              mask="P-######"
              :loading="form.validating"
              @change="form.validate('cod_protocolo')"
              :error="form.invalid('cod_protocolo')"
            >
              <template v-slot:label>
                Protocolo
                <span class="text-red-7 text-weight-bold">(*)</span></template
              >
              <template v-slot:error>
                {{ form.errors.cod_protocolo }}
              </template>
            </q-input>
            <q-input
              class="col-12 col-sm-6 q-pa-sm"
              :class="form.invalid('cod_escritura') ? 'q-mb-sm' : ''"
              dense
              outlined
              v-model="form.cod_escritura"
              label="Codigo de Escritura"
              mask="E-######"
              :loading="form.validating"
              @change="form.validate('cod_escritura')"
              :error="form.invalid('cod_escritura')"
            >
              <template v-slot:label>
                Codigo de Escritura
                <span class="text-red-7 text-weight-bold">(*)</span></template
              >
              <template v-slot:error>{{ form.errors.cod_escritura }}</template>
            </q-input>
            <q-input
              class="col-12 col-sm-6 q-pa-sm"
              :class="form.invalid('cod_folioInicial') ? 'q-mb-sm' : ''"
              dense
              outlined
              v-model="form.cod_folioInicial"
              label="Codigo Folio Inicial"
              mask="F-######"
              :loading="form.validating"
              @change="form.validate('cod_folioInicial')"
              :error="form.invalid('cod_folioInicial')"
            >
              <template v-slot:label>
                Folio Inicial
                <span class="text-red-7 text-weight-bold">(*)</span></template
              >
              <template v-slot:append>
                <q-toggle
                  v-model="vueltaFI"
                  size="xm"
                  dense
                  checked-icon="check"
                  color="blue"
                  unchecked-icon="clear"
                  :disable="deshabili_FI"
                />
                <div class="text-caption">Vuelta</div>
              </template>
              <template v-slot:error>
                {{ form.errors.cod_folioInicial }}
              </template>
            </q-input>
            <q-input
              class="col-12 col-sm-6 q-pa-sm"
              :class="form.invalid('cod_folioFinal') ? 'q-mb-sm' : ''"
              dense
              outlined
              v-model="form.cod_folioFinal"
              label="Codigo Folio Final"
              mask="F-######"
              :loading="form.validating"
              @change="form.validate('cod_folioFinal')"
              :error="form.invalid('cod_folioFinal')"
            >
              <template v-slot:label>
                Folio Final
                <span class="text-red-7 text-weight-bold">(*)</span></template
              >
              <template v-slot:append>
                <q-toggle
                  v-model="vueltaFF"
                  size="xm"
                  dense
                  checked-icon="check"
                  color="blue"
                  unchecked-icon="clear"
                  :disable="deshabili_FF"
                />
                <div class="text-caption">Vuelta</div>
              </template>
              <template v-slot:error>
                {{ form.errors.cod_folioFinal }}
              </template>
            </q-input>
            <q-input
              class="col-12 q-pa-sm"
              :class="form.invalid('observaciones') ? 'q-mb-sm' : ''"
              dense
              type="textarea"
              outlined
              v-model="form.observaciones"
              label="observaciones"
              :loading="form.validating"
              @change="form.validate('observaciones')"
              :error="form.invalid('observaciones')"
            >
              <template v-slot:error>
                {{ form.errors.observaciones }}
              </template>
            </q-input>
          </div>
        </q-card-section>
      </div>
      <q-separator />
      <q-card-actions align="right">
        <q-btn label="Cancelar" flat v-close-popup />
        <q-btn
          label="Guardar"
          color="positive"
          :loading="form.processing"
          type="submit"
        />
      </q-card-actions>
    </q-form>
  </q-card>
</template>
<script setup>
import { useForm } from "laravel-precognition-vue";
import { onMounted, ref, watch } from "vue";
import GenerarPDFSolicitud from "src/components/GenerarPDFSolicitud.vue";
import { useQuasar } from "quasar";

const $q = useQuasar();
const emits = defineEmits(["save"]);
const props = defineProps({
  title: String,
  D_solicitud: {
    type: Object,
    required: true,
    validator: (value) => value && value.id && value.solicitante,
  },
  D_busqueda: {
    type: Object,
    required: true,
    validator: (value) => value && value.id && value.cod_protocolo,
  },
});

// Validación inicial de props
if (!props.D_solicitud?.id || !props.D_busqueda?.id) {
  console.error("Faltan datos requeridos en las props");
  $q.notify({
    type: "negative",
    message: "Error: Datos incompletos para la verificación",
    position: "top",
  });
}

const opcionVista = ref(1);
const vueltaFI = ref(false);
const vueltaFF = ref(false);

function CargaVueltaFolio(dato) {
  return dato && /[vV]/.test(dato);
}

// Inicialización del formulario con validación
let form = useForm("post", "api/registro_verificaciones", {
  solicitud_id: props.D_solicitud?.id || null,
  RB_id_derivado: props.D_busqueda?.id || null,
  cod_protocolo: props.D_busqueda?.cod_protocolo || "",
  cod_escritura: props.D_busqueda?.cod_escritura || "",
  cod_folioInicial: props.D_busqueda?.cod_folioInicial || "",
  cod_folioFinal: props.D_busqueda?.cod_folioFinal || "",
  observaciones: props.D_busqueda?.observaciones || "",
});

onMounted(async () => {
  if (!props.D_busqueda) return;

  vueltaFI.value = CargaVueltaFolio(props.D_busqueda.cod_folioInicial);
  vueltaFF.value = CargaVueltaFolio(props.D_busqueda.cod_folioFinal);

  // Validación inicial del formulario
  [
    "cod_protocolo",
    "cod_escritura",
    "cod_folioInicial",
    "cod_folioFinal",
  ].forEach((field) => {
    form.validate(field);
  });
});

// const submit = async () => {
//   console.log('Datos a enviar:', {
//   ...form.data(),
//   RB_id_derivado: form.RB_id_derivado,
//   solicitud_id: form.solicitud_id
// });
//   try {
//     // Aplicar modificaciones de "vuelta" si es necesario
//     if(vueltaFI.value) {
//       form.cod_folioInicial = CargaVueltaFolio(form.cod_folioInicial)
//         ? form.cod_folioInicial
//         : `${form.cod_folioInicial} V`;
//     }

//     if(vueltaFF.value) {
//       form.cod_folioFinal = CargaVueltaFolio(form.cod_folioFinal)
//         ? form.cod_folioFinal
//         : `${form.cod_folioFinal} V`;
//     }

//     // Validación manual de campos requeridos
//     const requiredFields = ['solicitud_id', 'RB_id_derivado', 'cod_protocolo', 'cod_escritura', 'cod_folioInicial', 'cod_folioFinal'];
//     const missingFields = requiredFields.filter(field => !form[field]);

//     if (missingFields.length > 0) {
//       throw new Error(`Faltan campos requeridos: ${missingFields.join(', ')}`);
//     }

//     const response = await form.submit();

//     $q.notify({
//       type: 'positive',
//       message: 'Verificación guardada correctamente',
//       position: 'top-right'
//     });

//     emits("save", 'verificacion');

//   } catch (error) {
//     console.error('Error al guardar verificación:', error);
//     console.log('Respuesta completa del error:', error.response?.data);

//     let errorMessage = 'Error al guardar la verificación';

//     if (error.response?.status === 422) {
//       errorMessage = Object.values(error.response.data.errors).flat().join(', ');
//     } else if (error.message) {
//       errorMessage = error.message;
//     }

//     $q.notify({
//       type: 'negative',
//       message: errorMessage,
//       position: 'top-right',
//       timeout: 5000
//     });
//   }
// };

// Watchers para manejar el estado de los toggles de "vuelta"

const submit = async () => {
  try {
    // Verificar que los datos requeridos existen
    if (!props.D_solicitud?.id || !props.D_busqueda?.id) {
      throw new Error("Datos incompletos para realizar la verificación");
    }

    // Aplicar modificaciones de "vuelta"
    if (vueltaFI.value) {
      form.cod_folioInicial = form.cod_folioInicial.includes(" V")
        ? form.cod_folioInicial
        : `${form.cod_folioInicial} V`;
    }

    if (vueltaFF.value) {
      form.cod_folioFinal = form.cod_folioFinal.includes(" V")
        ? form.cod_folioFinal
        : `${form.cod_folioFinal} V`;
    }

    // Mostrar datos que se enviarán
    console.log("Enviando datos:", {
      solicitud_id: props.D_solicitud.id,
      RB_id_derivado: props.D_busqueda.id,
      ...form.data(),
    });

    const response = await form.submit();

    $q.notify({
      type: "positive",
      message: "Verificación registrada correctamente",
      position: "top",
      timeout: 2000,
    });

    emits("save", "verificacion");
  } catch (error) {
    let errorMessage = "Error al guardar la verificación";

    if (error.response?.data?.errors) {
      // Mostrar todos los errores de validación
      errorMessage = Object.values(error.response.data.errors)
        .flat()
        .join(", ");
    } else if (error.message) {
      errorMessage = error.message;
    }

    $q.notify({
      type: "negative",
      message: errorMessage,
      position: "top",
      timeout: 5000,
      actions: [{ icon: "close", color: "white" }],
    });

    console.error("Error detallado:", {
      error: error.response?.data || error.message,
      requestData: form.data(),
    });
  }
};

const deshabili_FI = ref(form.cod_folioInicial === "");
watch(
  () => form.cod_folioInicial,
  (newVal) => {
    if (newVal === "") {
      vueltaFI.value = false;
      deshabili_FI.value = true;
    } else {
      deshabili_FI.value = false;
    }
  }
);

const deshabili_FF = ref(form.cod_folioFinal === "");
watch(
  () => form.cod_folioFinal,
  (newVal) => {
    if (newVal === "") {
      vueltaFF.value = false;
      deshabili_FF.value = true;
    } else {
      deshabili_FF.value = false;
    }
  }
);

defineExpose({ form });
</script>

<style scoped>
.my-card {
  width: 100%;
  max-width: 80vw;
}
</style>
