<!-- ejemplo de uso -->
<!--
<div class="row">
  <SelectUbigeoPlus v-model="codigo" cod_departamento="21" cod_provincia="01"
    :show_departamento="true" :show_provincia="true" Class="q-pa-sm col-12 col-md-4 col-sm-6"/>
</div>
-->
<template>
  <q-select v-if="cod_departamento === null || show_departamento"
      use-input hide-selected fill-input input-debounce="0" 
      :clearable="clearable" :dense="dense" :outlined="outlined" 
      v-model="model_depa" label="Departamento" :class="Class" lazy-rules
      :rules="requerido?[(val) => (val!==null) || 'Por favor seleccione departamento',]:[]"
      :options="optionsDepartamentos" option-label="nombre" option-value="cod_dep"
      @filter="filterDepartamentos" :loading="cargarComponent || loading" :disable="cargarComponent || props.cod_departamento !== null">
      <template v-slot:label> Departamento <span v-if="requerido" class="text-red-7 text-weight-bold">(*)</span></template>
      <template v-slot:no-option>
          <q-item>
              <q-item-section class="text-grey">
                  No se han encontrado resultados
              </q-item-section>
          </q-item>
      </template>
  </q-select>
  <q-select v-if="cod_provincia === null || show_provincia"
      use-input hide-selected fill-input input-debounce="0" 
      :clearable="clearable" :dense="dense" :outlined="outlined" 
      v-model="model_prov" label="Provincia" :class="Class" lazy-rules
      :rules="requerido?[(val) => (val!==null) || 'Por favor seleccione provincia',]:[]"
      :options="optionsProvincias"  option-label="nombre" option-value="cod_prov" :readonly="model_depa===null"
      @filter="filterProvincias" :loading="cargarComponent || loading" :disable="cargarComponent || props.cod_provincia !== null">
      <template v-slot:label> Provincia <span v-if="requerido" class="text-red-7 text-weight-bold">(*)</span></template>
      <template v-slot:no-option>
          <q-item>
              <q-item-section class="text-grey">
                  No se han encontrado resultados
              </q-item-section>
          </q-item>
      </template>
  </q-select>
  <q-select use-input hide-selected fill-input input-debounce="0" 
      :clearable="clearable" :dense="dense" :outlined="outlined" 
      v-model="model_dist" label="Distrito" :class="Class" lazy-rules
      :rules="requerido?[(val) => (val!==null) || 'Por favor seleccione distrito',]:[]"
      :options="optionsDistritos"  option-label="nombre" option-value="cod_dist" :readonly="model_prov===null"
      @filter="filterDistritos" :loading="cargarComponent || loading" :disable="cargarComponent" >
      <template v-slot:label> Distrito <span v-if="requerido" class="text-red-7 text-weight-bold">(*)</span></template>
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
import UbigeosService from 'src/services/UbigeoService';
import { onBeforeMount, ref, watch } from 'vue';
const emit = defineEmits(['update:modelValue']);
const props = defineProps({
    modelValue: {default:null},
    cod_departamento: {type:String,default:null},
    cod_provincia: {type:String,default:null},
    show_departamento: {default:false},
    show_provincia: {default:false},
    requerido: {default:false},
    /**** estilos******* */
    Class: {default:''},
    loading: {default:false},
    dense: {default:false},
    outlined: {default:false},
    clearable: {default:false},
});
const cargarComponent = ref(false);

const model_depa = ref(null);
const model_prov = ref(null);
const model_dist = ref(null);

// let allDepartamentos = [];
let allProvincias = [];
let allDistritos = [];

let stringDepartamentos = [];
let stringProvincias = [];
let stringDistritos = [];

function emitir(_model){
  emit('update:modelValue', _model? typeof _model === 'object'? _model.cod_dep+_model.cod_prov+_model.cod_dist:_model:null);
}
let auxi_dep = null; let auxi_pro = null; let auxi_dis = null;
// onBeforeMount
onBeforeMount(async () => {
  cargarComponent.value = true;
  [stringDepartamentos, allProvincias, allDistritos] = await UbigeosService.getAllUbigeo();
  [auxi_dep,auxi_pro,auxi_dis] = separarCadena(props.modelValue);
  if(auxi_dep) { getProvincias(auxi_dep); CargarModelDep(auxi_dep);};
  if(auxi_pro) { getDistritos(auxi_dep, auxi_pro); CargarModelPro(auxi_pro);};
  if(auxi_dis) CargarModelDis(auxi_dis);
  cargarComponent.value = false;
});
/* *** metodos Get ************* */
function getProvincias(cod_dep) {
  if(cod_dep) stringProvincias = allProvincias.filter(v => v.cod_dep === cod_dep);
}
function getDistritos(cod_dep, cod_prov) {
  if(cod_dep && cod_prov) stringDistritos = allDistritos.filter(v => v.cod_dep === cod_dep && v.cod_prov === cod_prov);
}
/******* revisar cambios de valor ************************ */
watch(()=>model_depa.value,(newval,oldval)=>{
  if(newval && auxi_dep!==newval.cod_dep) {
    model_prov.value = null; model_dist.value = null;
    getProvincias(newval.cod_dep); 
    auxi_dep=newval.cod_dep;
  }else if(newval === null) model_prov.value = null;
});
watch(()=>model_prov.value,(newval,oldval)=>{
  if(newval && auxi_pro!==newval.cod_prov) {
    model_dist.value = null;
    getDistritos(newval.cod_dep, newval.cod_prov); 
    auxi_pro=newval.cod_prov;
  } else if(newval === null) model_dist.value = null;;
});
watch(()=>model_dist.value,(newval,oldval)=>{
  if(newval && auxi_dis!==newval.cod_dist)
    auxi_dis=newval.cod_dist;
  emitir(newval);
});
watch(()=>props.modelValue,(newval,oldval)=>{
  let [_dep_, _pro_, _dis_] = separarCadena(newval);
  if(_dep_ && auxi_dep!==_dep_) {
    getProvincias(_dep_);
    auxi_dep = _dep_; auxi_pro = null;
    CargarModelDep(_dep_);
  }
  if(_pro_ && auxi_pro!==_pro_) {
    getDistritos(auxi_dep, _pro_);
    auxi_pro = _pro_; auxi_dis = null;
    CargarModelPro(_pro_);
  }
  if(_dis_ && auxi_dis!==_dis_) {
    auxi_dis = _dis_;
    CargarModelDis(_dis_);
  }
});
/*** ********************************************************************* */
function separarCadena(cadena) {
  let codigo_array = cadena && cadena !=="" && cadena.length === 6? cadena.match(/.{1,2}/g) || [null,null,null]:[null,null,null];
  if (props.cod_departamento) codigo_array[0] = props.cod_departamento;
  if (props.cod_departamento && props.cod_provincia) codigo_array[1] = props.cod_provincia;
  return codigo_array;
}
function CargarModelDep(_dep){
  model_depa.value = _dep ? stringDepartamentos.find(v => v.cod_dep === _dep) : _dep;
}
function CargarModelPro(_pro){
  model_prov.value = _pro ? stringProvincias.find(v => v.cod_prov === _pro) : _pro;
}
function CargarModelDis(_dis){
  model_dist.value = _dis ? stringDistritos.find(v => v.cod_dist === _dis) : _dis;
}
/*******  para los filtros *********************************************************** */
const optionsDepartamentos = ref(stringDepartamentos);
const optionsProvincias = ref(stringProvincias);
const optionsDistritos = ref(stringDistritos);
function filterDepartamentos(val, update) {
  update(() => {
    if (val === '') optionsDepartamentos.value = stringDepartamentos;
    else {
      optionsDepartamentos.value = stringDepartamentos.filter(v => {
        return v.nombre.toString().toLowerCase().includes(val.toLowerCase());
      });
    }
  })
};
function filterProvincias(val, update) {
  update(() => {
    if (val === '') optionsProvincias.value = stringProvincias;
    else {
      optionsProvincias.value = stringProvincias.filter(v => {
        return v.nombre.toString().toLowerCase().includes(val.toLowerCase());
      });
    }
  })
};
function filterDistritos(val, update) {
  update(() => {
    if (val === '') optionsDistritos.value = stringDistritos;
    else {
      optionsDistritos.value = stringDistritos.filter(v => {
        return v.nombre.toString().toLowerCase().includes(val.toLowerCase());
      });
    }
  })
};
</script>
