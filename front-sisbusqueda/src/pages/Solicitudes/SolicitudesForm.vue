<template>
  <q-card class="my-card" style="width: 1400px; max-width: 80vw">
    <q-card-section class="bg-primary text-white row">
      <div class="text-h6">{{ title }}</div>
      <q-space />
      <q-btn icon="close" color="negative" round v-close-popup />
    </q-card-section>
    <q-card-section class="q-pa-none">
      <div class="col-xs-12 col-sm-8 q-pa-sm">
        <q-form
          @submit="onSubmit"
          @validation-error="ValidaError(step)"
          @validation-success="ValidaSuccess($refs.stepper, step)"
        >
          <q-stepper
            v-model="step"
            ref="stepper"
            color="primary"
            header-nav
            animated
            flat
            bordered
          >
            <!-- Paso 1: Solicitante -->
            <q-step
              :name="1"
              title="Registro Solicitante"
              icon="settings"
              :done="step > 1"
              :header-nav="step > 1"
            >
              <div class="text-subtitle2">Tipo de Docuemnto</div>
              <q-option-group
                v-model="solicitudForm.tipo_documento"
                inline
                @update:model-value="onReset"
                :options="[
                  { label: 'DNI', value: 'DNI' },
                  { label: 'RUC', value: 'RUC' },
                ]"
              />
              <q-input
                outlined
                lazy-rules
                dense
                v-model="solicitudForm.num_documento"
                class="q-pa-sm"
                :label="solicitudForm.tipo_documento"
                :mask="
                  solicitudForm.tipo_documento === 'RUC'
                    ? '###########'
                    : '########'
                "
                :rules="
                  solicitudForm.tipo_documento === 'RUC'
                    ? [
                        (val) =>
                          (val && val !== '' && val.length === 11) ||
                          'Por favor ingrese el RUC',
                      ]
                    : [
                        (val) =>
                          (val && val !== '' && val.length === 8) ||
                          'Por favor ingrese el DNI',
                      ]
                "
              >
                <template v-slot:label>
                  {{ solicitudForm.tipo_documento }}
                  <span class="text-red-7 text-weight-bold">(*)</span>
                </template>
                <template v-slot:after>
                  <q-btn
                    :label="$q.screen.lt.sm ? '' : 'Buscar'"
                    @click="getSolicitante"
                    color="primary"
                    icon-right="search"
                  />
                </template>
              </q-input>
              <div v-if="NoEncontroDatosPersona">No se encontraron datos</div>
              <q-tab-panels
                v-model="solicitudForm.tipo_documento"
                class="q-mt-md"
              >
                <q-tab-panel name="DNI" class="q-pa-none q-mb-md row">
                  <q-input
                    class="col-12 col-md-4 q-pa-sm"
                    label="Primer Apellido"
                    dense
                    outlined
                    v-model="solicitudForm.apellido_paterno"
                    :loading="loading"
                    lazy-rules
                    :rules="[
                      (val) =>
                        (val && val !== '') ||
                        'Por favor ingrese Primer Apellido',
                    ]"
                  >
                    <template v-slot:label>
                      Primer Apellido
                      <span class="text-red-7 text-weight-bold">(*)</span>
                    </template>
                  </q-input>
                  <q-input
                    class="col-12 col-md-4 q-pa-sm"
                    label="Segundo Apellido"
                    dense
                    outlined
                    v-model="solicitudForm.apellido_materno"
                    :loading="loading"
                    lazy-rules
                    :rules="[
                      (val) =>
                        (val && val !== '') ||
                        'Por favor ingrese Segundo Apellido',
                    ]"
                  >
                    <template v-slot:label>
                      Segundo Apellido
                      <span class="text-red-7 text-weight-bold">(*)</span>
                    </template>
                  </q-input>
                  <q-input
                    class="col-12 col-md-4 q-pa-sm"
                    label="Nombres"
                    dense
                    outlined
                    v-model="solicitudForm.nombres"
                    :loading="loading"
                    lazy-rules
                    :rules="[
                      (val) =>
                        (val && val !== '') || 'Por favor ingrese nombres',
                    ]"
                  >
                    <template v-slot:label>
                      Nombres
                      <span class="text-red-7 text-weight-bold">(*)</span>
                    </template>
                  </q-input>
                </q-tab-panel>
                <q-tab-panel name="RUC" class="q-pa-none q-mb-md">
                  <q-input
                    class="col-12 col-md-6 q-pa-sm"
                    label="Asunto"
                    dense
                    outlined
                    v-model="solicitudForm.asunto"
                    :loading="loading"
                    lazy-rules
                    :rules="[
                      (val) =>
                        (val && val !== '') || 'Por favor ingrese Asunto',
                    ]"
                  >
                    <template v-slot:label>
                      Asunto
                      <span class="text-red-7 text-weight-bold">(*)</span>
                    </template>
                  </q-input>
                </q-tab-panel>
              </q-tab-panels>
              <div v-if="okSolicitante" class="row">
                <div class="row full-width">
                  <SelectUbigeoPlus
                    Class="q-pa-sm col-12 col-sm-6 col-md-4"
                    dense
                    outlined
                    clearable
                    v-model="solicitudForm.ubigeo_cod"
                    :loading="loading"
                    :requerido="true"
                  />
                </div>
                <q-input
                  class="col-12 col-md-4 q-pa-sm"
                  label="Celular"
                  dense
                  outlined
                  clearable

                  v-model="solicitudForm.celular"
                  mask="### ### ###"

                  :loading="loading"
                  :requerido="true"

                  lazy-rules
                    :rules="[
                      (val) =>
                        (val && val !== '') || 'Por favor ingrese su celular',
                    ]"
                >
                  <template v-slot:label>
                    Celular
                    <span class="text-red-7 text-weight-bold">(*)</span>
                  </template>

                </q-input>


                <q-input
                  class="col-12 col-md-4 q-pa-sm"
                  label="Correo Electrónico"
                  dense
                  outlined
                  v-model="solicitudForm.correo"
                  lazy-rules
                  :rules="[
                    (val) =>
                      val === null ||
                      val === '' ||
                      /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) ||
                      'Por favor ingrese un correo electrónico válido',
                  ]"
                />
                <q-input
                  class="col-12 col-md-4 q-pa-sm"
                  label="Direccion - Domicilio"
                  dense
                  outlined
                  clearable
                  v-model="solicitudForm.direccion"
                  :loading="loading"
                  :requerido="true"

                  lazy-rules
                    :rules="[
                      (val) =>
                        (val && val !== '') || 'Por favor ingrese su Direccion - Domicilio',
                    ]"
                >
                  <template v-slot:label>
                    Direccion - Domicilio
                    <span class="text-red-7 text-weight-bold">(*)</span>
                  </template>

                </q-input>
              </div>
            </q-step>

            <!-- Paso 2: Solicitud -->
            <q-step
              :name="2"
              title="Registrar Solicitud"
              icon="create_new_folder"
              :done="step > 2"
              :header-nav="step > 2"
            >
              <div class="q-mb-md row">
                <SelectInput
                  class="col-12 col-md-6 q-pa-sm"
                  label="Notarios"
                  dense
                  outlined
                  clearable
                  lazy-rules
                  v-model="solicitudForm.notario_id"
                  :rules="[
                    (val) =>
                      (val && val !== '') || 'Por favor seleccione un notario',
                  ]"
                  :options="NotarioServive"
                  :option-label="
                    (notario) => {
                      const nombreUbigeo = notario.ubigeo?.nombre;
                      return nombreUbigeo
                        ? `${notario.nombre_completo} - ${nombreUbigeo}`.trim()
                        : notario.nombre_completo;
                    }
                  "
                  option-value="id"
                  :requerido="true"
                />
                <SelectInput
                  class="col-12 col-md-6 q-pa-sm"
                  label="Subserie"
                  dense
                  outlined
                  clearable
                  v-model="solicitudForm.subserie_id"
                  :options="SubSerieService"
                  OptionLabel="nombre"
                  OptionValue="id"
                />
                <InputTextSelect
                  class="col-12 col-md-6 q-pa-sm"
                  dense
                  outlined
                  clearable
                  v-model="solicitudForm.otorgantes"
                  label="Otorgante"
                  :requerido="true"
                  :options="OtorganteService"
                  OptionLabel="nombre_completo"
                  OptionValue="nombre_completo"
                />
                <InputTextSelect
                  class="col-12 col-md-6 q-pa-sm"
                  dense
                  outlined
                  clearable
                  v-model="solicitudForm.favorecidos"
                  label="Favorecido"
                  :requerido="true"
                  :options="FavorecidoService"
                  OptionLabel="nombre_completo"
                  OptionValue="nombre_completo"
                />
                <div class="row full-width"></div>
                <div class="col-12 col-md-6 q-pa-sm row">
                  <InputAnio
                    :requerido="true"
                    class="col-12 col-sm-4"
                    dense
                    outlined
                    v-model="solicitudForm.anio"
                    :RangoAnios="rangoAnios"
                    :key="'anio-' + rangoAnios.join('-')"
                  />
                  <InputMes
                    :readonly="solicitudForm.anio === null"
                    class="col-12 col-sm-4"
                    dense
                    outlined
                    clearable
                    v-model="solicitudForm.mes"
                    :modelAnio="solicitudForm.anio"
                  />
                  <InputDia
                    :readonly="
                      solicitudForm.anio === null || solicitudForm.mes === null
                    "
                    class="col-12 col-sm-4"
                    dense
                    outlined
                    v-model="solicitudForm.dia"
                    v-model:modelAnio="solicitudForm.anio"
                    v-model:modelMes="solicitudForm.mes"
                  />
                </div>
                <q-input
                  class="col-12 col-md-6 q-pa-sm"
                  dense
                  outlined
                  clearable
                  v-model="solicitudForm.bien"
                  label="Nombre del Bien"
                />
                <q-input
                  dense
                  outlined
                  clearable
                  class="col-12 col-md-4 q-pa-sm"
                  v-model="solicitudForm.sescritura"
                  label="Escritura"
                  mask="E-######"
                />
                <q-input
                  dense
                  outlined
                  clearable
                  class="col-12 col-md-4 q-pa-sm"
                  v-model="solicitudForm.sprotocolo"
                  label="Protocolo"
                  mask="P-######"
                />
                <q-input
                  dense
                  outlined
                  clearable
                  class="col-12 col-md-4 q-pa-sm"
                  v-model="solicitudForm.sfolio"
                  label="Folio"
                  mask="F-######"
                />
                <q-input
                  dense
                  outlined
                  clearable
                  class="col-12 q-pa-sm"
                  v-model="solicitudForm.observaciones"
                  label="Observaciones"
                  type="textarea"
                  rows="3"
                />
              </div>
            </q-step>

            <!-- Paso 3: Caja -->
            <q-step
              :name="3"
              title="Registro Caja"
              icon="point_of_sale"
              :done="step > 3"
              :header-nav="step > 3"
            >
              <div class="row full-width q-mb-md">
                <q-space />
                <!-- <div class="row items-center text-body">
                  <q-icon name="description" class="q-mr-xs" />
                  <span class="text-bold">Concepto:</span>
                  <span class="q-ml-xs">Búsqueda de documentos</span>
                  <q-space />
                  <span class="q-ml-md">Precio: <span class="text-primary">{{ formatNumberToSoles(redondearConDecimales(precioVigente)) }}</span></span>
                </div> -->
                <div class="row items-center text-body">
                  <q-icon name="description" class="q-mr-xs" />
                  <span class="text-bold">Concepto:</span>
                  <span class="q-ml-xs">{{ tupaBusqueda?.denominacion || 'Búsqueda de documentos' }}</span>
                  <q-space />
                  <span class="q-ml-md">
                    Precio:
                    <span class="text-primary">
                      {{ tupaBusqueda ? formatNumberToSoles(Number(tupaBusqueda.costo)) : '' }}
                    </span>
                  </span>
                </div>
              </div>
              <div class="row">
                <div class="col-12 col-md-6">
                  <q-input
                    class="q-pa-sm"
                    type="number"
                    min="0"
                    step="0.01"
                    prefix="S/"
                    dense
                    outlined
                    clearable
                    v-model.number="montoEntregado"
                    label="Monto entregado"
                    :rules="[val => val === null || val === '' || Number(val) >= 0 || 'Ingrese un monto válido']"
                  />
                </div>
                <div class="col-12 col-md-6 row">
                  <div class="col-7">
                    <div v-if="montoEntregado" class="q-ma-sm text-right text-weight-bold text-subtitle1">
                      Monto Entregado:
                    </div>
                    <div class="q-ma-sm text-right text-weight-bold text-subtitle1">
                      Subtotal:
                    </div>
                    <div v-if="montoEntregado" class="q-ma-sm text-right text-weight-bold text-subtitle1">
                      {{ Number(montoEntregado) < redondearConDecimales(tupaBusqueda.costo) ? 'Faltante:' : 'Vuelto:' }}
                    </div>
                  </div>
                  <div class="col-5">
                    <div v-if="montoEntregado" class="q-ma-sm text-subtitle1">
                      {{ formatNumberToSoles(montoEntregado) }}
                    </div>
                    <div class="q-ma-sm text-green-13 text-weight-bold text-subtitle1">
                      {{ formatNumberToSoles(redondearConDecimales(tupaBusqueda.costo)) }}
                    </div>
                    <div v-if="montoEntregado" class="q-ma-sm text-subtitle1"
                      :class="Number(montoEntregado) < redondearConDecimales(tupaBusqueda.costo) ? 'text-negative' : 'text-positive'">
                      {{ formatNumberToSoles(Number(montoEntregado) - redondearConDecimales(tupaBusqueda.costo)) }}
                    </div>
                  </div>
                </div>
              </div>
            </q-step>

            <template v-slot:navigation>
              <q-stepper-navigation>
                <q-btn
                  color="primary"
                  type="submit"
                  :label="step === 3 ? 'Enviar Con Pago' : 'Siguiente'"
                  :disable="step === 3 && (!montoEntregado || Number(montoEntregado) < redondearConDecimales(tupaBusqueda?.costo))"
                />
                <q-btn
                  v-if="step > 1"
                  flat
                  color="primary"
                  @click="$refs.stepper.previous()"
                  label="Regresar"
                  class="q-ml-sm"
                />
                <q-btn
                  v-if="step === 3"
                  color="secondary"
                  label="Enviar sin pago"
                  @click="enviarSinPago"
                  class="q-ml-sm"
                  :disable="montoEntregado !== undefined && montoEntregado !== null && montoEntregado !== '' && Number(montoEntregado) >= redondearConDecimales(tupaBusqueda?.costo)"
                />
              </q-stepper-navigation>
            </template>
          </q-stepper>
        </q-form>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import DniService from "src/services/DniService";
import SolicitudService from "src/services/SolicitudService";
import NotarioServive from "src/services/NotarioService";
import SubSerieService from "src/services/SubSerieService";
import TupaService from "src/services/TupaService";
import PagoService from "src/services/PagoService";
import OtorganteService from "src/services/OtorganteService";
import FavorecidoService from "src/services/FavorecidoService";
import SelectUbigeoPlus from "src/components/SelectUbigeoPlus.vue";
import SelectInput from "src/components/SelectInput.vue";
import {
  formatNumberToSoles,
  redondearConDecimales,
} from "src/utils/ConvertMoney";
import { useQuasar } from "quasar";
import InputAnio from "src/components/InputAnio.vue";
import InputMes from "src/components/InputMes.vue";
import InputDia from "src/components/InputDia.vue";
import InputTextSelect from "src/components/InputTextSelect.vue";

const $q = useQuasar();
const step = ref(1);
const emit = defineEmits(["save"]);
const montoEntregado = ref();
const precioVigente = ref();
const props = defineProps({
  title: String,
});

const notarioAñoInicio = ref(1801);
const notarioAñoFinal  = ref(new Date().getFullYear());
const rangoAnios = computed(() => [
  notarioAñoInicio.value,
  notarioAñoFinal.value
]);
const notarios = ref([]);

const tupaBusqueda = ref(null);

async function cargarTupaBusqueda() {
  const resp = await TupaService.getData({ params: { sub_code: "0101" } });
  tupaBusqueda.value = Array.isArray(resp.data) ? resp.data[0] : resp.data;
}

onMounted(async () => {
  await cargarTupaBusqueda();
  await getPrecioVigente();
  try {
    await getPrecioVigente();
    const response = await NotarioServive.getData({ params: { rowsPerPage: 0 } });
    if (response && Array.isArray(response.data) && response.data.length > 0) {
      notarios.value = response.data;
    } else {
      notarios.value = [];
    }
  } catch (error) {
    notarios.value = [];
    $q.notify({
      type: "negative",
      message:
        "No se pudieron cargar los notarios. Por favor, inténtalo de nuevo.",
    });
  }
});

const solicitudForm = ref({
  id: null,
  nombres: "",
  apellido_paterno: "",
  apellido_materno: "",
  nombre_completo: "",
  asunto: "",
  tipo_documento: "DNI",
  num_documento: "",
  direccion: "",
  correo: "",
  celular: "",
  ubigeo_cod: "",
  notario_id: "",
  subserie_id: "",
  otorgantes: "",
  favorecidos: "",
  anio: null,
  mes: null,
  dia: null,
  ubigeo_cod_soli: "",
  bien: "",
  observaciones: "",
  precio: "",
  sfolio: "",
  sprotocolo: "",
  sescritura: "",
  mas_datos: ""
});

async function getPrecioVigente() {
  // Asegúrate de usar el valor reactivo de tupaBusqueda
  const costo = Number(tupaBusqueda.value?.costo ?? 0);
  precioVigente.value = costo;
  solicitudForm.value.precio = redondearConDecimales(costo);
}

const okSolicitante = computed(() => {
  if (solicitudForm.value.tipo_documento === "DNI") {
    return (
      solicitudForm.value.nombres !== "" &&
      solicitudForm.value.apellido_materno !== "" &&
      solicitudForm.value.apellido_paterno !== "" &&
      solicitudForm.value.num_documento !== ""
    );
  } else {
    return (
      solicitudForm.value.asunto !== "" &&
      solicitudForm.value.num_documento !== ""
    );
  }
});

const nombreCompleto = computed(() => {
  return (
    solicitudForm.value.nombres +
    " " +
    solicitudForm.value.apellido_paterno +
    " " +
    solicitudForm.value.apellido_materno
  );
});


const masDatos = computed(() => {
  const parts = [
    solicitudForm.value.observaciones,
    solicitudForm.value.sescritura,
    solicitudForm.value.sprotocolo,
    solicitudForm.value.sfolio
  ].filter(Boolean); // Elimina valores vacíos

  return parts.join(' - '); // Une con guiones
});


const loading = ref(false);
const NoEncontroDatosPersona = ref(false);

async function getSolicitante() {
  loading.value = true;
  try {
    let res_solicitante = await DniService.getSolicitanteDni(
      solicitudForm.value.num_documento
    );
    if (res_solicitante?.existe) {
      NoEncontroDatosPersona.value = false;
      solicitudForm.value.nombres = res_solicitante.nombres;
      solicitudForm.value.apellido_paterno = res_solicitante.apellido_paterno;
      solicitudForm.value.apellido_materno = res_solicitante.apellido_materno;
      solicitudForm.value.direccion = res_solicitante.direccion;
      solicitudForm.value.correo = res_solicitante.correo;
      solicitudForm.value.celular = res_solicitante.celular;
      solicitudForm.value.ubigeo_cod = res_solicitante.ubigeo_cod;
    } else if (res_solicitante?.message) {
      NoEncontroDatosPersona.value = true;
      onReset(false);
    } else {
      NoEncontroDatosPersona.value = false;
      solicitudForm.value.nombres = res_solicitante.nombres;
      solicitudForm.value.apellido_paterno = res_solicitante.apellidoPaterno;
      solicitudForm.value.apellido_materno = res_solicitante.apellidoMaterno;
    }
  } catch (error) {
    NoEncontroDatosPersona.value = true;
  }
  loading.value = false;
}

function onReset(n_documento = true) {
  solicitudForm.value.cargo = null;
  if (n_documento) {
    solicitudForm.value.num_documento = "";
  }
  solicitudForm.value.nombres = "";
  solicitudForm.value.apellido_paterno = "";
  solicitudForm.value.apellido_materno = "";
  solicitudForm.value.nombre_completo = "";
  solicitudForm.value.asunto = "";
  solicitudForm.value.correo = null;
  solicitudForm.value.celular = null;
  solicitudForm.value.direccion = null;
  solicitudForm.value.ubigeo_cod = null;
}

watch(
  () => solicitudForm.value.notario_id,
  (newNotarioId) => {
    solicitudForm.value.anio = null;
    solicitudForm.value.mes = null;
    solicitudForm.value.dia = null;
    if (!notarios.value.length) {
      notarioAñoInicio.value = 1801;
      notarioAñoFinal.value = new Date().getFullYear();
      return;
    }
    const notarioSeleccionado = notarios.value.find(n => String(n.id) === String(newNotarioId));
    const codUbigeo = notarioSeleccionado?.ubigeo_cod || '';
    solicitudForm.value.ubigeo_cod_soli = codUbigeo;
    // solicitudForm.value.ubigeo_cod = codUbigeo;

    if (newNotarioId) {
      const notarioSeleccionado = notarios.value.find(n => n.id === newNotarioId);
      if (notarioSeleccionado) {
        notarioAñoInicio.value = notarioSeleccionado.año_inicio ?? 1801;
        notarioAñoFinal.value = notarioSeleccionado.año_final ?? new Date().getFullYear();
      } else {
        notarioAñoInicio.value = 1801;
        notarioAñoFinal.value = new Date().getFullYear();
      }
    } else {
      notarioAñoInicio.value = 1801;
      notarioAñoFinal.value = new Date().getFullYear();
    }
  }
);

// Modifica la función para asignar el ubigeo del notario seleccionado
function asignarUbigeoNotario() {
  const notarioSeleccionado = notarios.value.find(
    n => String(n.id) === String(solicitudForm.value.notario_id)
  );
  const codUbigeo = notarioSeleccionado?.ubigeo_cod || '';
  solicitudForm.value.ubigeo_cod_soli = codUbigeo;
  // solicitudForm.value.ubigeo_cod = codUbigeo; // <-- Asigna también al campo principal de la solicitud
}

const onSubmit = async () => {};



// async function ValidaSuccess(event, step) {
//   if (step === 3) {
//     // Asignar nombre completo correctamente
//     if (solicitudForm.value.tipo_documento === 'DNI') {
//       solicitudForm.value.nombre_completo =
//         `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
//     } else {
//       solicitudForm.value.nombre_completo = solicitudForm.value.asunto;
//     }

//     solicitudForm.value.mas_datos = masDatos.value;
//     asignarUbigeoNotario();

//     try {
//       // Guardar la solicitud
//       const request = await SolicitudService.save(solicitudForm.value);
//       let solicitudId = request?.data?.id || request?.id;

//       if (!solicitudId) {
//         try {
//           const listaSolicitudes = await SolicitudService.getData();
//           if (Array.isArray(listaSolicitudes.data)) {
//             const ordenadas = listaSolicitudes.data.sort((a, b) => b.id - a.id);
//             if (ordenadas.length > 0) {
//               solicitudId = ordenadas[0].id;
//             }
//           }
//         } catch (e) {
//           solicitudId = null;
//         }
//       }

//       if (!solicitudId) {
//         $q.notify({
//           type: "negative",
//           message: "No se pudo obtener el ID de la solicitud. El pago no se guardará."
//         });
//         return;
//       }

//       // Buscar el TUPA de búsqueda de documentos (sub_code 0101)
//       const tupaBusquedaResp = await TupaService.getData({ params: { sub_code: "0101" } });
//       const tupaBusqueda = Array.isArray(tupaBusquedaResp.data)
//         ? tupaBusquedaResp.data[0]
//         : tupaBusquedaResp.data;

//       // Obtener el user_id del usuario logueado
//       let userId = null;
//       try {
//         const user = JSON.parse(localStorage.getItem('user'));
//         userId = user?.id || user?.user?.id || null;
//       } catch (e) {
//         userId = null;
//       }

//       // Generar nombre completo correctamente para el pago
//       let nombreCompletoPago = "";
//       if (solicitudForm.value.tipo_documento === 'DNI') {
//         nombreCompletoPago = `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
//       } else {
//         nombreCompletoPago = solicitudForm.value.asunto;
//       }

//       // Crear el pago con estado 0 y flags correctos
//       const pagoPayload = {
//         solicitud_id: solicitudId,
//         tipo_documento: solicitudForm.value.tipo_documento,
//         num_documento: solicitudForm.value.num_documento,
//         nombre_completo: nombreCompletoPago,
//         total: Number(tupaBusqueda.costo),
//         user_id: userId,
//         estado: 0,
//         tupas: [
//           {
//             tupa_id: tupaBusqueda.id,
//             cantidad: 1,
//             Subtotal: Number(tupaBusqueda.costo),
//             precio: Number(tupaBusqueda.costo),
//             denominacion: tupaBusqueda.denominacion
//           }
//         ],
//         desde_solicitud: true,
//         con_pago: true // <--- IMPORTANTE: aquí indicas que es CON pago
//       };

//       // Guardar el pago y obtener el ID
//       const pagoResponse = await PagoService.save(pagoPayload);
//       const pagoId = pagoResponse?.data?.id || pagoResponse?.id;

//       // Asignar el ID del pago al campo pago_busqueda de la solicitud
//       if (pagoId && solicitudId) {
//         await SolicitudService.update(solicitudId, { pago_busqueda: pagoId });
//       }

//       emit("save", "solicitud");
//     } catch (error) {
//       if (error.response && error.response.data && error.response.data.errors) {
//         $q.notify({
//           type: "negative",
//           message: Object.values(error.response.data.errors).flat().join('\n')
//         });
//       } else {
//         $q.notify({
//           type: "negative",
//           message: "Error al guardar el pago"
//         });
//       }
//     }
//   } else if (step === 2) {
//     event.next();
//   } else {
//     event.next();
//   }
// }


async function ValidaSuccess(event, step) {
  try {
    if (step === 3) {
      // Asignar nombre completo correctamente
      if (solicitudForm.value.tipo_documento === 'DNI') {
        solicitudForm.value.nombre_completo =
          `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
      } else {
        solicitudForm.value.nombre_completo = solicitudForm.value.asunto;
      }

      solicitudForm.value.mas_datos = masDatos.value;
      asignarUbigeoNotario();

      try {
        // Guardar la solicitud
        const request = await SolicitudService.save(solicitudForm.value);
        let solicitudId = request?.data?.id || request?.id;

        // Si no se obtuvo el ID, buscar el mayor ID de la lista de solicitudes
        if (!solicitudId) {
          try {
            const listaSolicitudes = await SolicitudService.getData();
            if (Array.isArray(listaSolicitudes.data)) {
              const ordenadas = listaSolicitudes.data.sort((a, b) => b.id - a.id);
              if (ordenadas.length > 0) {
                solicitudId = ordenadas[0].id;
              }
            }
          } catch (e) {
            solicitudId = null;
          }
        }

        if (!solicitudId) {
          $q.notify({
            type: "negative",
            message: "No se pudo obtener el ID de la solicitud. El pago no se guardará."
          });
          return;
        }

        // Buscar el TUPA de búsqueda de documentos (sub_code 0101)
        const tupaBusquedaResp = await TupaService.getData({ params: { sub_code: "0101" } });
        const tupaBusqueda = Array.isArray(tupaBusquedaResp.data)
          ? tupaBusquedaResp.data[0]
          : tupaBusquedaResp.data;

        // Obtener el user_id del usuario logueado
        let userId = null;
        try {
          const user = JSON.parse(localStorage.getItem('user'));
          userId = user?.id || user?.user?.id || null;
        } catch (e) {
          userId = null;
        }

        // Generar nombre completo correctamente para el pago
        let nombreCompletoPago = "";
        if (solicitudForm.value.tipo_documento === 'DNI') {
          nombreCompletoPago = `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
        } else {
          nombreCompletoPago = solicitudForm.value.asunto;
        }

        // Crear el pago con estado 0 y flags correctos
        const pagoPayload = {
          solicitud_id: solicitudId,
          tipo_documento: solicitudForm.value.tipo_documento,
          num_documento: solicitudForm.value.num_documento,
          nombre_completo: nombreCompletoPago,
          total: Number(tupaBusqueda.costo),
          user_id: userId,
          estado: 0,
          tupas: [
            {
              tupa_id: tupaBusqueda.id,
              cantidad: 1,
              Subtotal: Number(tupaBusqueda.costo),
              precio: Number(tupaBusqueda.costo),
              denominacion: tupaBusqueda.denominacion
            }
          ],
          desde_solicitud: true,
          con_pago: true // <--- IMPORTANTE: aquí indicas que es CON pago
        };

        // Guardar el pago y obtener el ID
        const pagoResponse = await PagoService.save(pagoPayload);
        const pagoId = pagoResponse?.data?.id || pagoResponse?.id;

        // Asignar el ID del pago al campo pago_busqueda de la solicitud
        if (pagoId && solicitudId) {
          await SolicitudService.update(solicitudId, { pago_busqueda: pagoId, estado: 'Buscando', area_id: 2 });
        }
        console.log("Payload de pago:", pagoPayload);



        emit("save", "solicitud");
      } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
          $q.notify({
            type: "negative",
            message: Object.values(error.response.data.errors).flat().join('\n')
          });
        } else {
          $q.notify({
            type: "negative",
            message: "Error al guardar el pago"
          });
        }

        console.error("Error al guardar el pago:", error);
      }
    } else if (step === 2) {
      event.next();
    } else {
      event.next();
    }
  } catch (error) {
    console.error(error); // <-- Agrega esto para ver el error completo en consola
    if (error.response && error.response.data && error.response.data.errors) {
      $q.notify({
        type: "negative",
        message: Object.values(error.response.data.errors).flat().join('\n')
      });
    } else if (error.response && error.response.data && error.response.data.message) {
      $q.notify({
        type: "negative",
        message: error.response.data.message
      });
    } else {
      $q.notify({
        type: "negative",
        message: "Error al guardar el pago"
      });
    }
  }
}



async function enviarSinPago() {
  try {
    // Asignar nombre completo correctamente
    if (solicitudForm.value.tipo_documento === 'DNI') {
      solicitudForm.value.nombre_completo =
        `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
    } else {
      solicitudForm.value.nombre_completo = solicitudForm.value.asunto;
    }

    asignarUbigeoNotario();
    solicitudForm.value.mas_datos = masDatos.value;

    // Guardar la solicitud y obtener el ID generado
    const request = await SolicitudService.save(solicitudForm.value);
    let solicitudId = request?.data?.id || request?.id;

    if (!solicitudId) {
      try {
        const listaSolicitudes = await SolicitudService.getData();
        if (Array.isArray(listaSolicitudes.data)) {
          const ordenadas = listaSolicitudes.data.sort((a, b) => b.id - a.id);
          if (ordenadas.length > 0) {
            solicitudId = ordenadas[0].id;
          }
        }
      } catch (e) {
        solicitudId = null;
      }
    }

    // Buscar el TUPA de búsqueda de documentos (sub_code 0101)
    const tupaBusquedaResp = await TupaService.getData({ params: { sub_code: "0101" } });
    const tupaBusqueda = Array.isArray(tupaBusquedaResp.data)
      ? tupaBusquedaResp.data[0]
      : tupaBusquedaResp.data;

    // Obtener el user_id del usuario logueado
    let userId = null;
    try {
      const user = JSON.parse(localStorage.getItem('user'));
      userId = user?.id || user?.user?.id || null;
    } catch (e) {
      userId = null;
    }

    // Generar nombre completo correctamente para el pago
    let nombreCompletoPago = "";
    if (solicitudForm.value.tipo_documento === 'DNI') {
      nombreCompletoPago = `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
    } else {
      nombreCompletoPago = solicitudForm.value.asunto;
    }

    // Crear el pago con estado 1 y flags correctos
    const pagoPayload = {
      solicitud_id: solicitudId,
      tipo_documento: solicitudForm.value.tipo_documento,
      num_documento: solicitudForm.value.num_documento,
      nombre_completo: nombreCompletoPago,
      total: Number(tupaBusqueda.costo),
      user_id: userId,
      estado: 1,
      tupas: [
        {
          tupa_id: tupaBusqueda.id,
          cantidad: 1,
          Subtotal: Number(tupaBusqueda.costo),
          precio: Number(tupaBusqueda.costo),
          denominacion: tupaBusqueda.denominacion
        }
      ],
      desde_solicitud: true,
      con_pago: false // <--- IMPORTANTE: aquí indicas que es SIN pago
    };

    await PagoService.save(pagoPayload);

    $q.notify({ type: "positive", message: "Solicitud enviada y pago generado sin boleta." });
    emit("save", "solicitud");
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errors) {
      $q.notify({
        type: "negative",
        message: Object.values(error.response.data.errors).flat().join('\n')
      });
    } else {
      $q.notify({
        type: "negative",
        message: "Error al enviar la solicitud sin pago"
      });
    }
  }
}
// async function enviarSinPago() {
//   try {
//     // Asignar el nombre completo según el tipo de documento
//     if (solicitudForm.value.tipo_documento === 'DNI') {
//       solicitudForm.value.nombre_completo =
//         solicitudForm.value.apellido_paterno + ' ' +
//         solicitudForm.value.apellido_materno + ' ' +
//         solicitudForm.value.nombres;
//     } else {
//       solicitudForm.value.nombre_completo = solicitudForm.value.asunto;
//     }

//     // Asignar el ubigeo_cod del notario seleccionado
//     // Siempre asignar el ubigeo del notario antes de guardar
//     asignarUbigeoNotario();
//     solicitudForm.value.mas_datos = masDatos.value;

//     // Mostrar datos de la solicitud antes de guardar
//     console.log("Datos enviados de la solicitud (sin pago):", solicitudForm.value);

//     // Guardar la solicitud y obtener el ID generado
//     const request = await SolicitudService.save(solicitudForm.value);
//     console.log("Respuesta del backend al guardar solicitud (sin pago):", request);

//     // Notificar éxito y cerrar modal
//     $q.notify({ type: "positive", message: "Solicitud enviada sin pago." });
//     emit("save", "solicitud");
//   } catch (error) {
//     if (error.response && error.response.data && error.response.data.errors) {
//       $q.notify({
//         type: "negative",
//         message: Object.values(error.response.data.errors).flat().join('\n')
//       });
//     } else {
//       $q.notify({
//         type: "negative",
//         message: "Error al enviar la solicitud sin pago"
//       });
//     }
//   }
// }

function ValidaError(step) {}

function setValue(values) {
  solicitudForm.value.id = values.id;
  solicitudForm.value.nombres = values.solicitante.nombres;
  solicitudForm.value.apellido_paterno = values.solicitante.apellido_paterno;
  solicitudForm.value.apellido_materno = values.solicitante.apellido_materno;
  solicitudForm.value.num_documento = values.solicitante.num_documento;
  solicitudForm.value.celular = values.solicitante.celular;
  solicitudForm.value.correo = values.solicitante.correo;
  solicitudForm.value.direccion = values.solicitante.direccion;
  solicitudForm.value.ubigeo_cod = values.solicitante.ubigeo_cod;
  solicitudForm.value.otorgantes = values.otorgantes;
  solicitudForm.value.favorecidos = values.favorecidos;
  solicitudForm.value.ubigeo_cod_soli = values.ubigeo_cod;
  solicitudForm.value.bien = values.bien;
  solicitudForm.value.mas_datos = values.masDatos;
}

defineExpose({
  setValue,
});
</script>
<style></style>
