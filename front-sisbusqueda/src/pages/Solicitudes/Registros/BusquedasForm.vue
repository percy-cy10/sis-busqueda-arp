<template>
    <!-- content -->
    <q-card class="my-card" style="width: 1400px; max-width: 80vw">
      <q-card-section class="bg-primary text-white row">
        <div class="text-h6 row items-center">{{ title }}</div>
        <q-space />
        <q-btn icon="close" color="negative" round  v-close-popup />
      </q-card-section>
      <q-form @submit.prevent="submit">
        <div class="row">
          <q-card-section class="col-12 col-lg-7 row q-pl-md">
            <q-btn-toggle v-model="opcionVista" spread no-caps toggle-color="primary" color="white" text-color="grey" class="col-12"
              :options="[ {label: 'Datos', value: 1}, {label: 'Ver en PDF', value: 2}]" />
            <div v-if="opcionVista === 1" class="full-width row">
              <template v-for="(item) in [
                  { label: 'SOLICITANTE', value: D_solicitud?.solicitante.tipo_documento === 'DNI' ? 'Nombres:' : 'Asunto:', data: D_solicitud?.solicitante.tipo_documento === 'DNI' ? D_solicitud?.solicitante.nombre_completo : D_solicitud?.solicitante.asunto },
                  { label: 'DATOS DEL DOCUMENTO', value: 'Escritura Pública:', data: D_solicitud?.sub_serie.nombre },
                  { label: null, value: 'Notario:', data: D_solicitud?.notario.nombre_completo },
                  { label: null, value: 'Otorgantes:', data: D_solicitud?.otorgantes },
                  { label: null, value: 'Favorecidos:', data: D_solicitud?.favorecidos },
                  { label: null, value: 'Lugar y Fecha:', data: `${D_solicitud?.ubigeo.nombre}, Año:${D_solicitud?.anio} Mes:${D_solicitud?.mes} Día:${D_solicitud?.dia}` },
                  { label: null, value: 'Bien:', data: D_solicitud?.bien },
                  { label: null, value: 'Otros Datos:', data: D_solicitud?.mas_datos }
                ]" :key="item">
                <div v-if="item.label" class="col-12 q-py-sm text-weight-bold text-subtitle2">{{ item.label }}</div>
                <div class="col-12 col-sm-3 items-center row q-py-sm q-pl-sm text-weight-bold">{{ item.value }}</div>
                <div class="col-12 col-sm-9 items-center row q-py-sm q-pl-sm">{{ item.data }}</div>
              </template>
            </div>
            <div v-else class="full-width">
              <GenerarPDFSolicitud :datosSolicitudRow="D_solicitud" :ver="true" height="450px"/>
            </div>
          </q-card-section>
          <q-card-section class="col-12 col-lg-5 q-pa-md">
            <div class="col-12 q-py-sm text-weight-bold text-subtitle1">Sugerencias Encontradas</div>
            <SugerenciasIntersection :notario_id="D_solicitud.notario_id" @sugerencia="DarSugerencia($event)" style="max-height: 150px;"/>
            <div class="col-12 q-py-sm text-weight-bold text-subtitle1">Registro de busqueda</div>
            <div class="row q-mb-md">
              <q-input class="col-12 col-sm-6 q-pa-sm" :class="form.invalid('cod_protocolo') ? 'q-mb-sm' : ''" dense outlined
                  v-model="form.cod_protocolo" label="Protocolo" mask="P-######" :loading="form.validating"
                  @change="form.validate('cod_protocolo')" :error="form.invalid('cod_protocolo')">
                  <template v-slot:label> Protocolo <span class="text-red-7 text-weight-bold">(*)</span></template>
                  <template v-slot:error> {{ form.errors.cod_protocolo }} </template>
              </q-input>
              <q-input class="col-12 col-sm-6 q-pa-sm" :class="form.invalid('cod_escritura') ? 'q-mb-sm' : ''" dense outlined
                  v-model="form.cod_escritura" label="Codigo de Escritura" mask="E-######" :loading="form.validating"
                  @change="form.validate('cod_escritura')" :error="form.invalid('cod_escritura')">
                  <template v-slot:label> Codigo de Escritura <span class="text-red-7 text-weight-bold">(*)</span></template>
                  <template v-slot:error>{{ form.errors.cod_escritura }}</template>
              </q-input>
              <q-input class="col-12 col-sm-6 q-pa-sm" :class="form.invalid('cod_folioInicial') ? 'q-mb-sm' : ''" dense outlined
                  v-model="form.cod_folioInicial" label="Codigo Folio Inicial" mask="F-######" :loading="form.validating"
                  @change="form.validate('cod_folioInicial')" :error="form.invalid('cod_folioInicial')">
                  <template v-slot:label> Folio Inicial <span class="text-red-7 text-weight-bold">(*)</span></template>
                  <template v-slot:append> 
                    <q-toggle v-model="vueltaFI" size="xm" dense checked-icon="check" color="blue" unchecked-icon="clear" :disable="deshabili_FI" /> 
                    <div class="text-caption">Vuelta</div>
                  </template>
                  <template v-slot:error> {{ form.errors.cod_folioInicial }} </template>
              </q-input>
              <q-input class="col-12 col-sm-6 q-pa-sm" :class="form.invalid('cod_folioFinal') ? 'q-mb-sm' : ''" dense outlined
                  v-model="form.cod_folioFinal" label="Codigo Folio Final" mask="F-######" :loading="form.validating"
                  @change="form.validate('cod_folioFinal')" :error="form.invalid('cod_folioFinal')">
                  <template v-slot:label> Folio Final <span class="text-red-7 text-weight-bold">(*)</span></template>
                  <template v-slot:append> 
                    <q-toggle v-model="vueltaFF" size="xm" dense checked-icon="check" color="blue" unchecked-icon="clear" :disable="deshabili_FF" /> 
                    <div class="text-caption">Vuelta</div>
                  </template>
                  <template v-slot:error> {{ form.errors.cod_folioFinal }} </template>
              </q-input>
              <q-input class="col-12 q-pa-sm" :class="form.invalid('observaciones') ? 'q-mb-sm' : ''" dense type="textarea" outlined
                  v-model="form.observaciones" label="observaciones" :loading="form.validating"
                  @change="form.validate('observaciones')" :error="form.invalid('observaciones')">
                  <template v-slot:error> {{ form.errors.observaciones }} </template>
              </q-input>
            </div>
          </q-card-section>
        </div>
        <q-separator />
        <q-card-actions align="right">
          <q-btn label="Cancelar" flat v-close-popup />
          <q-btn label="Guardar" color="positive" :loading="form.processing" type="submit" />
        </q-card-actions>
      </q-form>
    </q-card>
</template>
<script setup>
import { useForm } from "laravel-precognition-vue";
import GenerarPDFSolicitud from "src/components/GenerarPDFSolicitud.vue";
import SugerenciasIntersection from "src/components/SugerenciasIntersection.vue";
import { onMounted, ref, watch } from "vue";
const emits = defineEmits(["save"]);
const props = defineProps({
  title: String,
  D_solicitud: {default: null,},
});
const opcionVista = ref(1);

const vueltaFI = ref(false);
const vueltaFF = ref(false);

let form = useForm("post", "api/registro_busquedas", {
    solicitud_id: props.D_solicitud.id,
    cod_protocolo: "",
    cod_escritura: "",
    cod_folioInicial: "",
    cod_folioFinal: "",
    observaciones: "",
  });
onMounted(async() => {
  // await DatosSugerencia();
});
const submit = () => {
  if(vueltaFI.value) form.cod_folioInicial = CargaVueltaFolio(form.cod_folioInicial) ? form.cod_folioInicial : form.cod_folioInicial+' V';
  if(vueltaFF.value) form.cod_folioFinal = CargaVueltaFolio(form.cod_folioFinal) ? form.cod_folioFinal : form.cod_folioFinal+' V';
  form.submit()
    .then((response) => {
      // form.reset();
      // form.setData()
      // console.log(response);
      emits("save",'busqueda');
    }).catch((error) => {
      // alert(error);
    });
};
function CargaVueltaFolio(dato){
  return dato && /[vV]/.test(dato);
}

function DarSugerencia(datos){
  console.log(datos);
  form.cod_protocolo = datos.libro.protocolo;
  form.cod_escritura = datos.escritura.cod_escritura;
  vueltaFI.value = CargaVueltaFolio(datos.escritura.cod_folioInicial);
  vueltaFF.value = CargaVueltaFolio(datos.escritura.cod_folioFinal);
  form.cod_folioInicial = datos.escritura.cod_folioInicial;
  form.cod_folioFinal = datos.escritura.cod_folioFinal;
}
const deshabili_FI = ref(form.cod_folioInicial === '')
watch(()=>form.cod_folioInicial,(newVal,oldVal)=>{
  if(newVal===''){
    vueltaFI.value = false;
    deshabili_FI.value = true;
  }else
    deshabili_FI.value = false;
});
const deshabili_FF = ref(form.cod_folioFinal === '')
watch(()=>form.cod_folioFinal,(newVal,oldVal)=>{
  if(newVal===''){
    vueltaFF.value = false;
    deshabili_FF.value = true;
  }else
    deshabili_FF.value = false;
});
defineExpose({
  form,
});
</script>
<style scoped>
  .my-card{
    width: 100%;
    max-width: 80vw;
  }
</style>
  