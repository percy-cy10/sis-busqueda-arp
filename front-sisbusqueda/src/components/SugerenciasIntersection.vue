<template>
<div class="q-pa-md flex justify-center" style="position: relative; overflow-y: auto;">
    <div style="max-width: 100%;">
        <q-intersection v-for="val,index in arraySugerencias" :key="index" transition="flip-right">
            <q-item clickable v-ripple @click="DarSugerencia(val)">
            <q-item-section avatar style="position: relative; margin-right: 10px;">
                <q-icon color="primary" name="receipt_long" size="lg" text-color="white" />
                <q-badge floating color="primary">{{ val.cod_escritura }}</q-badge> 
            </q-item-section>
            <q-item-section>
                <q-item-label lines="2">{{ val?.sub_serie.nombre }}</q-item-label>
                <q-item-label caption lines="2">
                    <span class="text-weight-bold">BIEN:</span>{{ val?.bien}} 
                </q-item-label>
                <q-item-label caption lines="1">
                <div class="row">
                    <div class="q-pr-sm">
                        <span class="text-weight-bold">FECHA:</span>{{ val?.anio}}{{ val.mes?'/'+val.mes:'' }}{{ val.dia?'/'+val.dia:'' }} 
                    </div>
                    <div class="q-pr-sm"> <q-badge outline color="primary"> {{ val.cod_folioInicial }}</q-badge></div>
                    <div class="q-pr-sm"> <q-badge outline color="primary"> {{ val.cod_folioFinal }}</q-badge></div>
                </div>
                </q-item-label>
            </q-item-section>
            </q-item>
        </q-intersection>
        <q-inner-loading v-if="cargar" :showing="cargar">
            <q-spinner-facebook size="250px" color="light-blue" />
        </q-inner-loading>
    </div>
</div>
</template>

<script setup>
import { onBeforeMount, ref } from 'vue';
import LibroService from 'src/services/LibroService';
import EscrituraService from 'src/services/EscrituraService';
const emits = defineEmits(["sugerencia"]);
const props = defineProps({
    subserie_id:{default:null},
    notario_id:{default:null},
    bien:{default:null},
    anio:{default:null},
    mes:{default:null},
    dia:{default:null},
    cod_escritura:{default:null},
    cod_folioInicial:{default:null},
    precision:{default:{
        subserie_id:false,
        notario_id:true,
        bien:false,
        anio:false,
        mes:false,
        dia:false,
    }},
});
const cargar = ref(false);
const arraySugerencias = ref([]);
let libros = null;
onBeforeMount(async()=>{
    if (props.notario_id) await DatosSugerencia();
})
async function DatosSugerencia() {
  cargar.value = true;
  try {
    const res_libros = await LibroService.getData({params: {
        rowsPerPage: 0,
        filter_by: { notario_id: props.notario_id }
    }});
    libros = res_libros.data;
    const Libro_ids = res_libros.data.map(item => item.id);
    const res_escriPromises = Libro_ids.map(async libroId => {
      const res_escri = await EscrituraService.getData({params: {
          rowsPerPage: 0,
          filter_by: { libro_id: libroId } //agregar mas filtros
      }});
      return res_escri.data;
    });
    const res_escriData = await Promise.all(res_escriPromises);
    arraySugerencias.value = res_escriData.flatMap(data => data);
  } catch (error) {
    console.error('Error al obtener datos sugerencia:', error);
  }
  cargar.value = false;
}

function DarSugerencia(datos){
  let libro = libros.filter(val =>{
    return val.id === datos.libro_id; 
  });
  emits('sugerencia',{libro:libro[0],escritura:datos})
}
</script>

<style lang="sass" scoped>

</style>