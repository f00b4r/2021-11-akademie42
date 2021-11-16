<template>
  <div>
    <block :clicks="clicks" @increment="doIncrement"/>
    <block :clicks="clicks" @increment="doIncrement" :size="3"/>
  </div>
</template>

<script>
import Block from "./Block.vue";

export default {
  name: "App",
  components: {
    Block
  },
  computed: {
    clicks() {
      return this.$store.state.numbers.clicks;
    }
  },
  created() {
    // this.$flush.subscribe((flash) => {
    //   console.log(flash);
    // });
  },
  methods: {
    doIncrement({size}) {
      this.$store.dispatch('numbers/increment', {size});
    }
  }
};
</script>
