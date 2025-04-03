<template>
    <!-- content -->
    <q-card class="my-card" style="width: 1400px; max-width: 80vw">
      <q-card-section class="bg-primary text-white row">
        <div class="text-h6">{{title}}</div>
        <q-space />

        <q-btn icon="close" color="negative" round  v-close-popup />
      </q-card-section>
      <q-card-section class="q-pa-none">
        <div class="col-xs-12 col-sm-8 q-pa-sm">
          <q-form @submit="onSubmit" @validation-error="ValidaError(step)"
              @validation-success="ValidaSuccess($refs.stepper, step)">
            <q-stepper v-model="step" ref="stepper" color="primary" header-nav animated flat bordered>
              <q-step :name="1" title="Registro Solicitante" icon="settings"
                :done="step > 1" :header-nav="step > 1">
                <div class="text-subtitle2">Tipo de Docuemnto</div>
                <q-option-group v-model="solicitudForm.tipo_documento" inline @update:model-value="onReset"
                    :options="[ { label: 'DNI', value: 'DNI' }, { label: 'RUC', value: 'RUC' },]"/>
                <q-input outlined lazy-rules dense v-model="solicitudForm.num_documento" class="q-pa-sm"
                    :label="solicitudForm.tipo_documento" :mask="solicitudForm.tipo_documento === 'RUC' ?'###########':'########'"
                    :rules="solicitudForm.tipo_documento === 'RUC' ?[(val) => (val && val !== '' && val.length === 11) || 'Por favor ingrese el RUC']:[(val) => (val && val !== '' && val.length === 8) || 'Por favor ingrese el DNI',]" >
                    <template v-slot:label> {{ solicitudForm.tipo_documento }} <span class="text-red-7 text-weight-bold">(*)</span></template>
                    <template v-slot:after>
                      <q-btn :label="$q.screen.lt.sm ? '' : 'Buscar'" @click="getSolicitante"
                        color="primary" icon-right="search" />
                    </template>
                </q-input>
                <div v-if="NoEncontroDatosPersona"> No se encontraron datos</div>
                <q-tab-panels v-model="solicitudForm.tipo_documento" class="q-mt-md">
                  <q-tab-panel name="DNI" class="q-pa-none q-mb-md row">
                    <q-input class="col-12 col-md-4 q-pa-sm" label="Primer Apellido" dense outlined
                      v-model="solicitudForm.apellido_paterno" :loading="loading" lazy-rules
                      :rules="[(val) => (val && val !== '') || 'Por favor ingrese Primer Apellido',]">
                      <template v-slot:label> Primer Apellido <span class="text-red-7 text-weight-bold">(*)</span></template>
                    </q-input>
                    <q-input class="col-12 col-md-4 q-pa-sm" label="Segundo Apellido" dense outlined 
                      v-model="solicitudForm.apellido_materno" :loading="loading" lazy-rules
                      :rules="[(val) => (val && val !== '') || 'Por favor ingrese Segundo Apellido',]">
                      <template v-slot:label> Segundo Apellido <span class="text-red-7 text-weight-bold">(*)</span></template>
                    </q-input>
                    <q-input class="col-12 col-md-4 q-pa-sm" label="Nombres" dense outlined
                      v-model="solicitudForm.nombres" :loading="loading" lazy-rules
                      :rules="[(val) => (val && val !== '') || 'Por favor ingrese nombres',]">
                      <template v-slot:label> Nombres <span class="text-red-7 text-weight-bold">(*)</span></template>
                    </q-input>
                  </q-tab-panel>
                  <q-tab-panel name="RUC" class="q-pa-none q-mb-md">
                    <q-input class="col-12 col-md-6 q-pa-sm" label="Asunto" dense outlined
                        v-model="solicitudForm.asunto" :loading="loading" lazy-rules
                        :rules="[(val) => (val && val !== '') || 'Por favor ingrese Asunto',]">
                      <template v-slot:label> Asunto <span class="text-red-7 text-weight-bold">(*)</span></template>
                    </q-input>
                  </q-tab-panel>
                </q-tab-panels>

                <div v-if="okSolicitante"  class="row">
                  <div class="row full-width">
                    <SelectUbigeoPlus Class="q-pa-sm col-12 col-sm-6 col-md-4" dense outlined clearable
                      v-model="solicitudForm.ubigeo_cod" :loading="loading" :requerido="true" />
                  </div>
                  <q-input class="col-12 col-md-4 q-pa-sm" label="Celular" dense outlined clearable
                        v-model="solicitudForm.celular" mask="### ### ###"/>
                  <q-input class="col-12 col-md-4 q-pa-sm" label="Correo Electrónico" dense outlined
                      v-model="solicitudForm.correo" lazy-rules :rules="[(val) => val === null || val === '' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Por favor ingrese un correo electrónico válido']"/>
                  <q-input class="col-12 col-md-4 q-pa-sm" label="Direccion - Domicilio" dense outlined clearable
                      v-model="solicitudForm.direccion" />
                </div>

              </q-step>

              <q-step :name="2" title="Registrar Solicitud" icon="create_new_folder"
                :done="step > 2" :header-nav="step > 2" >
                <div class="q-mb-md row">
                  
                  <SelectInput class="col-12 col-md-6 q-pa-sm" label="Notarios" dense outlined clearable lazy-rules
                      v-model="solicitudForm.notario_id" :rules="[(val) => (val && val !== '') || 'Por favor selecciones Notario',]"
                      :options="NotarioServive" OptionLabel="nombre_completo" OptionValue="id" :requerido="true"/>
                  <SelectInput class="col-12 col-md-6 q-pa-sm" label="Subserie" dense outlined clearable
                      v-model="solicitudForm.subserie_id" 
                      :options="SubSerieService" OptionLabel="nombre" OptionValue="id"/>
                  <InputTextSelect class="col-12 col-md-6 q-pa-sm" dense outlined clearable
                      v-model="solicitudForm.otorgantes" label="Otorgante" :requerido="true"
                      :options="OtorganteService" OptionLabel="nombre_completo" OptionValue="nombre_completo"/>
                  <InputTextSelect class="col-12 col-md-6 q-pa-sm" dense outlined clearable
                      v-model="solicitudForm.favorecidos" label="Favorecido" :requerido="true"
                      :options="FavorecidoService" OptionLabel="nombre_completo" OptionValue="nombre_completo"/>
                  <div class="row full-width">
                    <SelectUbigeoPlus cod_departamento="21" Class="q-pa-sm col-12 col-md-6" dense outlined clearable
                      v-model="solicitudForm.ubigeo_cod_soli" :requerido="true" />
                  </div>
                  <div class="col-12 col-md-6 q-pa-sm row">
                    <InputAnio :requerido="true" class="col-12 col-sm-4" dense outlined
                        v-model="solicitudForm.anio" />
                    <InputMes :readonly="solicitudForm.anio===null" class="col-12 col-sm-4" dense outlined clearable
                        v-model="solicitudForm.mes" :modelAnio="solicitudForm.anio"/>
                    <InputDia :readonly="solicitudForm.anio===null || solicitudForm.mes===null" class="col-12 col-sm-4" dense outlined
                        v-model="solicitudForm.dia" v-model:modelAnio="solicitudForm.anio" v-model:modelMes="solicitudForm.mes"/>
                  </div>
                  <q-input class="col-12 col-md-6 q-pa-sm" dense outlined clearable
                      v-model="solicitudForm.bien" label="Nombre del Bien"/>
                    <q-input dense outlined clearable class="col-12 col-md-4 q-pa-sm"
                        v-model="solicitudForm.sescritura" label="Escritura" mask="E-######" />
                    <q-input dense outlined clearable class="col-12 col-md-4 q-pa-sm"
                        v-model="solicitudForm.sprotocolo" label="Protocolo" mask="P-######" />
                    <q-input dense outlined clearable class="col-12 col-md-4 q-pa-sm"
                        v-model="solicitudForm.sfolio" label="Folio" mask="F-######" />
                </div>
              </q-step>
              <q-step :name="3" title="Registro Caja" icon="point_of_sale"
                :done="step > 3" :header-nav="step > 3" >
                  <div class="row full-width">
                    <q-space/>
                    <div class="row items-center text-body">Precio por Busqueda: {{ formatNumberToSoles(precioVigente) }}</div>
                  </div>
                  <div class="row">
                    <div class="col-12 col-md-6">
                      <q-input class="q-pa-sm" mask="###" prefix="s/" dense outlined clearable
                          v-model="montoEntregado" label="Monto entregado"/>
                    </div>
                    <div class="col-12 col-md-6 row">
                      <div class="col-7">
                        <div v-if="montoEntregado" class="q-ma-sm text-right text-weight-bold text-subtitle1">Monto Entregado :</div>
                        <div class="q-ma-sm text-right text-weight-bold text-subtitle1">Subtotal :</div>
                        <div v-if="montoEntregado" class="q-ma-sm text-right text-weight-bold text-subtitle1">Monto de Cambio (vuelto) :</div>
                      </div>
                      <div class="col-5">
                        <div v-if="montoEntregado" class="q-ma-sm text-subtitle1">{{ montoEntregado?formatNumberToSoles(montoEntregado):'' }}</div>
                        <div class="q-ma-sm text-green-13 text-weight-bold text-subtitle1">{{formatNumberToSoles(redondearConDecimales(precioVigente))}}</div>
                        <div v-if="montoEntregado" class="q-ma-sm text-yellow-9 text-subtitle1">{{ montoEntregado?formatNumberToSoles(montoEntregado - redondearConDecimales(precioVigente)):'' }}</div>
                      </div>
                    </div>
                  </div>
              </q-step>
              <template v-slot:navigation>
                <q-stepper-navigation>
                  <q-btn color="primary" type="submit" :label="step === 3?'Enviar':'Siguiente'" />
                  <q-btn
                  v-if="step > 1"
                    flat
                    color="primary"
                    @click="$refs.stepper.previous()"
                    label="Regresar"
                    class="q-ml-sm"
                    />
                </q-stepper-navigation>
              </template>
            </q-stepper>
          </q-form>
        </div>
        <!-- </div> -->
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
import OtorganteService from "src/services/OtorganteService";
import FavorecidoService from "src/services/FavorecidoService";
import SelectUbigeoPlus from "src/components/SelectUbigeoPlus.vue"
import SelectInput from "src/components/SelectInput.vue";
import { formatNumberToSoles, redondearConDecimales } from "src/utils/ConvertMoney"
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
const props = defineProps ({
  title: String,
});
const solicitudForm = ref({
    //parte de solicitante ************
    id:null,
    nombres: '',
    apellido_paterno: '',
    apellido_materno: '',
    nombre_completo: '',
    asunto: '',
    tipo_documento: 'DNI',
    num_documento: "",
    direccion: "",
    correo: "",
    celular: "",
    ubigeo_cod: "",
    //parte de solicitud **************
    notario_id:'',
    subserie_id:'',
    otorgantes: "",
    favorecidos: "",
    anio: null,
    mes: null,
    dia: null,
    ubigeo_cod_soli: null,
    bien: "",
    mas_datos: "",
    //datos para generar PDF **********
    precio:'',
    sfolio: '',
    sprotocolo: '',
    sescritura: '',
  });

async function getPrecioVigente(){
  precioVigente.value = (await TupaService.getData({params: {precio_sub_code:'0101'}}));
  solicitudForm.value.precio = redondearConDecimales(precioVigente.value);
}

onMounted(async()=>{
  getPrecioVigente();
});
const okSolicitante = computed(() => {
  if (solicitudForm.value.tipo_documento === 'DNI') {
    return (
      solicitudForm.value.nombres !== "" &&
      solicitudForm.value.apellido_materno !== "" &&
      solicitudForm.value.apellido_paterno !== "" &&
      solicitudForm.value.num_documento !== ""
    );
  } else {
    return solicitudForm.value.asunto !== "" && solicitudForm.value.num_documento !== "";
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
  return ( 
    solicitudForm.value.sfolio +
    " " +
    solicitudForm.value.sescritura +
    " " +
    solicitudForm.value.sprotocolo 
  );
});

const loading = ref(false);
const NoEncontroDatosPersona = ref(false);

async function getSolicitante() {
    loading.value = true;
    try {
      let res_solicitante = await DniService.getSolicitanteDni(solicitudForm.value.num_documento);
      if (res_solicitante?.existe) {
        NoEncontroDatosPersona.value = false;
        solicitudForm.value.nombres = res_solicitante.nombres;
        solicitudForm.value.apellido_paterno = res_solicitante.apellido_paterno;
        solicitudForm.value.apellido_materno = res_solicitante.apellido_materno;
        solicitudForm.value.direccion = res_solicitante.direccion;
        solicitudForm.value.correo = res_solicitante.correo;
        solicitudForm.value.celular = res_solicitante.celular;
        solicitudForm.value.ubigeo_cod = res_solicitante.ubigeo_cod;
      }else if (res_solicitante?.message) {
        console.log(res_solicitante.message);
        NoEncontroDatosPersona.value = true;
        onReset(false);
      } else {
        NoEncontroDatosPersona.value = false;
        solicitudForm.value.nombres = res_solicitante.nombres;
        solicitudForm.value.apellido_paterno = res_solicitante.apellidoPaterno;
        solicitudForm.value.apellido_materno = res_solicitante.apellidoMaterno;
      }
    } catch (error) {
      console.log(error.response?.data.errors);
      NoEncontroDatosPersona.value = true;
    }
    loading.value = false;
}

function onReset(n_documento = true) {
    solicitudForm.value.cargo = null;
    if (n_documento) {
      solicitudForm.value.num_documento = "";
    }
    solicitudForm.value.nombres = '';
    solicitudForm.value.apellido_paterno = '';
    solicitudForm.value.apellido_materno = '';
    solicitudForm.value.nombre_completo = '';
    solicitudForm.value.asunto = '';
    solicitudForm.value.correo = null;
    solicitudForm.value.celular = null;
    solicitudForm.value.direccion = null;
    solicitudForm.value.ubigeo_cod = null;
}

const onSubmit = async () => {
  // console.log('subm');
};
async function ValidaSuccess(event, step) {
  if (step === 3) {
    try {
      solicitudForm.value.nombre_completo = nombreCompleto.value;
      solicitudForm.value.mas_datos = masDatos.value;
      console.log(step,solicitudForm.value.nombre_completo);
      const request = await SolicitudService.save(solicitudForm.value);
      emit("save",'solicitud');
    } catch (error) {
      console.log(error.response?.data.errors);
    }
  } else if (step === 2) {
    event.next();
  } else {
    event.next();
  }
}
function ValidaError(step) {
  // console.log('Error:',step);
}

function setValue(values) {
  // solicitudForm.value = values;
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
  solicitudForm.value.mas_datos = values.mas_datos;
}

defineExpose({
    // setData,
    setValue,
  });
</script>
<style></style>
