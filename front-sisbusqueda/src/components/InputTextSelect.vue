<template>
  <div class="recomen" ref="recomendacion">
    <q-input :outlined="outlined" :dense="dense" :clearable="clearable" :label="label" :loading="loading"
      v-model="model" :class="Class" :rules="requerido?[val => (val && val !== '')|| 'Por favor ingrese '+label]:[]"
      @update:model-value="emitir(model)">
      <template v-slot:label> {{label}} <span v-if="requerido" class="text-red-7 text-weight-bold">(*)</span></template>
    </q-input>
    <div v-if="show" class="posicion shadow-box shadow-6">
      <div v-for="val, index in filterOptions" :key="index" @click="SelectItem(val)" :class="$q.dark.isActive ? 'select-dark':'select'" class="cursor-pointer q-px-md q-py-sm">
        <span>
          {{ typeof val === 'object'? val[op_label] : val }}
        </span>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed, onMounted, ref, watch } from 'vue';
const emit = defineEmits(['update:modelValue']);
const props = defineProps({
  label:{default:'Select recomendaciones'},
  modelValue: {default:null},
  options: {default:null},
  OptionLabel:{default:'nombre'},
  OptionValue:{default:'id'},
  ValueMulti:{default:null},
  ValueAll:{default:false},
  GenerateList :{default:null},
// aprtir de aqui son los estilos para definir
  requerido:{default:false},
  Class :{default:''},
  clearable :{default:false},
  outlined :{default:false},
  dense :{default:false},
});
let array = [];
const op_label = ref('');
const op_value = ref('');
const model = ref('');

const show = ref(false);
const loading = ref(false);

const recomendacion = ref(null);

onMounted(async () => {
  loading.value=true;
  op_label.value = props.GenerateList ? props.GenerateList.column : props.OptionLabel;
  op_value.value = props.GenerateList ? props.GenerateList.column : props.OptionValue;
  if(props.modelValue) model.value = props.modelValue;
  array = await ExtraerDatos(props.options);
  loading.value=false;
  document.addEventListener('click', ClickFueraDelRef);
});
function emitir(_model){
  if (_model && typeof _model === 'object') {
    let value = null;
    if(props.ValueMulti && typeof props.ValueMulti === 'object'){
      value = {};
      props.ValueMulti.forEach(element => {
        if (_model?.[element])
        value[element] = _model[element];
      });
    }else if(props.ValueAll){ value = _model;}
    else {value = _model[op_value.value]};
    emit('update:modelValue', value);
  }else
    emit('update:modelValue', _model);
}
function SelectItem(item){
  show.value=false;
  model.value = item && typeof item === 'object'?item[op_label.value]:item;
  emitir(item)
}
async function ExtraerDatos(options){
  if(props.options.hasOwnProperty('getData')){
    if(props.GenerateList) return await props.options.getData(props.GenerateList);
    const data = (await props.options.getData({params: {rowsPerPage: 0,order_by:op_label.value}}))
    if(data.hasOwnProperty('data')){
      return data.data;
    }else
      return data;
  }else{
    return options
  }
}
watch(()=>props.options,async(newVal,oldVal)=>{
  loading.value=true;
  array = await ExtraerDatos(newVal);
  loading.value=false;
});
watch(model,(newtext,oldtext)=>{
  if (newtext && newtext.length >= 2 && !filterOptions.value.some(item => {
          const compareText = (typeof item === 'object' ? item[op_label.value] : item) || '';
          return compareText === newtext;
        })
  ){
    show.value = true;
  } else {
    show.value = false;
  }
});
watch(()=>props.modelValue,(newVal,oldVal)=>{
    model.value = newVal;
});
const filterOptions = computed(()=> {
  if (model.value) {
    return array.filter(item => {
      const compareText = (typeof item === 'object' ? item[op_label.value] : item) || '';
      return compareText.toString().toLowerCase().includes(model.value.toLowerCase());
    });
  } else return [];
});
const ClickFueraDelRef = (event) => {
  if (recomendacion.value && !recomendacion.value.contains(event.target)) {
    if (show.value) show.value = false;
  }
};
</script>
<style lang="sass" scoped>
.select
  background-color: $grey-1

.select-dark
  background-color: $grey-10
.select:hover
  background-color: $grey-5

.select-dark:hover
  background-color: $grey-8

.posicion
  position: absolute
  z-index: 100
  // height: 100%
  max-height: 7em
  overflow-y: auto
  width: 100%
  border-radius: 0px 0px 10px 10px
.recomen
  position: relative
.recomen .q-field--with-bottom
  padding-bottom: 0px !important
</style>
