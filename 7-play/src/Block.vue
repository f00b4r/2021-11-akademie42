<template>
  <div>
    <button @click="doIncrement">Click me</button>
    <div>Total clicks: {{ clicks }}</div>
  </div>
</template>

<script>
export default {
  props: {
    clicks: {
      type: Number,
      required: true
    },
    size: {
      type: Number,
      required: false,
      default: 1
    }
  },
  methods: {
    doIncrement() {
      this.$emit('increment', {size: this.size});
    }
  }
};
</script>
