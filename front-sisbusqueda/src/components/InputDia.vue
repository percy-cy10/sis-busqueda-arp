<template>
  <q-input v-model="modelDia" :label="label" mask="##" :readonly="readonly" lazy-rules
    :rules="requerido?[val => (val!==null && val!=='' && val >= 1 && val <= cantidadDiasMes) || 'Ingrese un Día valido']:[val => val===null || val==='' || (val >= 1 && val <= cantidadDiasMes) || 'Ingrese un Día valido']">
    <template v-slot:label> {{label}} <span v-if="requerido" class="text-red-7 text-weight-bold">(*)</span></template>
    <template v-slot:append>
      <q-icon icon name="event" class="cursor-pointer" @click="readonly?modelpopup=true : modelpopup=false">
        <q-popup-proxy v-model="modelpopup" cover transition-show="scale" transition-hide="scale">
          <q-date v-model="modeldate" minimal emit-immediately :default-year-month="defaulYearMonth"
            @navigation="eventSelctFecha($event)" 
            :navigation-min-year-month="convertDate(RangoFechas[0],'yyyy/MM')"
            :navigation-max-year-month="convertDate(RangoFechas[1],'yyyy/MM')"
            :options="ValidarFecha">
            <div class="row items-center justify-end">
              <q-btn v-close-popup label="Cerrar" color="primary" flat />
            </div>
          </q-date>
        </q-popup-proxy>
      </q-icon>
    </template>
  </q-input>
</template>

<script setup>
import { convertDate } from 'src/utils/ConvertDate';
import { computed, onBeforeMount, ref, watch } from 'vue';
const emit = defineEmits(['update:modelValue','update:modelAnio','update:modelMes']);
const props = defineProps({
    modelValue: {default:null},
    modelMes: {default:null},
    modelAnio: {default:null},
    RangoFechas: { default: ['01/01/1901', convertDate(new Date(),'dd/MM/yyyy')] },
    requerido: { default: false },
    readonly: { default: false },
    label: { default: 'Día' },
});

const modelDia = ref(null);
const modeldate = ref(null);
const modelpopup = ref(false);

let auxiAnio = null;
let auxiMes = null;
let auxiDia = null;

onBeforeMount(()=>{
    if (props.modelValue){
        auxiDia = parseInt(props.modelValue)
        modelDia.value = auxiDia;
    }
    if (props.modelAnio){
        auxiAnio = parseInt(props.modelAnio);
    }
    if (props.modelMes){
        auxiMes = parseInt(props.modelMes);
    }
});
function eventSelctFecha(event) {
    if (auxiAnio !== event.year){
        auxiAnio = event.year;
        emit('update:modelAnio', auxiAnio);
    }
    if (auxiMes !== event.month){
        auxiMes = event.month;
        emit('update:modelMes', auxiMes);
    }
}
const cantidadDiasMes = computed(()=>{
  const fecha = new Date(parseInt(props.modelAnio), parseInt(props.modelMes) - 1, 1);
  fecha.setMonth(fecha.getMonth() + 1);
  fecha.setDate(fecha.getDate() - 1);
  return fecha.getDate();
});
function GeneraFecha(año,mes,dia){
    if(año && mes && dia && dia>=1 && dia<=cantidadDiasMes.value)
        return `${año}/${(mes + '').padStart(2,'0')}/${(dia + '').padStart(2,'0')}`
    else
        return null;
}
watch(()=>props.modelValue,(newval,oldval)=>{
    if (newval && !isNaN(parseInt(newval)) && auxiDia!==parseInt(newval)) {
        modeldate.value = GeneraFecha(auxiAnio,auxiMes,auxiDia);
    }else if (isNaN(parseInt(newval))){
        modeldate.value = null;
    }
});
watch(()=>modelDia.value,(newval,oldval)=>{
  if (newval && auxiDia!==parseInt(newval)) {
    auxiDia = parseInt(newval);
    modeldate.value = GeneraFecha(auxiAnio,auxiMes,auxiDia);
    emit('update:modelValue', parseInt(newval));
  }else if (isNaN(parseInt(newval))){
    auxiDia = null;
    modeldate.value = null;
    emit('update:modelValue', null);
  }
});
watch(()=>modeldate.value,(newval,oldval) => {
  if(newval && parseInt(newval.split('/')[2]) !== auxiDia){
      modelDia.value = parseInt(newval.split('/')[2]);
      modelpopup.value = false;
  } else if (newval === null){
      modelDia.value = null;
  }
});
watch(()=>props.modelAnio,(newval,oldval)=>{
  if (auxiAnio !== newval) {
    auxiAnio = newval;
  } 
});
watch(()=>props.modelMes,(newval,oldval)=>{
  if (newval && auxiMes !== newval) {
    auxiMes = newval;
  } else if (newval === null){
    modelDia.value = null;
  }
});
const defaulYearMonth = computed(() => {
  if ( validaAnio(props.modelAnio) && props.modelMes)
    return props.modelAnio + '/' + (props.modelMes + '').padStart(2, '0') || '';
  else if (validaAnio(props.modelAnio))
    return `${props.modelAnio}/00`;
  return `${new Date().getFullYear()}/00`;
});

/***  validación de años**************************** */
const [inicio, fin] = props.RangoFechas.map(fecha => parseInt(fecha.split('/')[2]));
function validaAnio(val) {
  return val && val !== '' && val >= inicio && val <= fin;
}
function ValidarFecha(date){
    return date >= convertDate(props.RangoFechas[0],'yyyy/MM/dd') && date <= convertDate(props.RangoFechas[1],'yyyy/MM/dd');
}
</script>

<style lang="sass" scoped>

</style>