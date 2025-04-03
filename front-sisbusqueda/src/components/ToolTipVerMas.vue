<template>
    <span ref="myDiv">
      <span>
        {{ displayText }}
        <q-tooltip v-if="showtooltip" v-model="mostrar" anchor="top middle" transition-hide="scale" transition-show="scale"
          class="text-body2">
          {{ fullText }}
        </q-tooltip>
      </span>
      <span v-if="showtooltip" @click="mostrar = !mostrar" class="text-weight-bold cursor-pointer text-caption">Ver {{mostrar?'menos':'más'}}</span>
    </span>

</template>

<script setup>
import { ref, computed, defineProps, onMounted } from 'vue';

const props = defineProps({
  text:{type:String,default:'Un libro (del latín liber, libri) es una obra impresa, manuscrita o pintada en una serie de hojas de papel, pergamino, vitela u otro material, unidas por un lado (es decir, encuadernadas) y protegidas con tapas, también llamadas cubiertas. Un libro puede tratar sobre cualquier tema. Según la definición de la Unesco, un libro debe poseer veinticinco hojas mínimo (49 páginas), pues de veinticuatro hojas o menos sería un folleto; y de una hasta cuatro páginas se consideran hojas sueltas (en una o dos hojas)​ También se llama «libro» a una obra de gran extensión publicada en varias unidades independientes, llamadas tomos o volúmenes. Otras veces se llama «libro» a cada una de las partes de una obra, aunque físicamente se publiquen todas en un mismo volumen (ejemplo: Libros de la Biblia).'},
  charLimit:{type:Number,default:null},
  palabrasLimit:{type:Number,default:null},
});

const myDiv = ref(null);

onMounted(() => {
  document.addEventListener('click', ClickFueraDelRef);
});
const mostrar = ref(false);
const limite = props.charLimit ? props.charLimit: props.palabrasLimit?props.palabrasLimit:null;

const displayText = computed(() => {
  if (props.charLimit) {
    return props.text.slice(0, props.charLimit)+'...';
  } else if (props.palabrasLimit) {
    const words = props.text.split(' ').slice(0, props.palabrasLimit).join(' ')+'...';
    return words;
  }else {
    return props.text;
  }
});

const fullText = computed(() => props.text);

const showtooltip = computed(() => limite ? props.text.length > limite : false);

const ClickFueraDelRef = (event) => {
  if (myDiv.value && !myDiv.value.contains(event.target)) {
    cerrarToolTip();
  }
};
const cerrarToolTip = () => {
  if(mostrar.value)
    mostrar.value = false;
};
const MouseLeave = () => {
  // Lógica que se ejecutará cuando el ratón salga del div
  mostrar.value = false;
  console.log('El ratón salió del div');
  // Puedes realizar otras acciones aquí
};
</script>
