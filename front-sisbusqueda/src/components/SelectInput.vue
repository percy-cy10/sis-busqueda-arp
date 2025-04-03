<template>
  <q-select use-input fill-input hide-selected
      debounce="200" input-debounce="0"
      :label="label"
      v-model="model"
      @update:model-value="emitir(model)"
      @filter="filter"
      :options="filterOptions"
      :option-value="op_value"
      :option-label="op_label"
      :loading="loading"
      :disable="loading"
      >
      <template v-slot:label> {{label}} <span v-if="requerido" class="text-red-7 text-weight-bold">(*)</span></template>
      <template v-slot:prepend>
        <slot name="prepend"></slot>
      </template>
      <template v-slot:append >
        <slot name="append"></slot>
      </template>
      <template v-if="active_before" v-slot:before >
        <slot name="before"></slot>
      </template>
      <!-- <template v-slot:after >
        <slot name="after"></slot>
      </template>
      <template v-slot:error >
        <slot name="error"></slot>
      </template>
      <template v-slot:hint >
        <slot name="hint"></slot>
      </template> -->
      <!-- <template v-slot:counter >
        <slot name="counter"></slot>
      </template>
      <template v-slot:loading >
        <slot name="loading"></slot>
      </template>
      <template v-slot:before-options >
        <slot name="before-options"></slot>
      </template>
      <template v-slot:after-options >
        <slot name="after-options"></slot>
      </template> -->
      <template v-slot:no-option>
          <q-item>
              <q-item-section class="text-grey">
                  No se han encontrado resultados
              </q-item-section>
          </q-item>
      </template>
  </q-select>
</template>

<script setup>
import { ref, onBeforeMount, watchEffect, watch } from 'vue';
const emit = defineEmits(['update:modelValue']);
const props = defineProps({
  label:{default:'Select'},
  modelValue: {default:null},
  options: {default:null},
  OptionLabel:{default:'nombre'},
  OptionValue:{default:'id'},
  ValueMulti:{default:null},
  ValueAll:{default:false},
/********************************************** */
  GenerateList :{default:null},
  requerido:{default:false},
  active_before:{default:false}, /* agregar activadores de slots** */
});
let stringOptions = null;
const model = ref('');
const loading = ref(false);
const op_label = ref('');
const op_value = ref('');

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
onBeforeMount(async () => {
  loading.value=true;
  op_label.value = props.GenerateList ? props.GenerateList.column : props.OptionLabel;
  op_value.value = props.GenerateList ? props.GenerateList.column : props.OptionValue;
  stringOptions = await ExtraerDatos(props.options);
  CargarModel(props.modelValue);
  loading.value=false;
});
watch(()=>props.options,async(newVal,oldVal)=>{
  loading.value=true;
  stringOptions = await ExtraerDatos(newVal);
  CargarModel(props.modelValue);
  loading.value=false;
});
watch(()=>props.modelValue,(newVal,oldVal)=>{
  CargarModel(newVal);
});
const filterOptions = ref(stringOptions);
function filter(val, update) {
  update(() => {
    if (val === '') filterOptions.value = stringOptions;
    else {
      filterOptions.value = stringOptions.filter(v => {
        if(typeof v === 'object'){
          return v[op_label.value].toString().toLowerCase().includes(val.toLowerCase());
        }else
          return v.toString().toLowerCase().includes(val.toLowerCase())
      });
    }
  });
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
function CargarModel(_model){
  if (_model && typeof stringOptions[0] === 'object' && typeof _model !== 'object')
    model.value = stringOptions.find(v => v[op_value.value] === _model);
  else
    model.value = _model;
}
// function limpiarEspaciosRepetidos(array,val) {
//   // Limpiar espacios en cada notario
//   array.forEach((item) => {
//     item[val] = item[val].replace(/\s+/g, ' ').trim();
//   });
//   // Eliminar elementos duplicados
//   const array_ = array.filter(
//     (item, index, self) =>
//       index ===
//       self.findIndex((t) => t[val] === item[val])
//   );
//   return array_;
// }
</script>
