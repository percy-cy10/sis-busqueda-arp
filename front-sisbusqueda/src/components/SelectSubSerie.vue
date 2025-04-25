<template>
  <q-select
    label="Sub-Serie-Componente"
    :model-value="subSerie"
    use-input=""
    @update:model-value="(e) => emits('update:subSerie', e)"
    :options="options"
    option-label="nombre"
    option-value="id"
    @filter="filterFn"
    dense
    clearable
  ></q-select>
</template>

<script setup>
import { onMounted, ref } from "vue";
import SubSerieService from "src/services/SubSerieService";

const props = defineProps(["subSerie"]);
const emits = defineEmits(["update:subSerie"]);

const stringOptions = ref([]);
const options = ref([]);

onMounted(async () => {
  stringOptions.value = (
    await SubSerieService.getData({
      params: {
        sort_by: "id",
        direction: "desc",
      },
    })
  ).data;
  console.log(stringOptions.value);
});

function filterFn(val, update, abort) {
  // call abort() at any time if you can't retrieve data somehow

  update(
    async () => {
      if (val === "") {
        options.value = stringOptions.value;
      } else {
        const needle = val.toLowerCase();
        options.value = (
          await SubSerieService.getData({
            params: { sort_by: "id", direction: "desc", search: needle },
          })
        ).data;
      }
    },

    // "ref" is the Vue reference to the QSelect
    (ref) => {
      if (val !== "" && ref.options.length > 0 && ref.getOptionIndex() === -1) {
        ref.moveOptionSelection(1, true); // focus the first selectable option and do not update the input-value
        ref.toggleOption(ref.options[ref.optionIndex], true); // toggle the focused option
      }
    }
  );
}
</script>

<!--
<template>
  <q-select
    dense
    outlined
    v-model="selected"
    :options="subseries"
    option-label="nombre"
    option-value="id"
    label="Subserie *"
    emit-value
    map-options
    clearable
    @update:model-value="handleChange"
  >
    <template v-slot:prepend>
      <q-icon name="mdi-folder" />
    </template>
    <template v-slot:no-option>
      <q-item>
        <q-item-section class="text-grey">
          No hay subseries disponibles
        </q-item-section>
      </q-item>
    </template>
  </q-select>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import SubserieService from 'src/services/SubSerieService'

const props = defineProps({
  subserieId: Number  // Prop para inicializar el valor seleccionado
})

const emit = defineEmits(['selectedItem'])

const subseries = ref([])  // Lista de subseries
const selected = ref(props.subserieId)  // Valor seleccionado de subserie, que se obtiene del prop

// Cargar subseries al montar el componente
onMounted(async () => {
  try {
    const { data } = await SubserieService.getData()
    subseries.value = data
  } catch (error) {
    console.error("Error cargando subseries:", error)
  }
})

// Escuchar cambios en el prop 'subserieId' y actualizar 'selected'
watch(() => props.subserieId, (newVal) => {
  selected.value = newVal
})

const handleChange = (value) => {
  emit('selectedItem', value)  // Emitir el valor seleccionado al componente padre
}
</script> -->
