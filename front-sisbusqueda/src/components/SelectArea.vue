<template>
    <q-select
      dense
      outlined
      v-model="model"
      @update:model-value="update($event)"
      clearable
      @clear="reset"
      use-input
      hide-selected
      fill-input
      input-debounce="0"
      :label="label"
      :options="options"
      option-label="nombre"
      option-value="id"
      @filter="filterFn"
    >
      <template v-slot:prepend>
        <q-icon name="work" />
      </template>
      <template v-slot:no-option>
        <q-item>
          <q-item-section class="text-grey"> No results </q-item-section>
        </q-item>
      </template>
    </q-select>
  </template>
  
  <script setup>
  import { onMounted, ref } from "vue";
  // import AsuntoSeervice from "src/services/AsuntoService";
  import AreaService from "src/services/AreaService";
  
  //Emits
  const emits = defineEmits(["selectedItem"]);
  const props = defineProps({
    id: {
      type: Number,
      default: null,
    },
    label: {
      type: String,
      default: "Derivar Para",
    },
  });
  
  const stringOptions = ref([]);
  // const options = ref(stringOptions);
  const options = ref([]);
  const model = ref(null);
  
  onMounted(async () => {
    stringOptions.value = (
      await AreaService.getData({
        params: { sort_by: "id", direction: "desc" },
      })
    ).data;
    if (props.id) {
      model.value = await AreaService.get(props.id);
    }
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
            await AreaService.getData({
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
  function update(event) {
    emits("selectedItem", event);
  }
  function reset() {
    model.value = null;
  }
  </script>
  