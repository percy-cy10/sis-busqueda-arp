<template>
  <q-input v-model="modelAño" :label="label" mask="####" bottom-slots lazy-rules
      :rules="requerido ? [val => (val && val !== '' && validaAnio(val)) || 'Ingrese un Año valido ['+inicio+','+fin+']'] : [val => val===null || val==='' || validaAnio(val)  || 'Ingrese un Año valido ['+inicio+','+fin+']']">
      <template v-slot:label> {{label}} <span v-if="requerido" class="text-red-7 text-weight-bold">(*)</span></template>
      <template v-slot:append>
        <q-icon icon name="event" class="cursor-pointer" @click="modelpopup=true">
          <span style="max-width: 160px;">
            <q-popup-proxy v-model="modelpopup" cover transition-show="scale" transition-hide="scale">
                <q-carousel v-model="modelslide" control-color="blue" transition-prev="scale" transition-next="scale" animated arrows padding
                    style="max-width: 20em; max-height: 15em;" class="shadow-1 rounded-borders">
                    <q-carousel-slide v-for="slideanio, index in anios" :key="index" :name="index" class="row">
                        <div v-for="anio, ind in slideanio" :key="ind" class="col-3 items-center row" style="height: 30px;">
                            <div :class="parseInt(modelAño) === anio?'selectAnio':$q.dark.isActive?'darkAnio':'Anio'" class="classAnio"
                                @click="modelAño = anio" v-close-popup>
                                {{ anio }}
                            </div>
                        </div>
                    </q-carousel-slide>
                </q-carousel>
                <div class="row justify-end"><q-btn v-close-popup label="Cerrar" color="primary" flat /></div>
            </q-popup-proxy>
          </span>
        </q-icon>
      </template>
  </q-input>
</template>

<script setup>
import { computed, onBeforeMount, ref, watch } from 'vue';
import { convertDate } from 'src/utils/ConvertDate';
const emit = defineEmits(['update:modelValue']);
const props = defineProps({
  modelValue: { default:null },
  RangoAnios: { default: [null, null] },
  RangoFechas: { default: ['01/01/1901', convertDate(new Date(),'dd/MM/yyyy')] },
  requerido: { default: false },
  label: { default: 'Año' },
});
const modelAño = ref(null);

const modelpopup = ref(false);
let auxiAnio = null;
const [inicio, fin] = props.RangoAnios[0]?props.RangoAnios:props.RangoFechas.map(fecha => parseInt(fecha.split('/')[2]));
onBeforeMount(()=>{
  if (props.modelValue) modelAño.value=props.modelValue;
});
const anios = computed(() => {
  const aniosArray = [];
  for (let year = inicio; year <= fin; year++) {
    aniosArray.push(year);
  }
  const rows = Math.ceil(aniosArray.length / 20);
  const matrix = new Array(rows);
  for (let i = 0; i < rows; i++) {
    matrix[i] = aniosArray.slice(i * 20, (i + 1) * 20);
  }
  return matrix;
});
const modelslide = ref(parseInt(anios.value.length/2));
watch(()=>modelAño.value,(newval,oldval) => {
  modelslide.value = encontrarSlide((parseInt(newval)));
  if(!isNaN(parseInt(newval)) && parseInt(newval)!==auxiAnio ) {
    auxiAnio = parseInt(newval);
    emit('update:modelValue', parseInt(newval) );
  }else if (newval===null || newval==='') {
    auxiAnio = null;
    emit('update:modelValue', null);
  }
});
watch(()=>props.modelValue,(newval,oldval) => {
  if(newval !== auxiAnio){
    modelAño.value = newval;
  }
});
function validaAnio(val) {
  return val >= inicio && val <= fin;
}
function encontrarSlide(año){
  const canti = anios.value.length;
  if (!isNaN(año)){
    let cantiSlide = 0;
    for (let i=0;i<canti;i++){
      cantiSlide = anios.value[i].length;
      if (anios.value[i][cantiSlide-1] >= año)
        return i;
    }
  }
  return parseInt(canti/2);
}
</script>
<style lang="sass" scoped>
.classAnio 
  padding: 4px
  height: 25px
  text-align: center
  border-radius: 20%
  margin: 0px
  cursor: pointer
.Anio:hover
  background-color: $grey-3
.darkAnio:hover
  background-color: $grey-9
.selectAnio 
  background-color: $primary
  color: white
// .q-field--with-bottom
//   padding-bottom: 0px !important
</style>