<template>
  <q-input v-model="modelMes" :label="label" bottom-slots mask="Aaaaaaaaa" :readonly="readonly" lazy-rules @click="readonly ? modelpopup=false : modelpopup=true"
    :rules="requerido ? [val => ( val && val !== '' && inMes(val)) || 'Ingrese un Mes'] : [val => val===null || val === '' || inMes(val) || 'Ingrese un Mes válido']">
    <template v-slot:label> {{label}} <span v-if="requerido" class="text-red-7 text-weight-bold">(*)</span></template>
    <template v-slot:counter>
        <q-popup-proxy v-model="modelpopup" cover transition-show="scale" transition-hide="scale">
            <div class="col-12 row q-pa-sm" style="max-width: 280px;">
                <div v-for="mes, index in listMes" :key="index" class="col-4 row items-center justify-center">
                    <span :class="PermitirMes(index+1) ? modelMes === mes ?'selectMes':$q.dark.isActive?'darkMes':'Mes':'noSelectMes'" class="classMes" 
                        @click="PermitirMes(index+1)?asignar(mes):null"> {{ mes }} </span>
                </div>
            </div>
            <div class="row justify-end"><q-btn v-close-popup label="Cerrar" color="primary" flat /></div>
        </q-popup-proxy>
    </template>
  </q-input>
</template>

<script setup>
import { convertDate } from 'src/utils/ConvertDate';
import { onBeforeMount, ref, watch } from 'vue';
const emit = defineEmits(['update:modelValue']);
const props = defineProps({
  modelValue: { default:null },
  modelAnio: { default:null },
  RangoFechas: { default: ['01/01/1901', convertDate(new Date(),'dd/MM/yyyy')] },
  requerido: { default: false },
  readonly: { default: false },
  label: { default: 'Mes' },
});
const modelMes = ref(null);
const modelpopup = ref(false);
const listMes = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];
let auxiMes = null;
let auxiAnio = null;
function asignar(mes){
    modelMes.value = mes; 
    modelpopup.value = false;
}
onBeforeMount(()=>{
  if (props.modelValue && !isNaN(parseInt(props.modelValue))) {
      auxiMes = parseInt(props.modelValue);
      modelMes.value = listMes[auxiMes-1];
  };
  if (props.modelAnio && !isNaN(parseInt(props.modelAnio)))
      auxiAnio = parseInt(props.modelAnio);
});
watch(()=>modelMes.value,(newval,oldval) => {
  if(newval && listMes.includes(newval))
    emit('update:modelValue', listMes.indexOf(newval)+1);
  else
    emit('update:modelValue',null);
});
watch(()=>props.modelValue,(newval,oldval)=>{
  if (auxiMes !== newval && !isNaN(parseInt(newval))) {
    auxiMes = parseInt(newval);
    modelMes.value = listMes[auxiMes-1];
  } else if (auxiMes !== newval) {
    auxiMes = newval;
    modelMes.value = newval;
  }
});
/**** cambios de año ************************************ */
watch(()=>props.modelAnio,(newval,oldval)=>{
    if (!isNaN(parseInt(newval)) && auxiAnio !== parseInt(newval)) {
        auxiAnio = parseInt(newval);
        if(!PermitirMes(auxiMes)){
            auxiMes = null;
            modelMes.value = null;
        }
    }else if (newval === null){
        auxiAnio = null;
        auxiMes = null;
        modelMes.value = null;
    }
});
/***  validación de años **************************** */
const [anio_ini, anio_fin] = props.RangoFechas ? props.RangoFechas.map(fecha => parseInt(fecha.split('/')[2])) : [null,null];
/*** validacion de mees***************************************** */
function inMes(val) {
    return listMes.includes(val);
}
const [mes_ini, mes_fin] = anio_ini && anio_fin ? props.RangoFechas.map(fecha => parseInt(fecha.split('/')[1])) : [null,null];
function PermitirMes (index_mes){
  return !(auxiAnio && mes_ini && mes_fin && index_mes &&
    ((auxiAnio === anio_ini && index_mes < mes_ini) ||
    (auxiAnio === anio_fin && index_mes > mes_fin)));
}
</script>

<style lang="sass" scoped>
.classMes 
  padding: 4px
  width: 80px
  text-align: center
  border-radius: 10px 10px 10px 10px
  margin: 0px
  cursor: pointer
.Mes:hover
  background-color: $grey-3
.darkMes:hover
  background-color: $grey-9
.selectMes 
  background-color: $primary
  color: white
.noSelectMes 
  color: $grey-6
  cursor: no-drop
</style>