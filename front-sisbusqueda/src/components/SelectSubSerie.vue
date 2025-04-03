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
