<template>
  <!-- ============================================= -->
  <!-- CARD PRINCIPAL DEL FORMULARIO DE SOLICITUDES -->
  <!-- ============================================= -->
  <q-card class="my-card" style="width: 1400px; max-width: 80vw">
    <!-- HEADER DEL CARD -->
    <q-card-section class="bg-primary text-white row items-center">
      <div class="text-h6">{{ title }}</div>
      <q-space />
      <q-btn icon="close" color="negative" round v-close-popup />
    </q-card-section>

    <!-- CONTENIDO PRINCIPAL: STEPPER + FORMULARIO -->
    <q-card-section class="q-pa-none">
      <div class="col-xs-12 col-sm-8 q-pa-sm">
        <q-form
          @submit="onSubmit"
          @validation-error="ValidaError(step)"
          @validation-success="ValidaSuccess($refs.stepper, step)"
          ref="formRef"
        >
          <!-- ============================ -->
          <!-- STEPPER DE NAVEGACIÓN -->
          <!-- ============================ -->
          <q-stepper
            v-model="step"
            ref="stepper"
            color="primary"
            header-nav
            animated
            flat
            bordered
          >
            <!-- ====================================== -->
            <!-- PASO 0: SELECCIÓN DE TIPO DE TRÁMITE -->
            <!-- ====================================== -->
            <q-step
              :name="0"
              title="Tipo de Trámite"
              icon="assignment"
              :done="step > 0"
              :header-nav="step > 0"
            >
              <div class="q-pa-md">
                <div class="text-h6 q-mb-md">
                  Seleccione el tipo de trámite:
                </div>

                <!-- GRID DE OPCIONES DE TRÁMITE -->
                <div class="row q-col-gutter-md q-mb-lg">
                  <div
                    v-for="tramite in tramites"
                    :key="tramite.value"
                    class="col-6 col-sm-3"
                  >
                    <q-card
                      class="tramite-card hover-card flex flex-center column"
                      :class="{
                        'tramite-selected':
                          solicitudForm.tipo_tramite === tramite.value,
                      }"
                      flat
                      bordered
                      style="height: 100px"
                      @click="seleccionarTramite(tramite.value)"
                    >
                      <q-card-section class="flex flex-center column q-pa-sm">
                        <q-avatar
                          size="60px"
                          :color="tramite.color"
                          class="q-mb-sm"
                        >
                          <q-icon
                            :name="tramite.icon"
                            size="30px"
                            color="white"
                          />
                        </q-avatar>
                        <div class="text-subtitle2 text-center">
                          {{ tramite.label }}
                        </div>
                      </q-card-section>

                      <!-- BADGE DE SELECCIONADO -->
                      <q-badge
                        v-if="solicitudForm.tipo_tramite === tramite.value"
                        color="green"
                        label="✔ Seleccionado"
                        class="absolute-top-right q-mt-sm q-mr-sm"
                      />
                    </q-card>
                  </div>
                </div>

                <!-- MENSAJE DE VALIDACIÓN -->
                <div
                  v-if="!solicitudForm.tipo_tramite"
                  class="q-mt-md text-negative"
                >
                  Por favor seleccione un tipo de trámite para continuar.
                </div>
              </div>
            </q-step>

            <!-- ========================================== -->
            <!-- PASO 1: REGISTRO DEL SOLICITANTE -->
            <!-- ========================================== -->
            <q-step
              :name="1"
              title="Registro Solicitante"
              icon="person"
              :done="step > 1"
              :header-nav="step > 1"
            >
              <div class="q-pa-md">
                <!-- TIPO DE DOCUMENTO -->
                <div class="text-subtitle2 q-mb-sm">Tipo de Documento</div>
                <q-option-group
                  v-model="solicitudForm.tipo_documento"
                  inline
                  @update:model-value="onResetDocumento"
                  :options="[
                    { label: 'DNI', value: 'DNI' },
                    { label: 'RUC', value: 'RUC' },
                  ]"
                />

                <!-- NÚMERO DE DOCUMENTO CON BÚSQUEDA -->
                <q-input
                  outlined
                  dense
                  lazy-rules
                  v-model="solicitudForm.num_documento"
                  :label="solicitudForm.tipo_documento"
                  :mask="
                    solicitudForm.tipo_documento === 'RUC'
                      ? '###########'
                      : '########'
                  "
                  :rules="documentoRules"
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
                      :loading="loading"
                    />
                  </template>
                </q-input>

                <!-- MENSAJE SI NO ENCUENTRA DATOS -->
                <div
                  v-if="NoEncontroDatosPersona"
                  class="q-pa-sm text-negative"
                >
                  No se encontraron datos para el documento ingresado.
                </div>

                <!-- CAMPOS SEGÚN TIPO DE DOCUMENTO -->
                <q-tab-panels
                  v-model="solicitudForm.tipo_documento"
                  class="q-mt-md"
                >
                  <!-- PANEL PARA DNI -->
                  <q-tab-panel name="DNI" class="q-pa-none q-mb-md row">
                    <q-input
                      class="col-12 col-md-4 q-pa-sm"
                      label="Primer Apellido"
                      dense
                      outlined
                      v-model="solicitudForm.apellido_paterno"
                      :loading="loading"
                      :rules="[
                        (val) =>
                          (val && val !== '') ||
                          'Por favor ingrese Primer Apellido',
                      ]"
                    />
                    <q-input
                      class="col-12 col-md-4 q-pa-sm"
                      label="Segundo Apellido"
                      dense
                      outlined
                      v-model="solicitudForm.apellido_materno"
                      :loading="loading"
                      :rules="[
                        (val) =>
                          (val && val !== '') ||
                          'Por favor ingrese Segundo Apellido',
                      ]"
                    />
                    <q-input
                      class="col-12 col-md-4 q-pa-sm"
                      label="Nombres"
                      dense
                      outlined
                      v-model="solicitudForm.nombres"
                      :loading="loading"
                      :rules="[
                        (val) =>
                          (val && val !== '') || 'Por favor ingrese nombres',
                      ]"
                    />
                  </q-tab-panel>

                  <!-- PANEL PARA RUC -->
                  <q-tab-panel name="RUC" class="q-pa-none q-mb-md row">
                    <q-input
                      class="col-12 col-md-6 q-pa-sm"
                      label="Razón social / Asunto"
                      dense
                      outlined
                      v-model="solicitudForm.asunto"
                      :loading="loading"
                      :rules="[
                        (val) =>
                          (val && val !== '') || 'Por favor ingrese Asunto',
                      ]"
                    />
                  </q-tab-panel>
                </q-tab-panels>

                <!-- DATOS ADICIONALES DEL SOLICITANTE -->
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
                        (val && val !== '') ||
                        'Por favor ingrese su Direccion - Domicilio',
                    ]"
                  >
                    <template v-slot:label>
                      Direccion - Domicilio
                      <span class="text-red-7 text-weight-bold">(*)</span>
                    </template>
                  </q-input>
                </div>
              </div>
            </q-step>

            <!-- ====================================== -->
            <!-- PASO 2: REGISTRO DE SOLICITUD/TRÁMITE -->
            <!-- ====================================== -->
            <q-step
              :name="2"
              title="Registrar Solicitud"
              icon="create_new_folder"
              :done="step > 2"
              :header-nav="step > 2"
            >
              <div class="q-pa-md row">
                <!-- COMPONENTES DINÁMICOS SEGÚN TIPO DE TRÁMITE -->
                <FormEscrituras
                  v-if="solicitudForm.tipo_tramite === 'escrituras'"
                  :solicitudForm="solicitudForm"
                  :notarios="notarios"
                  :rangoAnios="rangoAnios"
                  @asignar-ubigeo-notario="asignarUbigeoNotario"
                />

                <FormPartidas
                  v-else-if="solicitudForm.tipo_tramite === 'partidas'"
                  :solicitudForm="solicitudForm"
                  :errorTipoPartida="errorTipoPartida"
                  @seleccionar-tipo-partida="seleccionarTipoPartida"
                />

                <FormExpedientes
                  v-else-if="solicitudForm.tipo_tramite === 'expedientes'"
                  :solicitudForm="solicitudForm"
                  :errorTipoExpediente="errorTipoExpediente"
                  :materiasDisponibles="materiasDisponibles"
                  @seleccionar-tipo-expediente="seleccionarTipoExpediente"
                  @filter-materias="filterMaterias"
                />

                <FormEnace
                  v-else-if="solicitudForm.tipo_tramite === 'enace'"
                  :solicitudForm="solicitudForm"
                />

                <FormImpuesto
                  v-else-if="solicitudForm.tipo_tramite === 'impuesto'"
                  :solicitudForm="solicitudForm"
                />

                <FormProcesos
                  v-else-if="solicitudForm.tipo_tramite === 'procesos'"
                  :solicitudForm="solicitudForm"
                  :notarios="notarios"
                />

                <FormMinisterioPublico
                  v-else-if="
                    solicitudForm.tipo_tramite === 'ministerio_publico'
                  "
                  :solicitudForm="solicitudForm"
                />

                <!-- TRÁMITES GENÉRICOS -->
                <template v-else>
                  <div class="col-12 q-pa-sm">
                    <q-input
                      label="Observaciones"
                      dense
                      outlined
                      clearable
                      type="textarea"
                      rows="3"
                      v-model="solicitudForm.observaciones"
                    />
                  </div>
                </template>
              </div>
            </q-step>

            <!-- ====================================== -->
            <!-- PASO 3: REGISTRO DE CAJA Y PAGO -->
            <!-- ====================================== -->
            <q-step
              :name="3"
              title="Registro Caja"
              icon="point_of_sale"
              :done="step > 3"
              :header-nav="step > 3"
            >
              <div class="q-pa-md">
                <!-- INFORMACIÓN DEL CONCEPTO Y PRECIO -->
                <div class="row full-width q-mb-md">
                  <q-space />
                  <div class="row items-center text-body">
                    <q-icon name="description" class="q-mr-xs" />
                    <span class="text-bold">Concepto:</span>
                    <span class="q-ml-xs">{{
                      tupaBusqueda?.denominacion || "Búsqueda de documentos"
                    }}</span>
                    <q-space />
                    <span class="q-ml-md">
                      Precio:
                      <span class="text-primary">
                        {{
                          tupaBusqueda
                            ? formatNumberToSoles(Number(tupaBusqueda.costo))
                            : ""
                        }}
                      </span>
                    </span>
                  </div>
                </div>

                <!-- FORMULARIO DE PAGO -->
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
                      :rules="[
                        (val) =>
                          val === null ||
                          val === '' ||
                          Number(val) >= 0 ||
                          'Ingrese un monto válido',
                      ]"
                    />
                  </div>
                  <div class="col-12 col-md-6 row">
                    <div class="col-7">
                      <div
                        v-if="montoEntregado"
                        class="q-ma-sm text-right text-weight-bold text-subtitle1"
                      >
                        Monto Entregado:
                      </div>
                      <div
                        class="q-ma-sm text-right text-weight-bold text-subtitle1"
                      >
                        Subtotal:
                      </div>
                      <div
                        v-if="montoEntregado"
                        class="q-ma-sm text-right text-weight-bold text-subtitle1"
                      >
                        {{
                          Number(montoEntregado) <
                          redondearConDecimales(tupaBusqueda?.costo)
                            ? "Faltante:"
                            : "Vuelto:"
                        }}
                      </div>
                    </div>
                    <div class="col-5">
                      <div v-if="montoEntregado" class="q-ma-sm text-subtitle1">
                        {{ formatNumberToSoles(montoEntregado) }}
                      </div>
                      <div
                        class="q-ma-sm text-green-13 text-weight-bold text-subtitle1"
                      >
                        {{
                          formatNumberToSoles(
                            redondearConDecimales(tupaBusqueda?.costo)
                          )
                        }}
                      </div>
                      <div
                        v-if="montoEntregado"
                        class="q-ma-sm text-subtitle1"
                        :class="
                          Number(montoEntregado) <
                          redondearConDecimales(tupaBusqueda?.costo)
                            ? 'text-negative'
                            : 'text-positive'
                        "
                      >
                        {{
                          formatNumberToSoles(
                            Number(montoEntregado) -
                              redondearConDecimales(tupaBusqueda?.costo)
                          )
                        }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </q-step>

            <!-- ====================================== -->
            <!-- NAVEGACIÓN DEL STEPPER -->
            <!-- ====================================== -->
            <template v-slot:navigation>
              <q-stepper-navigation class="q-pa-sm">
                <q-btn
                  color="primary"
                  type="submit"
                  :label="step === 3 ? 'Enviar Con Pago' : 'Siguiente'"
                  :disable="
                    step === 3 &&
                    (!montoEntregado ||
                      Number(montoEntregado) <
                        redondearConDecimales(tupaBusqueda?.costo))
                  "
                  :loading="loadingSubmit"
                />
                <q-btn
                  v-if="step > 0"
                  flat
                  color="primary"
                  @click="$refs.stepper.previous()"
                  label="Regresar"
                  class="q-ml-sm"
                  :disable="loadingSubmit"
                />
                <q-btn
                  v-if="step === 3"
                  color="secondary"
                  label="Enviar sin pago"
                  @click="enviarSinPago"
                  class="q-ml-sm"
                  :disable="
                    (montoEntregado !== undefined &&
                      montoEntregado !== null &&
                      montoEntregado !== '' &&
                      Number(montoEntregado) >=
                        redondearConDecimales(tupaBusqueda?.costo)) ||
                    loadingSubmit
                  "
                  :loading="loadingSubmit"
                />
              </q-stepper-navigation>
            </template>
          </q-stepper>
        </q-form>
      </div>
    </q-card-section>

    <!-- OVERLAY DE CARGA DURANTE EL ENVÍO -->
    <q-inner-loading :showing="loadingSubmit">
      <q-spinner-gears size="50px" color="primary" />
      <div class="q-mt-md text-h6">Procesando solicitud...</div>
      <div class="q-mt-sm">Por favor espere un momento</div>
    </q-inner-loading>
  </q-card>
</template>

<script setup>
// =============================================
// IMPORTS Y DEPENDENCIAS
// =============================================
import { ref, computed, watch, onMounted, nextTick } from "vue";
import { useQuasar } from "quasar";

// Servicios
import DniService from "src/services/DniService";
import RucService from 'src/services/RucService'
import SolicitudService from "src/services/SolicitudService";
import NotarioServive from "src/services/NotarioService";
import TupaService from "src/services/TupaService";
import PagoService from "src/services/PagoService";

// Componentes
import SelectUbigeoPlus from "src/components/SelectUbigeoPlus.vue";

// Componentes de formularios específicos
import FormEscrituras from "src/components/FormEscrituras.vue";
import FormPartidas from "src/components/FormPartidas.vue";
import FormExpedientes from "src/components/FormExpedientes.vue";
import FormEnace from "src/components/FormEnace.vue";
import FormImpuesto from "src/components/FormImpuesto.vue";
import FormProcesos from "src/components/FormProcesos.vue";
import FormMinisterioPublico from "src/components/FormMinisterioPublico.vue";

// Utilidades
import {
  formatNumberToSoles,
  redondearConDecimales,
} from "src/utils/ConvertMoney";

// =============================================
// PROPS Y EMITS
// =============================================
const props = defineProps({ title: String });
const emit = defineEmits(["save"]);

// =============================================
// INSTANCIAS Y REFERENCIAS
// =============================================
const $q = useQuasar();

// =============================================
// VARIABLES REACTIVAS - ESTADO
// =============================================

// Control de steps y formulario
const step = ref(0);
const stepper = ref(null);
const formRef = ref(null);

// Estados de carga y validación
const loading = ref(false);
const loadingSubmit = ref(false);
const NoEncontroDatosPersona = ref(false);
const errorTipoPartida = ref(false);
const errorTipoExpediente = ref(false);

// Datos de pago
const montoEntregado = ref();
const precioVigente = ref();

// Datos de configuración
const notarios = ref([]);
const tupaBusqueda = ref(null);

// Variables para el rango de años de notarios
const notarioAñoInicio = ref(1801);
const notarioAñoFinal = ref(new Date().getFullYear());

// Variables para materias de expedientes
const materiasDisponibles = ref([]);
const allMaterias = ref([]);

// =============================================
// COMPUTED PROPERTIES
// =============================================

// Rango de años para notarios
const rangoAnios = computed(() => [
  notarioAñoInicio.value,
  notarioAñoFinal.value,
]);

// Validación de datos del solicitante
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


// =============================================
// COMPUTED PROPERTIES - VERSIÓN CORREGIDA
// =============================================

// Datos adicionales formateados - SOLO OBSERVACIONES
// const masDatos = computed(() => {
//   const tramite = solicitudForm.value.tipo_tramite;

//   // Para la mayoría de trámites, mas_datos son solo las observaciones
//   // Excepto para escrituras que incluyen datos específicos
//   if (tramite === 'escrituras') {
//     const parts = [];

//     // Solo incluir campos específicos si tienen valor
//     if (solicitudForm.value.sescritura) parts.push(`${solicitudForm.value.sescritura}`);
//     if (solicitudForm.value.sprotocolo) parts.push(`${solicitudForm.value.sprotocolo}`);
//     if (solicitudForm.value.sfolio) parts.push(`${solicitudForm.value.sfolio}`);
//     if (solicitudForm.value.mas_datos) parts.push(solicitudForm.value.mas_datos);

//     return parts.filter(part => part && part.trim() !== '').join(" - ");
//   }

//   // Para otros trámites, mas_datos son solo las observaciones
//   if (solicitudForm.value.mas_datos) {
//     return solicitudForm.value.mas_datos;
//   }

//   return '';
// });




const masDatos = computed(() => {
  const tramite = solicitudForm.value.tipo_tramite;
  const parts = [];

  // Siempre incluir observaciones si existen
  if (solicitudForm.value.observaciones) {
    parts.push(solicitudForm.value.observaciones);
  }

  // Si es trámite de escrituras, agregar datos adicionales
  if (tramite === 'escrituras') {
    if (solicitudForm.value.sescritura) parts.push(solicitudForm.value.sescritura);
    if (solicitudForm.value.sprotocolo) parts.push(solicitudForm.value.sprotocolo);
    if (solicitudForm.value.sfolio) parts.push(solicitudForm.value.sfolio);
    if (solicitudForm.value.mas_datos) parts.push(solicitudForm.value.mas_datos);
  } else {
    // Para otros trámites, incluir solo mas_datos si existen
    if (solicitudForm.value.mas_datos) {
      parts.push(solicitudForm.value.mas_datos);
    }
  }

  // Filtrar vacíos y unir con " - "
  return parts.filter(part => part && part.trim() !== '').join(" - ");
});





// Función auxiliar para formatear fecha unificada
function formatFechaUnificada() {
  const { anio, mes, dia } = solicitudForm.value;
  if (anio && mes && dia) {
    return `Fecha: ${dia}/${mes}/${anio}`;
  }
  return null;
}

// =============================================
// FORMULARIO PRINCIPAL
// =============================================
const solicitudForm = ref({
  // Campos básicos del solicitante
  tipo_tramite: null,
  tipo_documento: "DNI",
  num_documento: "",
  nombres: "",
  apellido_paterno: "",
  apellido_materno: "",
  nombre_completo: "",
  asunto: "",
  direccion: "",
  correo: "",
  celular: "",
  ubigeo_cod: "",

  // CAMPOS UNIFICADOS PARA TODOS LOS TRÁMITES
  anio: null,
  mes: null,
  dia: null,
  ubigeo_cod_soli: "",
  mas_datos: "",

  // Campos específicos para escrituras
  notario_id: "",
  subserie_id: "",
  otorgantes: "",
  favorecidos: "",
  bien: "",
  sfolio: "",
  sprotocolo: "",
  sescritura: "",

  // Campos para expedientes
  tipo_expediente: "",
  materia_proceso: "",
  demandante: "",
  demandado: "",
  causante: "",
  juzgado: "",
  secretario: "",

  // Campos para partidas
  tipo_partida: "",
  nombre_fallecido: "",
  nombre_nacido: "",
  nombre_esposo: "",
  nombre_esposa: "",

  // Campos para ENACE
  contrato_privado: "",
  otorgante_enace: "",
  favorecido_enace: "",
  institucion_enace: "",

  // Campos para IMPUESTO
  causante_impuesto: "",
  direccion_impuesto: "",

  // Campos para PROCESOS
  proceso_de: "",
  en_contra_de: "",
  causante_proceso: "",
  notario_proceso: "",

  // Campos para MINISTERIO PÚBLICO
  tipo_expediente_mp: "",
  caso_mp: "",
  area_mp: "",
  materia_mp: "",
  agraviado_mp: "",
  imputado_mp: "",
  fiscalia_mp: "",
  numero_caso_mp: "",
  numero_paquete_mp: "",
});

// =============================================
// OPCIONES Y CONFIGURACIONES
// =============================================

// Tipos de expediente con iconos y colores
const tiposExpediente = [
  { label: "CIVIL", value: "civil", icon: "balance", color: "blue" },
  { label: "PENAL", value: "penal", icon: "gavel", color: "red" },
];

// Definir materias por tipo de expediente
const materiasPorTipo = {
  civil: [
    {
      label: "Inscripción de Partida de Nacimiento",
      value: "inscripcion_partida_nacimiento",
    },
    {
      label: "Inscripción de Partida de Defunción",
      value: "inscripcion_partida_defuncion",
    },
    {
      label: "Inscripción de Partida de Matrimonio",
      value: "inscripcion_partida_matrimonio",
    },
    { label: "Desalojo", value: "desalojo" },
    { label: "Escritura Imperfecta", value: "escritura_imperfecta" },
    { label: "Inventario de Bienes", value: "inventario_bienes" },
    { label: "Expropiación de Propiedad", value: "expropiacion_propiedad" },
    { label: "Poder", value: "poder" },
    { label: "Otros (Civil)", value: "otros_civil" },
  ],
  penal: [
    { label: "Homicidio", value: "homicidio" },
    { label: "Lesiones", value: "lesiones" },
    { label: "Robo", value: "robo" },
    { label: "Hurto", value: "hurto" },
    { label: "Estafa", value: "estafa" },
    { label: "Otros (Penal)", value: "otros_penal" },
  ],
};

// Tipos de partida con iconos y colores
const tiposPartida = [
  {
    label: "Defunción",
    value: "defuncion",
    icon: "person_remove",
    color: "red",
  },
  {
    label: "Nacimiento",
    value: "nacimiento",
    icon: "child_friendly",
    color: "green",
  },
  { label: "Matrimonio", value: "matrimonio", icon: "favorite", color: "pink" },
];

// Tipos de trámites disponibles
const tramites = [
  {
    label: "Escrituras",
    value: "escrituras",
    icon: "description",
    color: "deep-orange",
  },
  { label: "Partidas", value: "partidas", icon: "article", color: "blue" },
  {
    label: "Expedientes",
    value: "expedientes",
    icon: "folder_open",
    color: "green",
  },
  { label: "Enace", value: "enace", icon: "school", color: "teal" },
  {
    label: "Impuesto Sucesorios",
    value: "impuesto",
    icon: "account_balance",
    color: "brown",
  },
  {
    label: "Procesos no contenciosos",
    value: "procesos",
    icon: "fact_check",
    color: "purple",
  },
  {
    label: "Ministerio Público",
    value: "ministerio_publico",
    icon: "gavel",
    color: "red",
  },
];

// =============================================
// REGLAS DE VALIDACIÓN
// =============================================
const documentoRules = [
  (val) => (val && val.length > 0) || "Ingrese número de documento",
  (val) =>
    (solicitudForm.value.tipo_documento === "RUC"
      ? val.length === 11
      : val.length === 8) ||
    `El ${solicitudForm.value.tipo_documento} debe tener ${
      solicitudForm.value.tipo_documento === "RUC" ? 11 : 8
    } dígitos`,
];

// =============================================
// FUNCIONES PRINCIPALES - INTERACCIÓN USUARIO
// =============================================

/**
 * Selecciona un tipo de trámite y avanza al siguiente paso
 */
function seleccionarTramite(tipo) {
  console.log("🎯 Seleccionando trámite:", tipo);

  limpiarCamposTramite();
  errorTipoPartida.value = false;
  errorTipoExpediente.value = false;

  solicitudForm.value.tipo_tramite = tipo;

  nextTick(async () => {
    if (tipo === "partidas") {
      console.log("🔄 Inicializando partidas...");
      await seleccionarTipoPartida("defuncion");
    } else if (tipo === "expedientes") {
      console.log("🔄 Inicializando expedientes...");
      await seleccionarTipoExpediente("civil");
    }

    console.log("✅ Trámite inicializado:", {
      tipo: solicitudForm.value.tipo_tramite,
      subTipo:
        solicitudForm.value.tipo_partida || solicitudForm.value.tipo_expediente,
    });

    if (step.value === 0) {
      siguientePaso();
    }
  });
}

/**
 * Selecciona un tipo de expediente específico
 */
function seleccionarTipoExpediente(tipo) {
  console.log("🎯 Seleccionando tipo de expediente:", tipo);

  errorTipoExpediente.value = false;
  limpiarCamposExpediente();

  nextTick(() => {
    solicitudForm.value.tipo_expediente = tipo;
    actualizarMateriasExpediente();

    if (formRef.value) {
      formRef.value.resetValidation();
    }

    console.log(
      "✅ Tipo de expediente seleccionado:",
      solicitudForm.value.tipo_expediente
    );
  });
}

/**
 * Actualiza las materias disponibles según el tipo de expediente
 */
function actualizarMateriasExpediente() {
  const tipo = solicitudForm.value.tipo_expediente;
  console.log("📋 Actualizando materias para tipo:", tipo);

  if (tipo && materiasPorTipo[tipo]) {
    materiasDisponibles.value = materiasPorTipo[tipo];
    allMaterias.value = materiasPorTipo[tipo];
    console.log("✅ Materias cargadas:", materiasDisponibles.value.length);
  } else {
    materiasDisponibles.value = [];
    allMaterias.value = [];
    console.warn("❌ No hay materias definidas para el tipo:", tipo);
  }
}

/**
 * Filtra las materias para el select
 */
function filterMaterias(val, update) {
  if (val === "") {
    update(() => {
      materiasDisponibles.value = allMaterias.value;
    });
    return;
  }

  update(() => {
    const needle = val.toLowerCase();
    materiasDisponibles.value = allMaterias.value.filter(
      (v) => v.label.toLowerCase().indexOf(needle) > -1
    );
  });
}

/**
 * Selecciona un tipo de partida específico
 */
function seleccionarTipoPartida(tipo) {
  console.log("🎯 Seleccionando tipo de partida:", tipo);

  errorTipoPartida.value = false;
  limpiarTodosLosCamposPartida();

  nextTick(() => {
    solicitudForm.value.tipo_partida = tipo;

    if (formRef.value) {
      formRef.value.resetValidation();
    }

    console.log(
      "✅ Tipo de partida seleccionado:",
      solicitudForm.value.tipo_partida
    );
  });
}

/**
 * Avanza al siguiente paso del stepper
 */
function siguientePaso() {
  if (step.value === 0 && !solicitudForm.value.tipo_tramite) {
    $q.notify({
      type: "warning",
      message: "Debe seleccionar un tipo de trámite antes de continuar.",
    });
    return;
  }
  stepper.value.next();
}

/**
 * Busca los datos del solicitante por documento
 */
async function getSolicitante() {
  loading.value = true;
  NoEncontroDatosPersona.value = false;

  try {
    const tipoDocumento = solicitudForm.value.tipo_documento;
    const numeroDocumento = solicitudForm.value.num_documento;

    let res_solicitante;

    if (tipoDocumento === 'RUC') {
      res_solicitante = await RucService.getSolicitanteRuc(numeroDocumento);

      if (res_solicitante) {
        solicitudForm.value.asunto = res_solicitante.razonSocial || '';
        solicitudForm.value.direccion = res_solicitante.direccion || '';

        const ubicacion = [
          res_solicitante.departamento,
          res_solicitante.provincia,
          res_solicitante.distrito
        ].filter(Boolean).join(' - ');

        if (ubicacion) {
          solicitudForm.value.direccion = solicitudForm.value.direccion
            ? `${solicitudForm.value.direccion} - ${ubicacion}`
            : ubicacion;
        }

        const estadoCondicion = [
          res_solicitante.estado,
          res_solicitante.condicion
        ].filter(Boolean).join(' - ');

        if (estadoCondicion) {
          solicitudForm.value.observaciones = `Estado: ${estadoCondicion}`;
        }

        if (res_solicitante.ubigeo) {
          solicitudForm.value.ubigeo_cod = res_solicitante.ubigeo;
        }

      } else {
        NoEncontroDatosPersona.value = true;
        onResetDocumento(false);
      }

    } else {
      res_solicitante = await DniService.getSolicitanteDni(numeroDocumento);

      if (res_solicitante?.existe) {
        solicitudForm.value.nombres = res_solicitante.nombres;
        solicitudForm.value.apellido_paterno = res_solicitante.apellido_paterno;
        solicitudForm.value.apellido_materno = res_solicitante.apellido_materno;
        solicitudForm.value.direccion = res_solicitante.direccion;
        solicitudForm.value.correo = res_solicitante.correo;
        solicitudForm.value.celular = res_solicitante.celular;
        solicitudForm.value.ubigeo_cod = res_solicitante.ubigeo_cod;
      } else if (res_solicitante?.message) {
        NoEncontroDatosPersona.value = true;
        onResetDocumento(false);
      } else {
        solicitudForm.value.nombres = res_solicitante.nombres;
        solicitudForm.value.apellido_paterno = res_solicitante.apellidoPaterno;
        solicitudForm.value.apellido_materno = res_solicitante.apellidoMaterno;
      }
    }

  } catch (error) {
    console.error('Error en consulta de solicitante:', error);
    NoEncontroDatosPersona.value = true;
    $q.notify({
      type: 'negative',
      message: 'Error al consultar los datos del documento'
    });
  } finally {
    loading.value = false;
  }
}

/**
 * Resetea los campos del documento
 */
function onResetDocumento(n_documento = true) {
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

// =============================================
// FUNCIONES DE LIMPIEZA Y GESTIÓN DE DATOS
// =============================================

/**
 * Limpia todos los campos de partidas
 */
function limpiarTodosLosCamposPartida() {
  console.log("🧹 Limpiando todos los campos de partida");

  const camposPartida = [
    "nombre_fallecido",
    "nombre_nacido",
    "nombre_esposo",
    "nombre_esposa",
  ];

  camposPartida.forEach((campo) => {
    if (solicitudForm.value[campo] !== undefined) {
      solicitudForm.value[campo] = "";
    }
  });

  console.log("✅ Campos de partida limpiados correctamente");
}

/**
 * Limpia todos los campos según el tipo de trámite
 */
function limpiarCamposTramite() {
  const tipoTramiteActual = solicitudForm.value.tipo_tramite;

  // LIMPIAR CAMPOS UNIFICADOS
  solicitudForm.value.anio = null;
  solicitudForm.value.mes = null;
  solicitudForm.value.dia = null;
  solicitudForm.value.ubigeo_cod_soli = "";
  solicitudForm.value.mas_datos = "";

  // Limpiar campos de expedientes si no es el trámite actual
  if (tipoTramiteActual !== "expedientes") {
    solicitudForm.value.tipo_expediente = "";
    limpiarTodosLosCamposExpediente();
  }

  // Limpiar campos de partidas si no es el trámite actual
  if (tipoTramiteActual !== "partidas") {
    solicitudForm.value.tipo_partida = "";
    limpiarTodosLosCamposPartida();
  }

  // Limpiar campos de escrituras si no es el trámite actual
  if (tipoTramiteActual !== "escrituras") {
    const camposEscrituras = [
      "notario_id",
      "subserie_id",
      "otorgantes",
      "favorecidos",
      "bien",
      "sescritura",
      "sprotocolo",
      "sfolio",
    ];
    camposEscrituras.forEach((campo) => {
      if (solicitudForm.value[campo] !== undefined) {
        solicitudForm.value[campo] = "";
      }
    });
  }

  // Limpiar campos de ENACE si no es el trámite actual
  if (tipoTramiteActual !== "enace") {
    const camposEnace = [
      "contrato_privado",
      "otorgante_enace",
      "favorecido_enace",
      "institucion_enace",
    ];
    camposEnace.forEach((campo) => {
      if (solicitudForm.value[campo] !== undefined) {
        solicitudForm.value[campo] = "";
      }
    });
  }

  // Limpiar campos de IMPUESTO si no es el trámite actual
  if (tipoTramiteActual !== "impuesto") {
    const camposImpuesto = [
      "causante_impuesto",
      "direccion_impuesto",
    ];
    camposImpuesto.forEach((campo) => {
      if (solicitudForm.value[campo] !== undefined) {
        solicitudForm.value[campo] = "";
      }
    });
  }

  // Limpiar campos de PROCESOS si no es el trámite actual
  if (tipoTramiteActual !== "procesos") {
    const camposProcesos = [
      "proceso_de",
      "en_contra_de",
      "causante_proceso",
      "notario_proceso",
    ];
    camposProcesos.forEach((campo) => {
      if (solicitudForm.value[campo] !== undefined) {
        solicitudForm.value[campo] = "";
      }
    });
  }

  // Limpiar campos de MINISTERIO PÚBLICO si no es el trámite actual
  if (tipoTramiteActual !== "ministerio_publico") {
    const camposMP = [
      "tipo_expediente_mp",
      "caso_mp",
      "area_mp",
      "materia_mp",
      "agraviado_mp",
      "imputado_mp",
      "fiscalia_mp",
      "numero_caso_mp",
      "numero_paquete_mp",
    ];
    camposMP.forEach((campo) => {
      if (solicitudForm.value[campo] !== undefined) {
        solicitudForm.value[campo] = "";
      }
    });
  }
}

/**
 * Limpia todos los campos de expedientes
 */
function limpiarTodosLosCamposExpediente() {
  console.log("🧹 Limpiando todos los campos de expediente");

  const camposExpediente = {
    strings: [
      "materia_proceso",
      "demandante",
      "demandado",
      "causante",
      "juzgado",
      "secretario",
      "ubigeo_expediente",
      "observaciones_expediente",
    ],
    numeros: ["anio_expediente", "mes_expediente", "dia_expediente"],
  };

  camposExpediente.strings.forEach((campo) => {
    if (solicitudForm.value[campo] !== undefined) {
      solicitudForm.value[campo] = "";
    }
  });

  camposExpediente.numeros.forEach((campo) => {
    if (solicitudForm.value[campo] !== undefined) {
      solicitudForm.value[campo] = null;
    }
  });

  console.log("✅ Campos de expediente limpiados correctamente");
}

/**
 * Limpia campos específicos de expedientes
 */
function limpiarCamposExpediente() {
  const tipoActual = solicitudForm.value.tipo_expediente;

  if (!tipoActual) return;

  solicitudForm.value.materia_proceso = "";
  console.log("✅ Campos específicos de expediente limpiados");
}

// =============================================
// FUNCIONES DE VALIDACIÓN
// =============================================

// =============================================
// FUNCIONES DE VALIDACIÓN - VERSIÓN CORREGIDA
// =============================================

/**
 * Función auxiliar para validar el paso actual
 */
async function validarPasoActual() {
  switch (solicitudForm.value.tipo_tramite) {
    case "partidas":
      return validarPartidas();
    case "expedientes":
      return validarExpedientes();
    case "escrituras":
      return validarEscrituras();
    case "enace":
      return validarEnace();
    case "impuesto":
      return validarImpuesto();
    case "procesos":
      return validarProcesos();
    case "ministerio_publico":
      return validarMinisterioPublico();
    default:
      return true;
  }
}

/**
 * Valida los campos específicos de partidas
 */
function validarPartidas() {
  if (solicitudForm.value.tipo_tramite !== "partidas") return true;

  if (!solicitudForm.value.tipo_partida) {
    errorTipoPartida.value = true;
    $q.notify({
      type: "warning",
      message: "Debe seleccionar un tipo de partida",
      position: "top",
    });
    return false;
  }

  const tipo = solicitudForm.value.tipo_partida;
  let errores = [];

  // Validaciones específicas según el tipo de partida
  if (tipo === "defuncion") {
    if (!solicitudForm.value.nombre_fallecido?.trim()) {
      errores.push("Ingrese el nombre del fallecido");
    }
  }

  if (tipo === "nacimiento") {
    if (!solicitudForm.value.nombre_nacido?.trim()) {
      errores.push("Ingrese el nombre del nacido");
    }
  }

  if (tipo === "matrimonio") {
    if (!solicitudForm.value.nombre_esposo?.trim()) {
      errores.push("Ingrese el nombre del esposo");
    }
    if (!solicitudForm.value.nombre_esposa?.trim()) {
      errores.push("Ingrese el nombre de la esposa");
    }
  }

  // Campos requeridos para todos los tipos de partida
  if (!solicitudForm.value.anio || !solicitudForm.value.mes || !solicitudForm.value.dia) {
    errores.push("Complete la fecha");
  }
  if (!solicitudForm.value.ubigeo_cod_soli) {
    errores.push("Seleccione la municipalidad de registro");
  }

  // NOTA: Las observaciones (mas_datos) NO son obligatorias

  if (errores.length > 0) {
    $q.notify({
      type: "warning",
      message: errores.join("\n"),
      position: "top",
      multiLine: true,
    });
    return false;
  }

  return true;
}

/**
 * Valida los campos específicos de expedientes
 */
function validarExpedientes() {
  if (solicitudForm.value.tipo_tramite !== "expedientes") return true;

  if (!solicitudForm.value.tipo_expediente) {
    errorTipoExpediente.value = true;
    $q.notify({
      type: "warning",
      message: "Debe seleccionar un tipo de expediente",
      position: "top",
    });
    return false;
  }

  const errores = [];

  // Campos requeridos para expedientes
  if (!solicitudForm.value.materia_proceso?.trim()) {
    errores.push("Seleccione la materia del proceso");
  }
  if (!solicitudForm.value.demandante?.trim()) {
    errores.push("Ingrese el nombre del demandante");
  }
  if (!solicitudForm.value.demandado?.trim()) {
    errores.push("Ingrese el nombre del demandado");
  }
  if (!solicitudForm.value.juzgado?.trim()) {
    errores.push("Ingrese el nombre del juzgado");
  }

  if (!solicitudForm.value.ubigeo_cod_soli) {
    errores.push("Seleccione el lugar del expediente");
  }
  if (!solicitudForm.value.anio || !solicitudForm.value.mes || !solicitudForm.value.dia) {
    errores.push("Complete la fecha del expediente");
  }

  // NOTA: Las observaciones (mas_datos) NO son obligatorias

  if (errores.length > 0) {
    $q.notify({
      type: "warning",
      message: errores.join("\n"),
      position: "top",
      multiLine: true,
    });
    return false;
  }

  return true;
}

/**
 * Valida los campos específicos de escrituras
 */
function validarEscrituras() {
  if (solicitudForm.value.tipo_tramite !== "escrituras") return true;

  const errores = [];

  // Campos requeridos para escrituras
  if (!solicitudForm.value.notario_id) {
    errores.push("Debe seleccionar un notario");
  }
  if (!solicitudForm.value.otorgantes?.trim()) {
    errores.push("Ingrese los otorgantes");
  }
  if (!solicitudForm.value.favorecidos?.trim()) {
    errores.push("Ingrese los favorecidos");
  }

  if (!solicitudForm.value.ubigeo_cod_soli) {
    errores.push("Seleccione el lugar de la escritura");
  }
  if (!solicitudForm.value.anio || !solicitudForm.value.mes || !solicitudForm.value.dia) {
    errores.push("Complete la fecha de la escritura");
  }

  // NOTA: Las observaciones (mas_datos) NO son obligatorias

  if (errores.length > 0) {
    $q.notify({
      type: "warning",
      message: errores.join("\n"),
      position: "top",
      multiLine: true,
    });
    return false;
  }

  return true;
}

/**
 * Valida los campos específicos de ENACE
 */
function validarEnace() {
  if (solicitudForm.value.tipo_tramite !== "enace") return true;

  const errores = [];

  // Campos requeridos para ENACE
  if (!solicitudForm.value.contrato_privado?.trim()) {
    errores.push("Ingrese el contrato privado");
  }
  if (!solicitudForm.value.otorgante_enace?.trim()) {
    errores.push("Ingrese el nombre del otorgante");
  }
  if (!solicitudForm.value.favorecido_enace?.trim()) {
    errores.push("Ingrese el nombre del favorecido");
  }
  if (!solicitudForm.value.institucion_enace?.trim()) {
    errores.push("Ingrese el nombre de la institución");
  }

  if (!solicitudForm.value.ubigeo_cod_soli) {
    errores.push("Seleccione el lugar del contrato");
  }
  if (!solicitudForm.value.anio || !solicitudForm.value.mes || !solicitudForm.value.dia) {
    errores.push("Complete la fecha del contrato");
  }

  // NOTA: Las observaciones (mas_datos) NO son obligatorias

  if (errores.length > 0) {
    $q.notify({
      type: "warning",
      message: errores.join("\n"),
      position: "top",
      multiLine: true,
    });
    return false;
  }

  return true;
}

/**
 * Valida los campos específicos de IMPUESTO
 */
function validarImpuesto() {
  if (solicitudForm.value.tipo_tramite !== "impuesto") return true;

  const errores = [];

  // Campos requeridos para IMPUESTO
  if (!solicitudForm.value.causante_impuesto?.trim()) {
    errores.push("Ingrese el nombre del causante");
  }
  if (!solicitudForm.value.direccion_impuesto?.trim()) {
    errores.push("Ingrese la dirección del bien inmueble");
  }

  if (!solicitudForm.value.ubigeo_cod_soli) {
    errores.push("Seleccione el lugar del impuesto");
  }
  if (!solicitudForm.value.anio || !solicitudForm.value.mes || !solicitudForm.value.dia) {
    errores.push("Complete la fecha del impuesto");
  }

  // NOTA: Las observaciones (mas_datos) NO son obligatorias

  if (errores.length > 0) {
    $q.notify({
      type: "warning",
      message: errores.join("\n"),
      position: "top",
      multiLine: true,
    });
    return false;
  }

  return true;
}

/**
 * Valida los campos específicos de PROCESOS
 */
function validarProcesos() {
  if (solicitudForm.value.tipo_tramite !== "procesos") return true;

  const errores = [];

  // Campos requeridos para PROCESOS
  if (!solicitudForm.value.proceso_de?.trim()) {
    errores.push("Ingrese el proceso");
  }
  if (!solicitudForm.value.en_contra_de?.trim()) {
    errores.push("Ingrese contra quién es el proceso");
  }
  if (!solicitudForm.value.causante_proceso?.trim()) {
    errores.push("Ingrese el nombre del causante");
  }
  if (!solicitudForm.value.notario_proceso) {
    errores.push("Seleccione o ingrese un notario");
  }

  if (!solicitudForm.value.ubigeo_cod_soli) {
    errores.push("Seleccione el lugar del proceso");
  }
  if (!solicitudForm.value.anio || !solicitudForm.value.mes || !solicitudForm.value.dia) {
    errores.push("Complete la fecha del proceso");
  }

  // NOTA: Las observaciones (mas_datos) NO son obligatorias

  if (errores.length > 0) {
    $q.notify({
      type: "warning",
      message: errores.join("\n"),
      position: "top",
      multiLine: true,
    });
    return false;
  }

  return true;
}

/**
 * Valida los campos específicos de MINISTERIO PÚBLICO
 */
function validarMinisterioPublico() {
  if (solicitudForm.value.tipo_tramite !== "ministerio_publico") return true;

  const errores = [];

  // Campos requeridos para MINISTERIO PÚBLICO
  if (!solicitudForm.value.tipo_expediente_mp) {
    errores.push("Seleccione el tipo de expediente");
  }
  if (!solicitudForm.value.caso_mp?.trim()) {
    errores.push("Ingrese el caso");
  }
  if (!solicitudForm.value.area_mp?.trim()) {
    errores.push("Ingrese el área");
  }
  if (!solicitudForm.value.materia_mp?.trim()) {
    errores.push("Ingrese la materia");
  }
  if (!solicitudForm.value.agraviado_mp?.trim()) {
    errores.push("Ingrese el agraviado o denunciante");
  }
  if (!solicitudForm.value.imputado_mp?.trim()) {
    errores.push("Ingrese el imputado o denunciado");
  }
  if (!solicitudForm.value.fiscalia_mp?.trim()) {
    errores.push("Ingrese la fiscalía");
  }
  if (!solicitudForm.value.numero_caso_mp?.trim()) {
    errores.push("Ingrese el número de caso");
  }
  if (!solicitudForm.value.numero_paquete_mp?.trim()) {
    errores.push("Ingrese el número de paquete");
  }

  if (!solicitudForm.value.ubigeo_cod_soli) {
    errores.push("Seleccione el lugar del expediente");
  }
  if (!solicitudForm.value.anio || !solicitudForm.value.mes || !solicitudForm.value.dia) {
    errores.push("Complete la fecha del expediente");
  }

  // NOTA: Las observaciones (mas_datos) NO son obligatorias

  if (errores.length > 0) {
    $q.notify({
      type: "warning",
      message: errores.join("\n"),
      position: "top",
      multiLine: true,
    });
    return false;
  }

  return true;
}

/**
 * Función auxiliar para validar el paso actual
 */
// async function validarPasoActual() {
//   switch (solicitudForm.value.tipo_tramite) {
//     case "partidas":
//       return validarPartidas();
//     case "expedientes":
//       return validarExpedientes();
//     case "escrituras":
//       return validarEscrituras();
//     case "enace":
//       return validarEnace();
//     case "impuesto":
//       return validarImpuesto();
//     case "procesos":
//       return validarProcesos();
//     case "ministerio_publico":
//       return validarMinisterioPublico();
//     default:
//       return true;
//   }
// }

// =============================================
// FUNCIONES DE NEGOCIO - NOTARIOS Y UBIGEO
// =============================================

/**
 * Asigna el ubigeo del notario seleccionado
 */
// function asignarUbigeoNotario() {
//   console.log("🔄 Asignando ubigeo del notario...");

//   const notarioSeleccionado = notarios.value.find(
//     (n) => String(n.id) === String(solicitudForm.value.notario_id)
//   );

//   console.log("📋 Notario seleccionado:", notarioSeleccionado);

//   if (notarioSeleccionado) {
//     const codUbigeo = notarioSeleccionado.ubigeo_cod || "";
//     solicitudForm.value.ubigeo_cod_soli = codUbigeo;

//     notarioAñoInicio.value = notarioSeleccionado.año_inicio ?? 1801;
//     notarioAñoFinal.value =
//       notarioSeleccionado.año_final ?? new Date().getFullYear();

//     console.log("✅ Ubigeo y rango de años actualizado:", {
//       notario: notarioSeleccionado.nombre_completo,
//       ubigeo: codUbigeo,
//       año_inicio: notarioAñoInicio.value,
//       año_final: notarioAñoFinal.value,
//     });
//   } else {
//     solicitudForm.value.ubigeo_cod_soli = "";
//     notarioAñoInicio.value = 1801;
//     notarioAñoFinal.value = new Date().getFullYear();
//     console.log("🔄 Notario no seleccionado, ubigeo y rango de años limpiados");
//   }
// }

// =============================================
// FUNCIONES DE PAGO Y ENVÍO
// =============================================


  /**
   * Asigna el ubigeo del notario seleccionado
   */
  async function asignarUbigeoNotario() {
    console.log("🔄 Asignando ubigeo del notario...");

    if (!solicitudForm.value.notario_id) {
      console.warn("⚠ No hay notario seleccionado");
      return;
    }

    const notarioSeleccionado = notarios.value.find(
      (n) => String(n.id) === String(solicitudForm.value.notario_id)
    );

    console.log("📋 Notario seleccionado:", notarioSeleccionado);

    if (notarioSeleccionado && notarioSeleccionado.ubigeo_cod) {
      solicitudForm.value.ubigeo_cod_soli = notarioSeleccionado.ubigeo_cod;

      notarioAñoInicio.value = notarioSeleccionado.año_inicio ?? 1801;
      notarioAñoFinal.value =
        notarioSeleccionado.año_final ?? new Date().getFullYear();

      console.log("✅ Ubigeo y rango de años actualizado:", {
        notario: notarioSeleccionado.nombre_completo,
        ubigeo: notarioSeleccionado.ubigeo_cod,
        año_inicio: notarioAñoInicio.value,
        año_final: notarioAñoFinal.value,
      });
    } else {
      console.warn("⚠ Notario no encontrado o sin ubigeo");
      solicitudForm.value.ubigeo_cod_soli = "";
    }
  }
/**
 * Carga los datos del TUPA de búsqueda
 */
async function cargarTupaBusqueda() {
  const resp = await TupaService.getData({ params: { sub_code: "0101" } });
  tupaBusqueda.value = Array.isArray(resp.data) ? resp.data[0] : resp.data;
}

/**
 * Obtiene el precio vigente del trámite
 */
async function getPrecioVigente() {
  const costo = Number(tupaBusqueda.value?.costo ?? 0);
  precioVigente.value = costo;
  solicitudForm.value.precio = redondearConDecimales(costo);
}


/**
 * Maneja la validación exitosa del formulario
 */
async function ValidaSuccess(event, currentStep) {
  try {
    if (currentStep === 2) {
      // Validar campos específicos del trámite antes de avanzar
      if (!(await validarPasoActual())) {
        return;
      }
      event.next();
    } else if (currentStep === 3) {
      // Validar campos específicos del trámite antes de enviar
      if (!(await validarPasoActual())) {
        return;
      }
      await procesarEnvioConPago();
    } else {
      event.next();
    }
  } catch (error) {
    console.error("Error en validación:", error);
    $q.notify({
      type: "negative",
      message: "Error en la validación del formulario",
    });
  }
}
/**
 * Procesa el envío con pago - VERSIÓN CORREGIDA
 */
async function procesarEnvioConPago() {
  loadingSubmit.value = true;

  try {
    console.log("🚀 Iniciando procesamiento con pago...");

    // Validar que el monto sea suficiente
    const montoRequerido = redondearConDecimales(tupaBusqueda.value?.costo);
    if (!montoEntregado.value || Number(montoEntregado.value) < montoRequerido) {
      $q.notify({
        type: "negative",
        message: `El monto entregado (${formatNumberToSoles(montoEntregado.value)}) es menor al requerido (${formatNumberToSoles(montoRequerido)})`
      });
      loadingSubmit.value = false;
      return;
    }

    // Validar campos específicos del trámite
    if (!(await validarPasoActual())) {
      loadingSubmit.value = false;
      return;
    }

    // Preparar el nombre completo del solicitante
    if (solicitudForm.value.tipo_documento === "DNI") {
      solicitudForm.value.nombre_completo =
        `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
    } else {
      solicitudForm.value.nombre_completo = solicitudForm.value.asunto;
    }

    // Asignar mas_datos y ubigeo
    solicitudForm.value.mas_datos = masDatos.value;
    if (solicitudForm.value.tipo_tramite === 'escrituras') {
      await asignarUbigeoNotario();
    }

    console.log("📤 Preparando datos para envío con pago...");

    // Preparar datos de envío
    const datosEnvio = {
      // Campos base REQUERIDOS
      tipo_tramite: solicitudForm.value.tipo_tramite,
      tipo_documento: solicitudForm.value.tipo_documento,
      num_documento: solicitudForm.value.num_documento,
      nombres: solicitudForm.value.nombres,
      apellido_paterno: solicitudForm.value.apellido_paterno,
      apellido_materno: solicitudForm.value.apellido_materno,
      nombre_completo: solicitudForm.value.nombre_completo,
      asunto: solicitudForm.value.asunto,
      direccion: solicitudForm.value.direccion,
      correo: solicitudForm.value.correo,
      celular: solicitudForm.value.celular,
      ubigeo_cod: solicitudForm.value.ubigeo_cod,

      // Campos unificados
      anio: solicitudForm.value.anio,
      mes: solicitudForm.value.mes,
      dia: solicitudForm.value.dia,
      ubigeo_cod_soli: solicitudForm.value.ubigeo_cod_soli,
      mas_datos: solicitudForm.value.mas_datos,
      con_pago: true, // ✅ CON PAGO

      // Campos específicos según el tipo de trámite
      ...(solicitudForm.value.tipo_tramite === 'partidas' && {
        tipo_partida: solicitudForm.value.tipo_partida,
        nombre_fallecido: solicitudForm.value.nombre_fallecido,
        nombre_nacido: solicitudForm.value.nombre_nacido,
        nombre_esposo: solicitudForm.value.nombre_esposo,
        nombre_esposa: solicitudForm.value.nombre_esposa
      }),

      ...(solicitudForm.value.tipo_tramite === 'escrituras' && {
        notario_id: solicitudForm.value.notario_id,
        subserie_id: solicitudForm.value.subserie_id,
        otorgantes: solicitudForm.value.otorgantes,
        favorecidos: solicitudForm.value.favorecidos,
        bien: solicitudForm.value.bien,
        sescritura: solicitudForm.value.sescritura,
        sprotocolo: solicitudForm.value.sprotocolo,
        sfolio: solicitudForm.value.sfolio
      }),

      ...(solicitudForm.value.tipo_tramite === 'expedientes' && {
        tipo_expediente: solicitudForm.value.tipo_expediente,
        materia_proceso: solicitudForm.value.materia_proceso,
        demandante: solicitudForm.value.demandante,
        demandado: solicitudForm.value.demandado,
        causante: solicitudForm.value.causante,
        juzgado: solicitudForm.value.juzgado,
        secretario: solicitudForm.value.secretario
      }),

      ...(solicitudForm.value.tipo_tramite === 'enace' && {
        contrato_privado: solicitudForm.value.contrato_privado,
        otorgante_enace: solicitudForm.value.otorgante_enace,
        favorecido_enace: solicitudForm.value.favorecido_enace,
        institucion_enace: solicitudForm.value.institucion_enace
      }),

      ...(solicitudForm.value.tipo_tramite === 'impuesto' && {
        causante_impuesto: solicitudForm.value.causante_impuesto,
        direccion_impuesto: solicitudForm.value.direccion_impuesto
      }),

      ...(solicitudForm.value.tipo_tramite === 'procesos' && {
        proceso_de: solicitudForm.value.proceso_de,
        en_contra_de: solicitudForm.value.en_contra_de,
        causante_proceso: solicitudForm.value.causante_proceso,
        notario_proceso: solicitudForm.value.notario_proceso
      }),

      ...(solicitudForm.value.tipo_tramite === 'ministerio_publico' && {
        tipo_expediente_mp: solicitudForm.value.tipo_expediente_mp,
        caso_mp: solicitudForm.value.caso_mp,
        area_mp: solicitudForm.value.area_mp,
        materia_mp: solicitudForm.value.materia_mp,
        agraviado_mp: solicitudForm.value.agraviado_mp,
        imputado_mp: solicitudForm.value.imputado_mp,
        fiscalia_mp: solicitudForm.value.fiscalia_mp,
        numero_caso_mp: solicitudForm.value.numero_caso_mp,
        numero_paquete_mp: solicitudForm.value.numero_paquete_mp
      })
    };

    console.log("📦 Datos listos para enviar con pago:", datosEnvio);

    // Guardar la solicitud
    const request = await SolicitudService.save(datosEnvio);
    let solicitudId = request?.data?.id || request?.id;

    console.log("✅ Solicitud guardada, ID:", solicitudId);

    // Si no se obtiene el ID, intentar obtenerlo de la lista de solicitudes
    if (!solicitudId) {
      try {
        console.log("🔄 Buscando ID de solicitud en lista...");
        const listaSolicitudes = await SolicitudService.getData();
        if (Array.isArray(listaSolicitudes.data)) {
          const ordenadas = listaSolicitudes.data.sort((a, b) => b.id - a.id);
          if (ordenadas.length > 0) {
            solicitudId = ordenadas[0].id;
            console.log("✅ ID obtenido de lista:", solicitudId);
          }
        }
      } catch (e) {
        console.error("❌ Error obteniendo ID de lista:", e);
        solicitudId = null;
      }
    }

    // Si no hay solicitudId, no se puede continuar con el pago
    if (!solicitudId) {
      $q.notify({
        type: "negative",
        message: "No se pudo obtener el ID de la solicitud. El pago no se guardará.",
      });
      loadingSubmit.value = false;
      return;
    }

    // Preparar datos del pago
    let userId = null;
    try {
      const user = JSON.parse(localStorage.getItem("user"));
      userId = user?.id || user?.user?.id || null;
      console.log("👤 User ID:", userId);
    } catch (e) {
      console.error("❌ Error obteniendo user ID:", e);
      userId = null;
    }

    let nombreCompletoPago = "";
    if (solicitudForm.value.tipo_documento === "DNI") {
      nombreCompletoPago =
        `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
    } else {
      nombreCompletoPago = solicitudForm.value.asunto;
    }

    // Calcular monto pagado y vuelto usando la misma lógica del template
    const totalRedondeado = redondearConDecimales(tupaBusqueda.value?.costo);
    const montoPagado = Number(montoEntregado.value);
    const vuelto = montoPagado - totalRedondeado;

    const pagoPayload = {
      solicitud_id: solicitudId,
      tipo_documento: solicitudForm.value.tipo_documento,
      num_documento: solicitudForm.value.num_documento,
      nombre_completo: nombreCompletoPago,
      total: Number(tupaBusqueda.value.costo),
      user_id: userId,
      estado: 0, // ✅ Pagado
      monto_pagado: montoPagado, // ← AÑADIDO
      vuelto: vuelto, // ← AÑADIDO
      tupas: [
        {
          tupa_id: tupaBusqueda.value.id,
          cantidad: 1,
          Subtotal: Number(tupaBusqueda.value.costo),
          precio: Number(tupaBusqueda.value.costo),
          denominacion: tupaBusqueda.value.denominacion,
        },
      ],
      desde_solicitud: true,
      con_pago: true, // ✅ Con pago
    };

    console.log("💰 Guardando pago:", pagoPayload);

    // Guardar el pago
    const pagoResponse = await PagoService.save(pagoPayload);
    const pagoId = pagoResponse?.data?.id || pagoResponse?.id;

    console.log("✅ Pago guardado, ID:", pagoId);

    // Actualizar la solicitud con el pago de búsqueda
    if (pagoId && solicitudId) {
      console.log("🔄 Actualizando solicitud con pago...");
      await SolicitudService.update(solicitudId, {
        pago_busqueda: pagoId,
        estado: "Buscando",
        area_id: 2,
      });
      console.log("✅ Solicitud actualizada correctamente");
    }



    $q.notify({
      type: "positive",
      message: "Solicitud enviada con pago correctamente",
      position: "top",
      timeout: 3000
    });

    console.log("🎉 Proceso completado exitosamente");
    emit("save", "solicitud");

  } catch (error) {
    console.error("❌ Error en procesar envío:", error);

    let errorMessage = "Error al procesar el pago";

    if (error.response?.data?.errors) {
      errorMessage = Object.values(error.response.data.errors).flat().join("\n");
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }

    $q.notify({
      type: "negative",
      message: errorMessage,
      position: "top",
      timeout: 5000
    });
  } finally {
    loadingSubmit.value = false;
  }
}

/**
 * Envía la solicitud sin procesar el pago - VERSIÓN CORREGIDA
 */
async function enviarSinPago() {
  loadingSubmit.value = true;

  try {
    console.log("🚀 Iniciando envío sin pago...");

    // Validar campos específicos del trámite
    if (!(await validarPasoActual())) {
      loadingSubmit.value = false;
      return;
    }

    // Preparar el nombre completo del solicitante
    if (solicitudForm.value.tipo_documento === "DNI") {
      solicitudForm.value.nombre_completo =
        `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
    } else {
      solicitudForm.value.nombre_completo = solicitudForm.value.asunto;
    }

    // Asignar mas_datos y ubigeo
    solicitudForm.value.mas_datos = masDatos.value;
    if (solicitudForm.value.tipo_tramite === 'escrituras') {
      await asignarUbigeoNotario();
    }

    console.log("📤 Preparando datos para envío sin pago...");

    // Preparar datos de envío
    const datosEnvio = {
      // Campos base REQUERIDOS
      tipo_tramite: solicitudForm.value.tipo_tramite,
      tipo_documento: solicitudForm.value.tipo_documento,
      num_documento: solicitudForm.value.num_documento,
      nombres: solicitudForm.value.nombres,
      apellido_paterno: solicitudForm.value.apellido_paterno,
      apellido_materno: solicitudForm.value.apellido_materno,
      nombre_completo: solicitudForm.value.nombre_completo,
      asunto: solicitudForm.value.asunto,
      direccion: solicitudForm.value.direccion,
      correo: solicitudForm.value.correo,
      celular: solicitudForm.value.celular,
      ubigeo_cod: solicitudForm.value.ubigeo_cod,

      // Campos unificados
      anio: solicitudForm.value.anio,
      mes: solicitudForm.value.mes,
      dia: solicitudForm.value.dia,
      ubigeo_cod_soli: solicitudForm.value.ubigeo_cod_soli,
      mas_datos: solicitudForm.value.mas_datos,
      con_pago: false, // ✅ SIN PAGO

      // Campos específicos según el tipo de trámite
      ...(solicitudForm.value.tipo_tramite === 'partidas' && {
        tipo_partida: solicitudForm.value.tipo_partida,
        nombre_fallecido: solicitudForm.value.nombre_fallecido,
        nombre_nacido: solicitudForm.value.nombre_nacido,
        nombre_esposo: solicitudForm.value.nombre_esposo,
        nombre_esposa: solicitudForm.value.nombre_esposa
      }),

      ...(solicitudForm.value.tipo_tramite === 'escrituras' && {
        notario_id: solicitudForm.value.notario_id,
        subserie_id: solicitudForm.value.subserie_id,
        otorgantes: solicitudForm.value.otorgantes,
        favorecidos: solicitudForm.value.favorecidos,
        bien: solicitudForm.value.bien,
        sescritura: solicitudForm.value.sescritura,
        sprotocolo: solicitudForm.value.sprotocolo,
        sfolio: solicitudForm.value.sfolio
      }),

      ...(solicitudForm.value.tipo_tramite === 'expedientes' && {
        tipo_expediente: solicitudForm.value.tipo_expediente,
        materia_proceso: solicitudForm.value.materia_proceso,
        demandante: solicitudForm.value.demandante,
        demandado: solicitudForm.value.demandado,
        causante: solicitudForm.value.causante,
        juzgado: solicitudForm.value.juzgado,
        secretario: solicitudForm.value.secretario
      }),

      ...(solicitudForm.value.tipo_tramite === 'enace' && {
        contrato_privado: solicitudForm.value.contrato_privado,
        otorgante_enace: solicitudForm.value.otorgante_enace,
        favorecido_enace: solicitudForm.value.favorecido_enace,
        institucion_enace: solicitudForm.value.institucion_enace
      }),

      ...(solicitudForm.value.tipo_tramite === 'impuesto' && {
        causante_impuesto: solicitudForm.value.causante_impuesto,
        direccion_impuesto: solicitudForm.value.direccion_impuesto
      }),

      ...(solicitudForm.value.tipo_tramite === 'procesos' && {
        proceso_de: solicitudForm.value.proceso_de,
        en_contra_de: solicitudForm.value.en_contra_de,
        causante_proceso: solicitudForm.value.causante_proceso,
        notario_proceso: solicitudForm.value.notario_proceso
      }),

      ...(solicitudForm.value.tipo_tramite === 'ministerio_publico' && {
        tipo_expediente_mp: solicitudForm.value.tipo_expediente_mp,
        caso_mp: solicitudForm.value.caso_mp,
        area_mp: solicitudForm.value.area_mp,
        materia_mp: solicitudForm.value.materia_mp,
        agraviado_mp: solicitudForm.value.agraviado_mp,
        imputado_mp: solicitudForm.value.imputado_mp,
        fiscalia_mp: solicitudForm.value.fiscalia_mp,
        numero_caso_mp: solicitudForm.value.numero_caso_mp,
        numero_paquete_mp: solicitudForm.value.numero_paquete_mp
      })
    };

    console.log("📦 Datos listos para enviar sin pago:", datosEnvio);

    // Guardar la solicitud
    const request = await SolicitudService.save(datosEnvio);
    let solicitudId = request?.data?.id || request?.id;

    console.log("✅ Solicitud guardada, ID:", solicitudId);

    // Si no se obtiene el ID, intentar obtenerlo de la lista de solicitudes
    if (!solicitudId) {
      try {
        console.log("🔄 Buscando ID de solicitud en lista...");
        const listaSolicitudes = await SolicitudService.getData();
        if (Array.isArray(listaSolicitudes.data)) {
          const ordenadas = listaSolicitudes.data.sort((a, b) => b.id - a.id);
          if (ordenadas.length > 0) {
            solicitudId = ordenadas[0].id;
            console.log("✅ ID obtenido de lista:", solicitudId);
          }
        }
      } catch (e) {
        console.error("❌ Error obteniendo ID de lista:", e);
        solicitudId = null;
      }
    }

    // Preparar datos del pago (sin pago, pero se crea un registro de pago pendiente)
    let userId = null;
    try {
      const user = JSON.parse(localStorage.getItem("user"));
      userId = user?.id || user?.user?.id || null;
      console.log("👤 User ID:", userId);
    } catch (e) {
      console.error("❌ Error obteniendo user ID:", e);
      userId = null;
    }

    let nombreCompletoPago = "";
    if (solicitudForm.value.tipo_documento === "DNI") {
      nombreCompletoPago =
        `${solicitudForm.value.apellido_paterno} ${solicitudForm.value.apellido_materno} ${solicitudForm.value.nombres}`.trim();
    } else {
      nombreCompletoPago = solicitudForm.value.asunto;
    }

    const pagoPayload = {
      solicitud_id: solicitudId,
      tipo_documento: solicitudForm.value.tipo_documento,
      num_documento: solicitudForm.value.num_documento,
      nombre_completo: nombreCompletoPago,
      total: Number(tupaBusqueda.value.costo),
      user_id: userId,
      estado: 1, // ✅ Pendiente de pago
      tupas: [
        {
          tupa_id: tupaBusqueda.value.id,
          cantidad: 1,
          Subtotal: Number(tupaBusqueda.value.costo),
          precio: Number(tupaBusqueda.value.costo),
          denominacion: tupaBusqueda.value.denominacion,
        },
      ],
      desde_solicitud: true,
      con_pago: false, // ✅ Sin pago
    };

    console.log("💰 Guardando pago pendiente:", pagoPayload);

    // Guardar el pago pendiente
    await PagoService.save(pagoPayload);

    $q.notify({
      type: "positive",
      message: "Solicitud enviada sin pago correctamente",
      position: "top",
      timeout: 3000
    });

    console.log("🎉 Proceso sin pago completado exitosamente");
    emit("save", "solicitud");

  } catch (error) {
    console.error("❌ Error en enviar sin pago:", error);

    let errorMessage = "Error al enviar la solicitud sin pago";

    if (error.response?.data?.errors) {
      errorMessage = Object.values(error.response.data.errors).flat().join("\n");
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }

    $q.notify({
      type: "negative",
      message: errorMessage,
      position: "top",
      timeout: 5000
    });
  } finally {
    loadingSubmit.value = false;
  }
}




/**
 * Maneja errores de validación
 */
function ValidaError(step) {
  console.log(`Error de validación en paso ${step}`);
}

/**
 * Maneja el envío del formulario
 */
function onSubmit() {
  // El envío real se maneja en ValidaSuccess
}

// =============================================
// WATCHERS - REACTIVIDAD
// =============================================

watch(
  () => solicitudForm.value.notario_id,
  (newNotarioId) => {
    console.log("🔄 Cambio en notario_id:", { nuevo: newNotarioId });

    solicitudForm.value.anio = null;
    solicitudForm.value.mes = null;
    solicitudForm.value.dia = null;

    if (!notarios.value.length) {
      notarioAñoInicio.value = 1801;
      notarioAñoFinal.value = new Date().getFullYear();
      return;
    }

    const notarioSeleccionado = notarios.value.find(
      (n) => String(n.id) === String(newNotarioId)
    );
    const codUbigeo = notarioSeleccionado?.ubigeo_cod || "";
    solicitudForm.value.ubigeo_cod_soli = codUbigeo;

    if (newNotarioId) {
      const notarioSeleccionado = notarios.value.find(
        (n) => n.id === newNotarioId
      );
      if (notarioSeleccionado) {
        notarioAñoInicio.value = notarioSeleccionado.año_inicio ?? 1801;
        notarioAñoFinal.value =
          notarioSeleccionado.año_final ?? new Date().getFullYear();
      } else {
        notarioAñoInicio.value = 1801;
        notarioAñoFinal.value = new Date().getFullYear();
      }
    } else {
      notarioAñoInicio.value = 1801;
      notarioAñoFinal.value = new Date().getFullYear();
    }

    nextTick(() => {
      if (formRef.value) {
        formRef.value.resetValidation();
      }
    });
  },
  { immediate: true }
);

watch(
  () => solicitudForm.value.tipo_expediente,
  (nuevoValor, valorAnterior) => {
    if (nuevoValor !== valorAnterior) {
      console.log("Tipo expediente cambió de", valorAnterior, "a", nuevoValor);

      if (valorAnterior) {
        solicitudForm.value.materia_proceso = "";
      }

      actualizarMateriasExpediente();

      nextTick(() => {
        if (formRef.value) {
          formRef.value.resetValidation();
        }
      });
    }
  },
  { immediate: false }
);

watch(
  () => solicitudForm.value.tipo_partida,
  (nuevoValor, valorAnterior) => {
    if (nuevoValor !== valorAnterior) {
      console.log("Tipo partida cambió de", valorAnterior, "a", nuevoValor);
      nextTick(() => {
        if (formRef.value) {
          formRef.value.resetValidation();
        }
      });
    }
  },
  { immediate: false }
);

watch(
  () => solicitudForm.value.tipo_tramite,
  (nuevoValor, valorAnterior) => {
    if (nuevoValor !== valorAnterior) {
      console.log("Tipo trámite cambió de", valorAnterior, "a", nuevoValor);
      limpiarCamposTramite();

      if (nuevoValor === "partidas") {
        nextTick(() => {
          seleccionarTipoPartida("defuncion");
        });
      }

      if (nuevoValor === "expedientes") {
        nextTick(() => {
          seleccionarTipoExpediente("civil");
        });
      }
    }
  }
);

// =============================================
// LIFECYCLE HOOKS
// =============================================

/**
 * Hook de montaje del componente
 */
onMounted(async () => {
  console.log("🚀 Inicializando componente...");

  await cargarTupaBusqueda();
  await getPrecioVigente();
  await cargarNotarios();

  actualizarMateriasExpediente();

  console.log("✅ Componente inicializado correctamente");
});

/**
 * Carga los notarios
 */
async function cargarNotarios() {
  try {
    console.log("📋 Cargando lista de notarios...");

    const response = await NotarioServive.getData({
      params: {
        rowsPerPage: 0,
        estado: "activo",
      },
    });

    if (response?.data?.length > 0) {
      notarios.value = response.data.map((notario) => ({
        ...notario,
        año_inicio: parseInt(notario.año_inicio) || 1801,
        año_final: parseInt(notario.año_final) || new Date().getFullYear(),
        ubigeo_cod: notario.ubigeo_cod || "",
      }));

      console.log("✅ Notarios cargados:", notarios.value.length);
      console.log("📊 Ejemplo de notario:", notarios.value[0]);
    } else {
      notarios.value = [];
      console.warn("⚠ No se encontraron notarios activos");
    }
  } catch (error) {
    console.error("❌ Error cargando notarios:", error);
    notarios.value = [];
    $q.notify({
      type: "warning",
      message: "No se pudieron cargar los notarios.",
    });
  }
}

// =============================================
// EXPOSICION DE MÉTODOS
// =============================================
defineExpose({
  setValue: (values) => {
    // Implementar si es necesario
  },
});
</script>

<style>
/* ============================================= */
/* ESTILOS GENERALES */
/* ============================================= */

/* Efectos hover para cards */
.hover-card {
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}
.hover-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

/* Estilos para cards de trámites */
.tramite-card {
  cursor: pointer;
  border-radius: 12px;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}
.tramite-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}
.tramite-selected {
  border: 2px solid #1976d2 !important;
  box-shadow: 0 8px 16px rgba(25, 118, 210, 0.4);
}

/* ============================================= */
/* ESTILOS PARA BOTONES DE TIPO DE PARTIDA */
/* ============================================= */

.tipo-partida-card {
  cursor: pointer;
  border-radius: 12px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  min-height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tipo-partida-default {
  background-color: #f8f9fa;
  border-color: #e9ecef;
}

.tipo-partida-default:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #007bff;
}

.tipo-partida-selected {
  background: linear-gradient(135deg, #007bff, #0056b3);
  border-color: #007bff;
  box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
  transform: translateY(-2px);
}

/* ============================================= */
/* EFECTOS Y ANIMACIONES */
/* ============================================= */

/* Efectos de hover suaves */
.hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

/* Animaciones suaves para formularios */
.form-transition {
  transition: all 0.3s ease;
}

/* Mejora visual para títulos de sección */
.section-title {
  border-left: 4px solid #007bff;
  padding-left: 12px;
  margin-bottom: 20px;
}

/* ============================================= */
/* ESTILOS PARA BOTONES DE TIPO DE EXPEDIENTE */
/* ============================================= */

.tipo-expediente-card {
  cursor: pointer;
  border-radius: 12px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  min-height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tipo-expediente-default {
  background-color: #f8f9fa;
  border-color: #e9ecef;
}

.tipo-expediente-default:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #007bff;
}

.tipo-expediente-selected {
  background: linear-gradient(135deg, #007bff, #0056b3);
  border-color: #007bff;
  box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
  transform: translateY(-2px);
}

/* Estilos específicos para materias de expedientes */
.materia-civil {
  border-left: 4px solid #1976d2;
}

.materia-penal {
  border-left: 4px solid #d32f2f;
}

/* ============================================= */
/* ESTILOS UNIFICADOS PARA SELECCIONES */
/* ============================================= */

/* Estilos base para cards de selección */
.tipo-seleccion-card {
  cursor: pointer;
  border-radius: 12px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  min-height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tipo-seleccion-default {
  background-color: #f8f9fa;
  border-color: #e9ecef;
}

.tipo-seleccion-default:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #007bff;
}

.tipo-seleccion-selected {
  background: linear-gradient(135deg, #007bff, #0056b3);
  border-color: #007bff;
  box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
  transform: translateY(-2px);
}

/* Aplicar a todos los tipos */
.tipo-partida-card,
.tipo-expediente-card,
.tramite-card {
  @extend .tipo-seleccion-card;
}

.tipo-partida-default,
.tipo-expediente-default {
  @extend .tipo-seleccion-default;
}

.tipo-partida-selected,
.tipo-expediente-selected,
.tramite-selected {
  @extend .tipo-seleccion-selected;
}

/* Colores específicos para cada tipo */
.tipo-partida-selected {
  background: linear-gradient(135deg, #28a745, #20c997);
}
.tipo-expediente-selected {
  background: linear-gradient(135deg, #17a2b8, #138496);
}
.tramite-selected {
  background: linear-gradient(135deg, #007bff, #0056b3);
}
</style>
