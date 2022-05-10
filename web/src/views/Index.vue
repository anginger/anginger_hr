<template>
  <v-container>
    <v-card flat>
      <v-card-title> 載入中... </v-card-title>
      <v-card-subtitle> Loading... </v-card-subtitle>
    </v-card>
  </v-container>
</template>

<script>
export default {
  name: "Index",
  created() {
    this.$router.replace("/manage");
  },
};
</script>
