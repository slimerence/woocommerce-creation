<template>
  <div id="app">
    <WooPlanner />
  </div>
</template>

<script>
import WooPlanner from "./components/WooPlanner.vue";

export default {
  name: "App",
  components: {
    WooPlanner,
  },
  
};
</script>

<style>
html,
body {
  margin: 0;
  padding: 0;
}

#app {
  height: 100%;
  height: 700px;
  width: 100%;
  margin: 0 auto;
  position: relative;
  /* background-color: aquamarine; */
  padding: 20px;
  box-sizing: border-box;

}
</style>
